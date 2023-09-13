<?php

namespace App\Http\Controllers\External\Operation;

use PDF;
use Datatables;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Classes\VNPay;
use App\Models\Product;
use App\Classes\HTDUtils;
use App\Models\OrderTrack;
use App\Models\KolConfig;
use App\Models\VendorOrder;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\DevelopmentNote;
use App\Models\OrderVendorInfo;
use App\Models\OrderAdminTrackLog;
use App\Models\OrderVNPayTrackLog;
use Illuminate\Support\Facades\DB;
use App\Models\OrderHandlingFeeLog;
use App\Http\Controllers\Controller;
use App\Models\AffiliateLevelConfig;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Orders\SaleFullReport;
use App\Exports\Orders\Reports\RefundDetail;
use App\Exports\Orders\MerchantPaymentReport;
use App\Exports\Orders\Reports\KOLConsumerBonus;
use App\Exports\Orders\Reports\ExternalOperation;


class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables($status, $from = null, $to = null, $key = null, $delivery_code = null)
    {
        $datas = $this->query($status, $from, $to, $key, $delivery_code)
            ->orderByDesc('created_at');
        if(!isset($from)){
            $datas = $datas->limit(200);
        }
        $datas = $datas->get();
         //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->editColumn('id', function(Order $data) {
                $id = '<a href="'.route('admin-order-invoice',$data->id).'" style="'
                    .($data->shipping_type == 'negotiate' ? 'background-color:LightSalmon;' :
                    ($data->orderconsumershippingcosts->count() > 0 ? '' : 'background-color:LightBlue;')
                    )
                    .'">'.$data->order_number
                    .'</a>';
                return $id;
            })
            ->editColumn('customer_received', function(Order $data) {
                $s = ($data->customer_received ? 'Yes' : '');
                return $s;
            })
            ->editColumn('pay_amount2', function(Order $data) {
                return $data->currency_sign . number_format(round($data->pay_amount2 * $data->currency_value , 2));
            })
            ->addColumn('action', function(Order $data) {
                $recover = $data->vendororders()->count() == 0 ? '<a href="javascript:;" data-href="' . route('admin-order-track-recover-vendororder',$data->id) . '" data-toggle="modal" data-target="#confirm-verify" class="delete"> <i class="fas fa-user-check"></i> Recover</a>' : '';
                $orders = '<a href="javascript:;" data-href="'. route('admin-order-edit',$data->id) .'" class="delivery" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Delivery Status</a>';
                $resend_order_info = '<a href="javascript:;" data-href="'. route('admin-send-order-info',$data->id) .'" class="resend-order-info"><i class="fas fa-envelope"></i>Send Order Info To Customer</a>';
                $resend_order_info_vendor = '<a href="javascript:;" data-href="'. route('admin-send-order-info-vendors',$data->id) .'" class="resend-order-info-vendors"><i class="fas fa-envelope"></i>Send Order Info To Vendors</a>';
                // $invoice_update = '<a href="javascript:;" data-href="'. route('admin-order-edit-invoice',$data->id) .'" class="invoice-update" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Update Invoice Info</a>';
                return '<div class="godropdown"><button class="go-dropdown-toggle"> Actions<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show',$data->id) . '" > <i class="fas fa-eye"></i> Details</a><a href="javascript:;" class="send" data-email="'. $data->customer_email .'" data-toggle="modal" data-target="#vendorform"><i class="fas fa-envelope"></i> Send</a>'.$resend_order_info.$resend_order_info_vendor.'<a href="javascript:;" data-href="'. route('admin-order-track',$data->id) .'" class="track" data-toggle="modal" data-target="#modal1"><i class="fas fa-truck"></i> Track Order</a>'.$orders.$recover.'</div></div>';
            })
            ->rawColumns(['id','action'])
            ->toJson();
    }

    public function query($status, $from = null, $to = null, $key = null, $delivery_code = 'null')
    {
        $statuss = array($status);
        if($status == 'all'){
            $statuss = array('pending', 'processing', 'completed', 'on delivery', 'declined');
        }
        $query = Order::whereIn('status',$statuss);
        if($key != '0'){
            $query = $query->where('customer_email' , 'like', '%'.$key.'%');
        }
        if($delivery_code != 'null'){

            $query = $query->where(function ($q) use ($delivery_code) {
                $q->where('order_number' , 'like', '%'.$delivery_code.'%')
                    ->orWhere(function ($q) use ($delivery_code) {
                        $q = $q->whereExists(function ($q) use ($delivery_code) {
                            $q->select(DB::raw(1))
                                ->from('order_consumer_shipping_costs')
                                ->whereColumn('order_consumer_shipping_costs.order_id', 'orders.id')
                                ->where('order_consumer_shipping_costs.shipping_partner_code', $delivery_code)
                                ;
                        });
                    });
            });
        }
        if($from != null){
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $query = $query->whereBetween('created_at',[$from, $to]);
        }
        return $query;
    }

    //*** JSON Request
    public function datatablesReport($status, $iscollected, $from = null, $to = null)
    {
        $statuss=array($status);
        if($status=='all'){
            $statuss = array('pending','processing','completed','declined','on delivery', 'declined');
        }
        $iscollecteds=array($iscollected);
        if($iscollected=='all'){
            $iscollecteds = array(1,0);
        }

        $orders = null;
        if($from == null){
            $orders = DB::table('orders')
                ->whereIn('status',$statuss);
        }
        else{
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $orders = DB::table('orders')
                ->whereIn('status',$statuss)
                // ->whereBetween('created_at',[$from, $to]);
                ->where(function ($q)  use ($from, $to){
                    $q = $q->whereExists(function ($q) use ($from, $to) {
                        $q->select(DB::raw(1))
                            ->from('order_vendor_infos')
                            ->whereColumn('order_vendor_infos.order_id', 'orders.id')
                            ->whereBetween('order_vendor_infos.payment_to_merchant_date',[$from, $to]);
                    });
                });
        }

        $order_details = DB::table('vendor_orders')
        ->joinSub($orders, 'o1', function ($join) {
            $join->on('vendor_orders.order_id', '=', 'o1.id');
        })
        ->join('users as shop', 'vendor_orders.user_id', '=', 'shop.id')
        ->join('products as prod', 'vendor_orders.product_id', '=', 'prod.id')
        ->join('categories as cat', 'prod.category_id', '=', 'cat.id')
        ->leftJoin('subcategories as sub_cat', 'prod.subcategory_id', '=', 'sub_cat.id')
        ->leftJoin('childcategories as child_cat', 'prod.childcategory_id', '=', 'child_cat.id')
        ->whereIn('is_handlingfee_collected',$iscollecteds)
        ->select(
            'shop.id as shop_id',
            'o1.id as order_id',
            'vendor_orders.product_id',
            'prod.name as product_name',
            'shop.shop_name as shop_name',
            DB::raw('DATE_FORMAT(o1.created_at,"%Y-%m-%d") as created_at'),
            'vendor_orders.qty',
            'vendor_orders.price',
            DB::raw('vendor_orders.price + vendor_orders.price_shopping_point_amount as amount'),
            'vendor_orders.shopping_point_used',
            'vendor_orders.shopping_point_amount',
            'vendor_orders.shopping_point_payment_remain',
            'o1.order_number',
            'o1.status',
            'shop.email',
            'shop.phone',
            'o1.is_online_payment',
            'o1.completed_at',
            'vendor_orders.is_handlingfee_collected',
            DB::raw('(CASE WHEN child_cat.handling_fee > 0 THEN child_cat.handling_fee '
                .'WHEN sub_cat.handling_fee > 0 THEN sub_cat.handling_fee '
                .'ELSE cat.handling_fee END) AS handling_fee')
        );

        $total_cash_amount = DB::query()->fromSub($order_details, 'subquery')
            ->where('is_online_payment','=','0')
            ->select('order_id', 'shop_id', DB::raw('SUM(amount) - SUM(shopping_point_amount) as total_cash_amount'))
            ->groupBy('order_id', 'shop_id');
        $total_creditcard_amount = DB::query()->fromSub($order_details, 'subquery')
            ->where('is_online_payment','=','1')
            ->select('order_id', 'shop_id', DB::raw('SUM(amount) - SUM(shopping_point_amount) as total_creditcard_amount'))
            ->groupBy('order_id', 'shop_id');

        $order_result = DB::query()->fromSub($order_details, 'od')
            ->leftJoinSub($total_cash_amount, 'tcamount', function ($join) {
                $join->on('od.order_id', '=', 'tcamount.order_id');
                $join->on('od.shop_id', '=', 'tcamount.shop_id');
            })
            ->leftJoinSub($total_creditcard_amount, 'tccamount', function ($join) {
                $join->on('od.order_id', '=', 'tccamount.order_id');
                $join->on('od.shop_id', '=', 'tccamount.shop_id');
            })
            ->select('od.order_id', 'od.shop_id', 'shop_name','email','phone','created_at','order_number','status'
                , DB::raw('SUM(amount) as total_amount')
                , 'total_cash_amount'
                , 'total_creditcard_amount'
                , DB::raw('SUM(shopping_point_used) as total_collected_sp')
                , DB::raw('SUM(shopping_point_amount) as total_collected_sp_amount')
                , DB::raw('SUM(shopping_point_payment_remain) as total_payment_amount')
                , DB::raw('SUM(amount * handling_fee) / SUM(amount)  as handling_fee_rate')
                , DB::raw('SUM(amount * handling_fee / 100.0) as merchant_handling_fee')
                , 'is_handlingfee_collected'
                )
            ->groupBy('od.order_id', 'od.shop_id', 'shop_name', 'email',
                 'phone', 'created_at', 'order_number', 'total_cash_amount',
                'total_creditcard_amount', 'is_handlingfee_collected')
            ->orderBy('od.order_id','desc')
            ->get();

        return Datatables::of($order_result)
            ->editColumn('total_amount', function($data) {
                return number_format(round($data->total_amount));
            })
            ->editColumn('total_cash_amount', function($data) {
                return number_format(round($data->total_cash_amount));
            })
            ->editColumn('total_creditcard_amount', function($data) {
                return number_format(round($data->total_creditcard_amount));
            })
            ->editColumn('total_collected_sp', function($data) {
                return number_format(round($data->total_collected_sp));
            })
            ->editColumn('total_collected_sp_amount', function($data) {
                return number_format(round($data->total_collected_sp_amount));
            })
            ->editColumn('total_payment_amount', function($data) {
                return number_format(round($data->total_payment_amount));
            })
            ->editColumn('handling_fee_rate', function($data) {
                return $data->handling_fee_rate.'%';
            })
            ->editColumn('merchant_handling_fee', function($data) {
                return number_format(round($data->merchant_handling_fee));
            })
            ->addColumn('action', function($data) {
                $invoice_update = '<a href="javascript:;" data-href="'. route('admin-order-edit-invoice',$data->order_id) .'" class="invoice-update" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Update Invoice Info</a>';
                // $collect = '<a href="javascript:;" data-href="'. route('admin-order-collect-handling-fee',[$data->order_id, $data->shop_id]) .'" class="collect-handling-fee" ><i class="fas fa-dollar-sign"></i> Collect Fee</a>';
                // $rollback = '<a href="javascript:;" data-href="'. route('admin-order-rollback-handling-fee',[$data->order_id, $data->shop_id]) .'" class="collect-handling-fee" ><i class="fas fa-dollar-sign"></i> Rollback Fee</a>';
                return '<div class="godropdown" ><button class="go-dropdown-toggle "> '.($data->is_handlingfee_collected == 1 ? 'Đã thu' : 'Chưa thu').'<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show', $data->order_id) . '" > <i class="fas fa-eye"></i> Details</a>'.$invoice_update.'</div></div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function datatable_delivery_partner($status, $from = null, $to = null){
        $statuss = array($status);
        if($status=='all'){
            $statuss = array('Pending','Processing','Completed','Declined','On Delivery');
        }

        $orders = null;
        if($from == null){
            $orders = DB::table('orders');
        }
        else{
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $orders = DB::table('orders')
                ->whereBetween('created_at',[$from, $to]);
        }

        $shippings = DB::table('order_consumer_shipping_costs as shipping')
            ->joinSub($orders, 'o', function ($join) {
                $join->on('shipping.order_id', '=', 'o.id');
            })
            ->whereIn('shipping.status',$statuss)
            ->join('users as shop', 'shipping.shop_id', '=', 'shop.id')
            ->select('shipping.order_id',
                'shipping.shop_id',
                'shop.name as shop_name',
                'shop.email',
                'shop.phone',
                'o.created_at',
                'o.order_number',
                'o.status as order_status',
                'shipping.shipping_partner',
                'shipping.shipping_partner_code',
                'shipping.shipping_cost',
                'shipping.MONEY_COLLECTION',
                'shipping.EXCHANGE_WEIGHT',
                'shipping.weight',
                'shipping.status as delivery_status',
                'shipping.products_amount',
                'shipping.shopping_point_amount',
                'shipping.tax_amount',
                'shipping.discount_amount',
                'shipping.total_qty',
                'shipping.total_item'
                )
            ->orderBy('o.id','desc')
            ->get()
        ;

        return Datatables::of($shippings)
            ->editColumn('shipping_cost', function($data) {
                return number_format(round($data->shipping_cost));
            })
            ->editColumn('MONEY_COLLECTION', function($data) {
                return number_format(round($data->MONEY_COLLECTION));
            })
            ->editColumn('products_amount', function($data) {
                return number_format(round($data->products_amount));
            })
            ->editColumn('shopping_point_amount', function($data) {
                return number_format(round($data->shopping_point_amount));
            })
            ->editColumn('tax_amount', function($data) {
                return number_format(round($data->tax_amount));
            })
            ->editColumn('discount_amount', function($data) {
                return number_format(round($data->discount_amount));
            })
            ->editColumn('total_qty', function($data) {
                return number_format(round($data->total_qty));
            })
            ->addColumn('action', function($data) {
                $finish = '<a href="javascript:;" data-href="'. route('admin-order-collect-handling-fee',[$data->order_id, $data->shop_id]) .'" class="collect-handling-fee" ><i class="fas fa-dollar-sign"></i> Complete Order</a>';
                return '<div class="godropdown" ><button class="go-dropdown-toggle "> '.($data->order_status).'<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show', $data->order_id) . '" > <i class="fas fa-eye"></i> Details</a>'.''.'</div></div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function index()
    {
        $now = Carbon::now()->format('Y-m-d');
        $config = KolConfig::where('kol_date', '=',  date('m-Y'))->first();
        // return view('admin.order.index', compact('now'));
        return view('admin.external.operation.report',[compact('config'),compact('now')]);
    }

    public function edit($id)
    {
        $data = Order::find($id);
        $todate = Carbon::now()->format('Y-m-d');

        $order_count_shop = DB::table('vendor_orders as t')
            ->select(
                'order_id',
                DB::raw('COUNT(DISTINCT user_id) as count_shop')
            )
            ->groupBy('order_id')
            ;

        $orders = DB::table('orders as t')
            ->joinSub($order_count_shop, 'cs', function ($join) {
                $join->on('cs.order_id', '=', 't.id');
            })
            ->select(
                'id',
                'order_number',
                'is_online_payment',
                'completed_at',
                'created_at',
                'status',
                'shopping_point_exchange_rate',
                'tax',
                'shipping_type',
                'percent_discount',
                'method',
                'payment_to_company_date',
                'payment_to_company_partner',
                'payment_to_company_amount',
                'refund_date',
                'refund_amount',
                'refund_bank',
                'refund_note',
                'cs.count_shop',
                'total_product_final_amount',
                'coupon_discount',
            );

        $order_details = DB::table('vendor_orders as vo')
            ->where('vo.order_id','=',$id)
            ->joinSub($orders, 'o1', function ($join) {
                $join->on('vo.order_id', '=', 'o1.id');
            })
            ->join('users as shop', 'vo.user_id', '=', 'shop.id')
            ->leftJoin('products as prod', 'vo.product_id', '=', 'prod.id')
            ->leftJoin('categories as cat', 'prod.category_id', '=', 'cat.id')
            ->leftJoin('subcategories as sub_cat', 'prod.subcategory_id', '=', 'sub_cat.id')
            ->leftJoin('childcategories as child_cat', 'prod.childcategory_id', '=', 'child_cat.id')
            ->leftJoin('order_consumer_shipping_costs as spc', function ($join) {
                $join->on('o1.id', '=', 'spc.order_id')
                    ->on('shop.id', '=', 'spc.shop_id');
            })
            ->select(
                'shop.id as shop_id',
                'o1.id as order_id',
                'vo.product_id',
                'prod.name as product_name',
                'shop.shop_name as shop_name',
                DB::raw('DATE_FORMAT(o1.created_at,"%Y-%m-%d") as created_at'),
                'vo.qty',
                'vo.price',
                DB::raw('vo.price + vo.price_shopping_point_amount as amount'),
                'vo.shopping_point_used',
                'vo.shopping_point_amount',
                'o1.order_number',
                'o1.status',
                'shop.email',
                'shop.phone',
                'o1.is_online_payment',
                'o1.completed_at',
                'o1.shopping_point_exchange_rate',
                'o1.payment_to_company_amount',
                'o1.method',
                DB::raw('CASE WHEN o1.total_product_final_amount > 0 THEN ifnull(o1.coupon_discount, 0) / o1.total_product_final_amount ELSE 0 END as percent_discount'),
                'vo.shop_coupon_code',
                'vo.shop_coupon_amount',
                'vo.shop_coupon_value',
                'vo.price_shopping_point',
                'vo.price_shopping_point_amount',
                'vo.shopping_point_payment_remain',
                'vo.item_price_shopping_point',
                'vo.product_sub_amount',
                'vo.product_final_amount',
                DB::raw('vo.product_final_amount * (CASE WHEN o1.total_product_final_amount > 0 THEN ifnull(o1.coupon_discount, 0) / o1.total_product_final_amount ELSE 0 END) as company_product_discount'),
                DB::raw('vo.product_final_amount - vo.product_final_amount * (CASE WHEN o1.total_product_final_amount > 0 THEN ifnull(o1.coupon_discount, 0) / o1.total_product_final_amount ELSE 0 END) as final_amount'),
                'vo.is_handlingfee_collected',
                'spc.shipping_cost',
                'spc.shipping_partner',
                DB::raw('(CASE WHEN method = "VNPay" THEN ifnull(spc.shipping_cost, 0) '
                    .'ELSE 0 END) AS delivery_fee'
                ),
                DB::raw('(CASE WHEN child_cat.handling_fee > 0 THEN child_cat.handling_fee '
                    .'WHEN sub_cat.handling_fee > 0 THEN sub_cat.handling_fee '
                    .'ELSE cat.handling_fee END) AS handling_fee'
                ),
                'shop.preferred',
                'o1.count_shop',
            );

        $order_result = DB::query()->fromSub($order_details, 'od')
            ->select('od.order_id', 'od.shop_id', 'shop_name','email','phone','created_at','order_number','status','preferred'
                , DB::raw('SUM(amount) as total_amount')
                , DB::raw('SUM(shopping_point_used) as total_collected_sp')
                , DB::raw('SUM(shopping_point_amount) as total_collected_sp_amount')
                , DB::raw('SUM(shopping_point_payment_remain) as total_payment_amount')
                , DB::raw('SUM(amount * handling_fee / 100.0) as merchant_handling_fee')
                , DB::raw('ROUND(SUM(amount * handling_fee / 100.0) / SUM(amount) * 100, 0)  as handling_fee_value')
                , 'is_handlingfee_collected'
                , DB::raw('SUM(final_amount) as _9_Amount')
                , DB::raw('SUM(price_shopping_point) as _10_Point')
                , 'shopping_point_exchange_rate as _11_Rate'
                , DB::raw('SUM(price_shopping_point_amount) as _12_Amount_of_Point')
                , DB::raw('SUM(shop_coupon_amount) as _13_Voucher_of_Merchant')
                , DB::raw('SUM(company_product_discount) as _14_Voucher_of_Techhub')
                , 'delivery_fee AS _15_Delivery_fee'
                , DB::raw('SUM(amount) + delivery_fee as _16_Total_of_bill')
                , DB::raw('CASE WHEN method = "VNPay" THEN (SUM(final_amount) + delivery_fee) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _17_Other_cards')
                , DB::raw('0 as _18_VISA_JCB_MASTERCARD_UPI_AMEX')
                , DB::raw('SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN (SUM(final_amount) + delivery_fee) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _19_Amount_Partner_Must_Pay')
                //, DB::raw('CASE WHEN payment_to_company_amount > 0 THEN payment_to_company_amount / count_shop ELSE SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / COUNT(DISTINCT shop_id)) ELSE 0 END END as _19_Amount_Partner_Must_Pay')
                , DB::raw('"" as _20_PAYMENT_TO_TECHHUB_Date')
                , DB::raw('CASE WHEN method = "VNPay" THEN method ELSE shipping_partner END as _21_PAYMENT_TO_TECHHUB_PARTNER')
                //, DB::raw('SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                , DB::raw('CASE WHEN payment_to_company_amount > 0 THEN payment_to_company_amount / count_shop ELSE SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN (SUM(final_amount) + delivery_fee) * 0.011 + (1650.0 / COUNT(DISTINCT shop_id)) ELSE 0 END END as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                , DB::raw('SUM(amount * handling_fee / 100.0) as _23_CHARGE_FEES_VND')
                , DB::raw('SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) as _24_Amount_Must_Pay_to_Merchant')
                , DB::raw('"" as _25_PAYMENT_to_Merchant_Date')
                , DB::raw('SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) as _26_Payment_to_Merchant_Amount')
                )
            ->groupBy('od.order_id', 'od.shop_id', 'shop_name', 'email', 'preferred',
                 'phone', 'created_at', 'order_number','status', 'is_handlingfee_collected', 'shopping_point_exchange_rate', 'delivery_fee', 'method', 'shipping_partner')
            ->get();
           //dd($order_result);
        return view('admin.order.delivery',compact('data', 'order_result','todate'));
    }

    public function kolConsumerBonus2w()
    {
        $config = KolConfig::where('kol_date', '=',  date('m-Y'))->first();

        if ($config == null  || $config->count()==0) {
            $errors = ['errors' => 'Please set config for '.date('m-Y')];
            return view('admin.order.reports.kol-consumer-bonus-2w',compact('errors','config'));

        }
        return view('admin.external.operation.report',compact('config'));
    }

    //
    public function datatablesOperationReport($from)
    {

        if ($from == null) {
            return response()->json(['errors' => 'Please set Year to reporting '], 400);
        }
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->operation($from);

        return  Datatables::of($datas)
            ->addColumn('user_total', function($data) {
                $info = '<span>'. $data['user_total'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('shop_total', function($data) {
                $info = '<span>'. $data['shop_total'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('shop_date', function($data)  {
                $info = '<span>'. $data['shop_date'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('order_total', function($data)  {
                $info = '<span>'. $data['order_total'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('order_complete', function($data)  {
                $info = '<span>'. $data['order_complete'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('order_declined', function($data)  {          
                $info = '<span>'. $data['order_declined'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('order_delivery', function($data)  {          
                $info = '<span>'. $data['order_delivery'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('order_pending', function($data)  {          
                $info = '<span>'. $data['order_pending'] .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_amount', function($data)  {
                $bonus =  number_format($data['total_amount'], 0, ',', ',');
                $info = '<span>'. $bonus .'</span><br>'.'<span>';
                return $info;
            })
            ->rawColumns([
                'user_total',
                'shop_total',
                'shop_date',
                'order_total',
                'order_complete',
                'order_declined',
                'order_delivery',
                'order_pending',
                'total_amount'
            ])
            ->toJson();

    }

    public function exportOperationReport($from)
    {
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->operation($from);

        if ($datas->count() > 0) {
            foreach($datas as $data){
                $data['total_amount'] =  number_format($data['total_amount'], 0, ',', ',');
            }
      
            $file_name = 'external_operation_report_'.$from.'___'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
            return Excel::download(new ExternalOperation($datas, $from), $file_name, null, []);
        }
        return;
    }
}
