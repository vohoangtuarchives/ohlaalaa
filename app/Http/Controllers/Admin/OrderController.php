<?php

namespace App\Http\Controllers\Admin;

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
use App\Exports\Orders\Reports\KOLConsumerBonus2W;
use App\Exports\Orders\Reports\MerchantPerformance;
use App\Exports\Orders\Reports\KOLConsumerBonusPaid;
use App\Exports\Orders\Reports\SaleFullReportDetail;
use App\Exports\Orders\Reports\MerchantPerformanceSummariesTop10ByShop;
use App\Exports\Orders\Reports\MerchantPerformanceSummariesTop10ByProduct;
use App\Exports\Orders\Reports\MerchantPerformanceSummariesTop10ByProvince;
use App\Exports\Orders\Reports\MerchantPerformanceSummariesTop10ByProductCategory;

class OrderController extends Controller
{
    const VAT = 2000000;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables($status, $from = null, $to = null, $key = null, $delivery_code = null)
    {
        if(!isset($from)){
           $from = Carbon::now()->format('Y-m-d');
        }
        $datas = $this->query($status, $from, $to, $key, $delivery_code)
         ->orderByDesc('created_at');
        $datas = $datas->limit(500);
        // $datas = $datas->offset(0)->limit(100);
        $datas = $datas->get();

         //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->editColumn('id', function(Order $data) {
                $id = '<img class="mr-2" src="'.asset('assets/images/admins/copy-solid.svg').'" width="20px" style="cursor:pointer" onclick="copyOrderNumber(this)"><a href="'.route('admin-order-invoice',$data->id).'" style="'
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
                $queryOnepay="";
                if($data->method == "Onepay"){
                    $queryOnepay = '<a href="javascript:;" class="onepay-query-button"  data-href="' . route('onepay.query',$data->id) . '"> <i class="fas fa-eye"></i> QueryDR OnePay</a>';
                }
                return '<div class="godropdown"><button class="go-dropdown-toggle"> Actions<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show',$data->id) . '" > <i class="fas fa-eye"></i> Details</a><a href="javascript:;" class="send" data-email="'. $data->customer_email .'" data-toggle="modal" data-target="#vendorform"><i class="fas fa-envelope"></i> Send</a>'.$resend_order_info.$resend_order_info_vendor.'<a href="javascript:;" data-href="'. route('admin-order-track',$data->id) .'" class="track" data-toggle="modal" data-target="#modal1"><i class="fas fa-truck"></i> Track Order</a>'.$orders.$recover.$queryOnepay. '</div></div>';
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

    #region SALE FULL REPORT START

    public function querySaleFullReport($status, $iscollected, $from = null, $to = null)
    {
        $statuss=array($status);
        if($status=='all'){
            $statuss = array('pending','processing','completed','declined','on delivery');
        }
        $iscollecteds=array($iscollected);
        if($iscollected=='all'){
            $iscollecteds = array(1,0);
        }

        $order_count_shop = DB::table('vendor_orders as t')
            ->select(
                'order_id',
                DB::raw('COUNT(DISTINCT user_id) as count_shop')
            )
            ->groupBy('order_id')
        ;

        $orders = null;
        if($from == null){
            $orders = DB::table('orders as t');
                //->where('order_number','=','202103yc4pid2oAZ');
        }
        else{
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $orders = DB::table('orders as t')
                ->where(function ($q)  use ($from, $to){
                    $q = $q->whereBetween('created_at',[$from, $to])
                        ->orWhereBetween('completed_at',[$from, $to])
                    ;
                });
        }

        $orders = $orders
            ->joinSub($order_count_shop, 'cs', function ($join) {
                $join->on('cs.order_id', '=', 't.id');
            })
            ->whereIn('status',$statuss)
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
            );


        $order_details = DB::table('vendor_orders')
        ->joinSub($orders, 'o1', function ($join) {
            $join->on('vendor_orders.order_id', '=', 'o1.id');
        })
        ->join('users as shop', 'vendor_orders.user_id', '=', 'shop.id')
        ->join('products as prod', 'vendor_orders.product_id', '=', 'prod.id')
        ->join('categories as cat', 'prod.category_id', '=', 'cat.id')
        ->leftJoin('subcategories as sub_cat', 'prod.subcategory_id', '=', 'sub_cat.id')
        ->leftJoin('childcategories as child_cat', 'prod.childcategory_id', '=', 'child_cat.id')
        ->leftJoin('order_consumer_shipping_costs as spc', function ($join) {
            $join->on('o1.id', '=', 'spc.order_id')
                ->on('shop.id', '=', 'spc.shop_id');
        })
        ->leftJoin('order_vendor_infos as vinfo', function ($join) {
            $join->on('o1.id', '=', 'vinfo.order_id')
                ->on('shop.id', '=', 'vinfo.shop_id');
        })
        ->whereIn('is_handlingfee_collected',$iscollecteds)
        ->select(
            'shop.affilate_code as shop_id',
            'o1.id as order_id',
            'vendor_orders.product_id',
            'prod.name as product_name',
            'shop.shop_name as shop_name',
            'vendor_orders.qty',
            'vendor_orders.price',
            DB::raw('vendor_orders.price + vendor_orders.price_shopping_point_amount as amount'),
            'vendor_orders.shopping_point_payment_remain',
            'shop.phone',
            'o1.is_online_payment',
            'o1.completed_at',
            'vendor_orders.is_handlingfee_collected',
            DB::raw('(CASE WHEN child_cat.handling_fee > 0 THEN child_cat.handling_fee '
                .'WHEN sub_cat.handling_fee > 0 THEN sub_cat.handling_fee '
                .'ELSE cat.handling_fee END) AS handling_fee'),
            'shop.affilate_code',
            DB::raw('DATE_FORMAT(o1.created_at,"%Y-%m-%d") as created_at'),
            'shop.name as shop',
            'shop.address',
            'shop.TaxCode',
            'shop.email',
            'shop.BankAccountName',
            'shop.BankName',
            'shop.BankAccountNumber',
            'shop.BankAddress',
            'o1.order_number',
            'o1.status',
            'o1.method',
            'vendor_orders.product_final_amount',
            'vendor_orders.shopping_point_used',
            'o1.shopping_point_exchange_rate',
            'o1.tax',
            'o1.shipping_type',
            'vendor_orders.shopping_point_amount',
            'vendor_orders.shop_coupon_amount',
            DB::raw('vendor_orders.product_final_amount * o1.percent_discount'),
            DB::raw('(CASE WHEN o1.method = "VNPay" THEN ifnull(spc.shipping_cost, 0) '
                    .'ELSE 0 END) AS delivery_fee'),
            DB::raw('vendor_orders.product_final_amount * percent_discount as company_product_discount'),
            DB::raw('vendor_orders.product_final_amount - vendor_orders.product_final_amount * percent_discount as final_amount'),
            'spc.shipping_cost',
            'spc.shipping_partner',
            'spc.shipping_partner_code',
            'o1.payment_to_company_date',
            'o1.payment_to_company_partner',
            'o1.payment_to_company_amount',
            'vinfo.payment_to_merchant_date',
            'vinfo.payment_to_merchant_amount',
            'vinfo.is_debt',
            'vinfo.debt_amount',
            'child_cat.name as child_category',
            'sub_cat.name as sub_category',
            'cat.name as main_category',
            'o1.refund_date',
            'o1.refund_amount',
            'o1.refund_bank',
            'o1.refund_note',
            'o1.count_shop',
        );
        return $order_details;
    }

    public function report_sale_full_data($status, $iscollected, $from = null, $to = null)
    {
        $order_details = $this->querySaleFullReport($status, $iscollected, $from, $to);
        $order_result = DB::query()->fromSub($order_details, 'od')
            ->select('od.order_id','created_at', 'od.shop_id', 'shop_name', 'address','TaxCode','email','order_number','status'
                , DB::raw('SUM(final_amount) as _9_Amount')
                , DB::raw('SUM(shopping_point_used) as _10_Point')
                , 'shopping_point_exchange_rate as _11_Rate'
                , DB::raw('SUM(shopping_point_amount) as _12_Amount_of_Point')
                , DB::raw('SUM(shop_coupon_amount) as _13_Voucher_of_Merchant')
                , DB::raw('SUM(company_product_discount) as _14_Voucher_of_Techhub')
                , 'delivery_fee AS _15_Delivery_fee'
                , DB::raw('SUM(amount) + SUM(amount * tax / 100) + delivery_fee as _16_Total_of_bill')
                , DB::raw('CASE WHEN method = "VNPay" THEN (SUM(final_amount) + delivery_fee) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _17_Other_cards')
                , DB::raw('0 as _18_VISA_JCB_MASTERCARD_UPI_AMEX')
                //, DB::raw('SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _19_Amount_Partner_Must_Pay')
                //, DB::raw('CASE WHEN payment_to_company_amount > 0 THEN payment_to_company_amount / count_shop ELSE SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / COUNT(DISTINCT shop_id)) ELSE 0 END END as _19_Amount_Partner_Must_Pay')
                , DB::raw('payment_to_company_amount / count_shop as _19_Amount_Partner_Must_Pay')
                , DB::raw('payment_to_company_date as _20_PAYMENT_TO_TECHHUB_Date')
                , DB::raw('CASE WHEN method = "VNPay" THEN method ELSE shipping_partner END as _21_PAYMENT_TO_TECHHUB_PARTNER')

                //, DB::raw('SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                //, DB::raw('CASE WHEN payment_to_company_amount > 0 THEN payment_to_company_amount / count_shop ELSE SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / COUNT(DISTINCT shop_id)) ELSE 0 END END as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                , DB::raw('payment_to_company_amount / count_shop as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                , DB::raw('SUM(amount * handling_fee / 100.0) as _23_CHARGE_FEES_VND')
                //, DB::raw('SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) as _24_Amount_Must_Pay_to_Merchant')
                //, DB::raw('CASE WHEN payment_to_merchant_amount > 0 THEN payment_to_merchant_amount ELSE SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) END as _24_Amount_Must_Pay_to_Merchant')
                , DB::raw('payment_to_merchant_amount as _24_Amount_Must_Pay_to_Merchant')
                , DB::raw('payment_to_merchant_date as _25_PAYMENT_to_Merchant_Date')
                //, DB::raw('SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) as _26_Payment_to_Merchant_Amount')
                //, DB::raw('CASE WHEN payment_to_merchant_amount > 0 THEN payment_to_merchant_amount ELSE SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) END as _26_Payment_to_Merchant_Amount')
                , DB::raw('payment_to_merchant_amount as _26_Payment_to_Merchant_Amount')
                , 'BankAccountName'
                , 'BankName'
                , 'BankAddress'
                , 'BankAccountNumber'
                , DB::raw('"" as _31_Payment_to_Carrier_Date')
                , DB::raw('delivery_fee as _32_Payment_to_Carrier_Amount')
                , DB::raw('SUM(amount * handling_fee) / SUM(amount) as handling_fee_rate')
                , 'tax'
                , 'phone'
                , 'shipping_partner_code'
                , 'shipping_type'
                , 'is_handlingfee_collected'
                , 'is_debt as merchant_is_debt'
                , 'debt_amount as merchant_debt_amount'
                , 'refund_date'
                , 'refund_amount'
                , 'refund_bank'
                , 'refund_note'
                )
            ->groupBy('od.order_id', 'od.shop_id', 'shop_name', 'email'
                , 'phone', 'created_at', 'order_number','status', 'is_handlingfee_collected'
                , 'shopping_point_exchange_rate', 'delivery_fee', 'method'
                , 'shipping_partner', 'shipping_partner_code'
                , 'address', 'TaxCode', 'email', 'shop', 'shipping_type',  'BankAccountName'
                , 'BankName'
                , 'BankAccountNumber'
                , 'BankAddress'
                , 'payment_to_company_amount'
                , 'payment_to_company_date'
                , 'payment_to_merchant_amount'
                , 'payment_to_merchant_date'
                , 'is_debt'
                , 'debt_amount'
                , 'refund_date'
                , 'refund_amount'
                , 'refund_bank'
                , 'refund_note'
                , 'count_shop'
                )
            ->orderByDesc('od.order_id')
            ->get();
        return $order_result;
    }

    public function datatables_report_sale_full($status, $iscollected, $from = null, $to = null)
    {
        $order_result = $this->report_sale_full_data($status, $iscollected, $from, $to);
        return Datatables::of($order_result)
            ->editColumn('_9_Amount', function($data) {
                return number_format(round($data->_9_Amount));
            })
            ->editColumn('_10_Point', function($data) {
                return number_format(round($data->_10_Point));
            })
            ->editColumn('_12_Amount_of_Point', function($data) {
                return number_format(round($data->_12_Amount_of_Point));
            })
            ->editColumn('_13_Voucher_of_Merchant', function($data) {
                return number_format(round($data->_13_Voucher_of_Merchant));
            })
            ->editColumn('_14_Voucher_of_Techhub', function($data) {
                return number_format(round($data->_14_Voucher_of_Techhub));
            })
            ->editColumn('_15_Delivery_fee', function($data) {
                return number_format(round($data->_15_Delivery_fee));
            })
            ->editColumn('_16_Total_of_bill', function($data) {
                return number_format(round($data->_16_Total_of_bill));
            })
            ->editColumn('_17_Other_cards', function($data) {
                return number_format(round($data->_17_Other_cards));
            })
            ->editColumn('_19_Amount_Partner_Must_Pay', function($data) {
                return number_format(round($data->_19_Amount_Partner_Must_Pay));
            })
            ->editColumn('_22_PAYMENT_TO_TECHHUB_AMOUNT', function($data) {
                return number_format(round($data->_22_PAYMENT_TO_TECHHUB_AMOUNT));
            })
            ->editColumn('_23_CHARGE_FEES_VND', function($data) {
                return number_format(round($data->_23_CHARGE_FEES_VND));
            })
            ->editColumn('_24_Amount_Must_Pay_to_Merchant', function($data) {
                return number_format(round($data->_24_Amount_Must_Pay_to_Merchant));
            })
            ->editColumn('_26_Payment_to_Merchant_Amount', function($data) {
                return number_format(round($data->_26_Payment_to_Merchant_Amount));
            })
            ->editColumn('_32_Payment_to_Carrier_Amount', function($data) {
                return number_format(round($data->_32_Payment_to_Carrier_Amount));
            })
            ->editColumn('merchant_debt_amount', function($data) {
                return number_format(round($data->merchant_debt_amount));
            })
            ->editColumn('handling_fee_rate', function($data) {
                return $data->handling_fee_rate.'%';
            })
            ->editColumn('tax', function($data) {
                return $data->tax.'%';
            })
            ->addColumn('action', function($data) {
                $orders = '<a href="javascript:;" data-href="'. route('admin-order-edit',$data->order_id) .'" class="delivery" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Delivery Status</a>';
                return '<div class="godropdown" ><button class="go-dropdown-toggle "> '.($data->is_handlingfee_collected == 1 ? 'Đã thu' : 'Chưa thu').'<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show', $data->order_id) . '" > <i class="fas fa-eye"></i> Details</a>'.$orders.'</div></div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function export_report_sale_full($status, $iscollected, $from = null, $to = null)
    {
        $order_result = $this->report_sale_full_data($status, $iscollected, $from, $to);
        $file_name = 'sale-full-report_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new SaleFullReport($order_result), $file_name, null, ['order_id']);
    }

    //SaleFullReportDetail
    public function dataSaleFullReportDetail($status, $iscollected, $from = null, $to = null)
    {
        $order_details = $this->querySaleFullReport($status, $iscollected, $from, $to);
        $order_result = DB::query()->fromSub($order_details, 'od')
            ->select('od.order_id','created_at', 'od.shop_id', 'shop_name', 'address','TaxCode','email','order_number','status'
                , DB::raw('final_amount as _9_Amount')
                , DB::raw('shopping_point_used as _10_Point')
                , 'shopping_point_exchange_rate as _11_Rate'
                , DB::raw('shopping_point_amount as _12_Amount_of_Point')
                , DB::raw('shop_coupon_amount as _13_Voucher_of_Merchant')
                , DB::raw('company_product_discount as _14_Voucher_of_Techhub')
                , 'delivery_fee AS _15_Delivery_fee'
                , DB::raw('0 as _18_VISA_JCB_MASTERCARD_UPI_AMEX')
                , DB::raw('payment_to_company_amount as _19_Amount_Partner_Must_Pay')
                , DB::raw('payment_to_company_date as _20_PAYMENT_TO_TECHHUB_Date')
                , DB::raw('CASE WHEN method = "VNPay" THEN method ELSE shipping_partner END as _21_PAYMENT_TO_TECHHUB_PARTNER')
                , DB::raw('payment_to_company_amount as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                , DB::raw('amount * handling_fee / 100.0 as _23_CHARGE_FEES_VND')
                , DB::raw('payment_to_merchant_amount as _24_Amount_Must_Pay_to_Merchant')
                , DB::raw('payment_to_merchant_date as _25_PAYMENT_to_Merchant_Date')
                , DB::raw('payment_to_merchant_amount as _26_Payment_to_Merchant_Amount')
                , 'BankAccountName'
                , 'BankName'
                , 'BankAddress'
                , 'BankAccountNumber'
                , DB::raw('"" as _31_Payment_to_Carrier_Date')
                , DB::raw('delivery_fee as _32_Payment_to_Carrier_Amount')
                , DB::raw('handling_fee as handling_fee_rate')
                , 'tax'
                , 'phone'
                , 'shipping_partner_code'
                , 'shipping_type'
                , 'is_handlingfee_collected'
                , 'is_debt as merchant_is_debt'
                , 'debt_amount as merchant_debt_amount'
                , 'product_name'
                , 'child_category'
                , 'sub_category'
                , 'main_category'
                , 'refund_date'
                , 'refund_amount'
                , 'refund_bank'
                , 'refund_note'
                )
            ->orderByDesc('od.order_id')
            ->get();
        return $order_result;
    }

    public function datatablesSaleFullReportDetail($status, $iscollected, $from = null, $to = null)
    {
        $order_result = $this->dataSaleFullReportDetail($status, $iscollected, $from, $to);
        return Datatables::of($order_result)
            ->editColumn('_9_Amount', function($data) {
                return number_format(round($data->_9_Amount));
            })
            ->editColumn('_10_Point', function($data) {
                return number_format(round($data->_10_Point));
            })
            ->editColumn('_12_Amount_of_Point', function($data) {
                return number_format(round($data->_12_Amount_of_Point));
            })
            ->editColumn('_13_Voucher_of_Merchant', function($data) {
                return number_format(round($data->_13_Voucher_of_Merchant));
            })
            ->editColumn('_14_Voucher_of_Techhub', function($data) {
                return number_format(round($data->_14_Voucher_of_Techhub));
            })
            ->editColumn('_15_Delivery_fee', function($data) {
                return number_format(round($data->_15_Delivery_fee));
            })
            ->editColumn('_19_Amount_Partner_Must_Pay', function($data) {
                return number_format(round($data->_19_Amount_Partner_Must_Pay));
            })
            ->editColumn('_22_PAYMENT_TO_TECHHUB_AMOUNT', function($data) {
                return number_format(round($data->_22_PAYMENT_TO_TECHHUB_AMOUNT));
            })
            ->editColumn('_23_CHARGE_FEES_VND', function($data) {
                return number_format(round($data->_23_CHARGE_FEES_VND));
            })
            ->editColumn('_24_Amount_Must_Pay_to_Merchant', function($data) {
                return number_format(round($data->_24_Amount_Must_Pay_to_Merchant));
            })
            ->editColumn('_26_Payment_to_Merchant_Amount', function($data) {
                return number_format(round($data->_26_Payment_to_Merchant_Amount));
            })
            ->editColumn('_32_Payment_to_Carrier_Amount', function($data) {
                return number_format(round($data->_32_Payment_to_Carrier_Amount));
            })
            ->editColumn('merchant_debt_amount', function($data) {
                return number_format(round($data->merchant_debt_amount));
            })
            ->editColumn('handling_fee_rate', function($data) {
                return $data->handling_fee_rate.'%';
            })
            ->editColumn('tax', function($data) {
                return $data->tax.'%';
            })
            ->addColumn('action', function($data) {
                $orders = '<a href="javascript:;" data-href="'. route('admin-order-edit',$data->order_id) .'" class="delivery" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Delivery Status</a>';
                return '<div class="godropdown" ><button class="go-dropdown-toggle "> '.($data->is_handlingfee_collected == 1 ? 'Đã thu' : 'Chưa thu').'<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show', $data->order_id) . '" > <i class="fas fa-eye"></i> Details</a>'.$orders.'</div></div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function saleFullReportDetail()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.sale-full-report-detail', compact('now'));
    }

    public function exportSaleFullReportDetail($status, $iscollected, $from = null, $to = null)
    {
        $order_result = $this->dataSaleFullReportDetail($status, $iscollected, $from, $to);
        $file_name = 'sale-full-report-detail_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new SaleFullReportDetail($order_result), $file_name, null, ['order_id']);
    }

    #endregion SALE FULL REPORT END



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
        return view('admin.order.index', compact('now'));
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

    public function edit_invoice($id)
    {
        $data = Order::find($id);

        $order_details = DB::table('vendor_orders')
        ->where('order_id','=',$id)
        ->join('orders as o1', 'vendor_orders.order_id', '=', 'o1.id')
        ->join('users as shop', 'vendor_orders.user_id', '=', 'shop.id')
        ->join('products as prod', 'vendor_orders.product_id', '=', 'prod.id')
        ->join('categories as cat', 'prod.category_id', '=', 'cat.id')
        ->leftJoin('subcategories as sub_cat', 'prod.subcategory_id', '=', 'sub_cat.id')
        ->leftJoin('childcategories as child_cat', 'prod.childcategory_id', '=', 'child_cat.id')
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

        $order_result = DB::query()->fromSub($order_details, 'od')
            ->select('od.order_id', 'od.shop_id', 'shop_name','email','phone','created_at','order_number','status'
                , DB::raw('SUM(amount) as total_amount')
                , DB::raw('SUM(shopping_point_used) as total_collected_sp')
                , DB::raw('SUM(shopping_point_amount) as total_collected_sp_amount')
                , DB::raw('SUM(shopping_point_payment_remain) as total_payment_amount')
                , DB::raw('SUM(amount * handling_fee / 100.0) as merchant_handling_fee')
                , 'is_handlingfee_collected'
                )
            ->groupBy('od.order_id', 'od.shop_id', 'shop_name', 'email',
                 'phone', 'created_at', 'order_number','status', 'is_handlingfee_collected')
            ->get();

        return view('admin.order.invoice-add-info',compact('data', 'order_result'));
    }

    private function shop_handling_fee($id, $shop_id)
    {
        $order_details = DB::table('vendor_orders')
            ->where('order_id','=',$id)
            ->where('vendor_orders.user_id','=',$shop_id)
            ->join('products as prod', 'vendor_orders.product_id', '=', 'prod.id')
            ->join('categories as cat', 'prod.category_id', '=', 'cat.id')
            ->leftJoin('subcategories as sub_cat', 'prod.subcategory_id', '=', 'sub_cat.id')
            ->leftJoin('childcategories as child_cat', 'prod.childcategory_id', '=', 'child_cat.id')
            ->select(
                'vendor_orders.user_id as shop_id',
                'vendor_orders.order_id as order_id',
                'vendor_orders.product_id',
                'vendor_orders.qty',
                'vendor_orders.price',
                DB::raw('vendor_orders.price + vendor_orders.price_shopping_point_amount  as amount'),
                'vendor_orders.shopping_point_used',
                'vendor_orders.shopping_point_amount',
                'vendor_orders.shopping_point_payment_remain',
                'vendor_orders.is_handlingfee_collected',
                DB::raw('(CASE WHEN child_cat.handling_fee > 0 THEN child_cat.handling_fee '
                .'WHEN sub_cat.handling_fee > 0 THEN sub_cat.handling_fee '
                .'ELSE cat.handling_fee END) AS handling_fee')
            );

        $data = DB::query()->fromSub($order_details, 'od')
            ->select(
                DB::raw('SUM(amount * handling_fee / 100.0) as merchant_handling_fee'),
                DB::raw('SUM(amount * handling_fee / 100.0) / SUM(amount) as merchant_handling_fee_rate')
                )
            ->get();
        $result[0] = $data->first()->merchant_handling_fee;
        $result[1] = $data->first()->merchant_handling_fee_rate;
        return $result;
    }

    //*** POST Request
    public function collecthandlingfee($id, $shopid)
    {
        $vendor_order = VendorOrder::where('order_id','=',$id)
            ->where('user_id','=',$shopid)
            ->update(['is_handlingfee_collected' => 1]);
        $msg = 'Handling fee collect succeed!';
        return response()->json($msg);
    }

    //*** POST Request
    public function collecthandlingfee_new($id, $shopid)
    {
        $vendor_order = VendorOrder::where('order_id','=',$id)
            ->where('user_id','=',$shopid)
            ->update(['is_handlingfee_collected' => 1]);

        $issuer = Auth::guard('admin')->user();
        $log = OrderHandlingFeeLog::where('order_id','=', $id)->where('shop_id','=', $shopid)->first();
        if($log == null){
            $log = new OrderHandlingFeeLog;
            $log->issuer_id = $issuer->id;
            $log->order_id = $id;
            $log->shop_id = $shopid;
            $handling_fee = $this->shop_handling_fee($id, $shopid);
            $log->handling_fee_amount = $handling_fee[0];
            $log->handling_fee_rate = $handling_fee[1];
            $log->save();
        }
        $log['issuer'] = $log->issuer()->first();
        return $log;
    }

    //*** POST Request
    public function rollbackhandlingfee($id, $shopid)
    {
        $vendor_order = VendorOrder::where('order_id','=',$id)
            ->where('user_id','=',$shopid)
            ->update(['is_handlingfee_collected' => 0]);
        $msg = 'Handling fee rollback succeed!';
        return response()->json($msg);
    }

    //*** POST Request
    public function update_invoice_info(Request $request, $id)
    {
        $data = Order::findOrFail($id);
        $input = $request->all();
        $this->update_invoice_info_sub($data,$input);
        $msg = 'Updated Successfully.';
        return response()->json($msg);
    }

    public function update_invoice_info_sub($data, $input)
    {
        $issuer = Auth::guard('admin')->user();
        $log_payment = new OrderAdminTrackLog;
        $log_refund = new OrderAdminTrackLog;
        $data_old = Order::find($data->id);
        if(isset($input['payment_to_company_date']))
            $data->payment_to_company_date=Carbon::parse($input['payment_to_company_date'])->format('Y-m-d');
        $data->payment_to_company_partner=$input['payment_to_company_partner'];
        $data->payment_to_company_amount=$input['payment_to_company_amount'];
        $data->refund_amount=$input['refund_amount'];
        if(isset($input['refund_date'])){
            $data->refund_date=Carbon::parse($input['refund_date'])->format('Y-m-d');
            $data->refund_by_name=$issuer->name;
        }

        $data->refund_bank=$input['refund_bank'];
        $data->refund_note=$input['refund_note'];
        $data->save();
        $log_payment->write_techhub_payment($data_old, $data, $issuer->id);
        $log_refund->write_refund($data_old, $data, $issuer->id);
        $vendor_ids = $data->vendororders()->select('user_id')->distinct()->get();
        foreach($vendor_ids as $vid){
            $info = OrderVendorInfo::where('order_id','=',$data->id)->where('shop_id','=',$vid->user_id)->first();
            if($info==null){
                $info = new OrderVendorInfo;
                $info->order_id = $data->id;
                $info->shop_id = $vid->user_id;
            }
            $log = new OrderAdminTrackLog;
            $old = OrderVendorInfo::where('order_id','=',$data->id)->where('shop_id','=',$vid->user_id)->first();

            if($input['is_debt_'.$vid->user_id] == 0){
                if($input['debt_amount_'.$vid->user_id] > 0)
                    $input['debt_amount_'.$vid->user_id] = 0;
            }

            $info->is_paid=$input['is_paid_'.$vid->user_id];
            if($info->is_paid){
                if(isset($input['payment_to_merchant_date_'.$vid->user_id])){
                    $info->payment_to_merchant_date=Carbon::parse($input['payment_to_merchant_date_'.$vid->user_id])->format('Y-m-d');
                }
                $info->payment_to_merchant_amount=$input['payment_to_merchant_amount_'.$vid->user_id];
            }

            $info->is_debt=$input['is_debt_'.$vid->user_id];
            $info->debt_amount=$input['debt_amount_'.$vid->user_id];
            $info->save();
            $log->write_vendor_info($old, $info, $issuer->id);
        }

        if($input['track_text'])
        {
            $title = ucwords($input['status']);
            $track = new OrderTrack;
            $track->order_id = $data->id;
            $track->title = $title;
            $track->text = $input['track_text'];
            $track->issuer_id = $issuer->id;
            $track->save();
        }
        return true;
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Logic Section
        $data = Order::findOrFail($id);
        $input = $request->all();
        if(!($input['refund_amount'] > 0)){
            unset($input['refund_date']);
        }

        try{
            $this->update_invoice_info_sub($data,$input);
        }
        catch (\Exception $e){
            $msg = $e->getMessage();
            return response()->json($msg);
       }


        if ($data->status == "completed"){
            // Then Save Without Changing it.
            $input['status'] = "completed";
            try{
                $data->update($input);
            }
            catch (\Exception $e){
                $msg = $e->getMessage();
                return response()->json($msg);
           }

            //--- Logic Section Ends

            //--- Redirect Section
            $msg = 'Status Updated Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends

        }
        else if ($data->status == "declined"){
            $msg = 'Status Updated Successfully.';
            return response()->json($msg);
        }
        else{
            $gs = Generalsetting::findOrFail(1);
            $consumer = $data->user()->first();
            if ($input['status'] == "completed"){

                $total_items = $data->vendororders->count();
                $finished_items = $data->vendororders->whereIn('status', ['completed','declined'])->count();
                if($total_items > $finished_items){
                    $msg = 'Please Complete All Vendor Order First!';
                    return response()->json($msg);
                }
                $completed_items = $data->vendororders->where('status', 'completed');
                $input['completed_at'] = date("Y-m-d H:m:s");
                $level_configs = AffiliateLevelConfig::orderBy('level', 'asc')->get();
                $percent_discount = $data->total_product_final_amount > 0 ? $data->coupon_discount / $data->total_product_final_amount : 0;
                foreach($completed_items as $vorder)
                {
                    $shop = User::findOrFail($vorder->user_id);
                    $product_amount = $vorder->qty * $vorder->unit_price + $vorder->qty * $vorder->item_price_shopping_point;
                    $shop_discount = $vorder->shop_coupon_amount;
                    $company_discount = $vorder->product_final_amount * $percent_discount;
                    $product_amount_for_rebate = $product_amount - $shop_discount - $company_discount;

                    //check pay shopping point for shop
                    if($vorder->is_shopping_point_used == 1 && $vorder->status != 'declined'){
                        $shop_point_log = new UserPointLog;
                        $shop_point_log->receive_payment_shopping_point($shop, $id, $vorder, $product_amount, $gs);
                        $shop->shopping_point = $shop->shopping_point + $vorder->shopping_point_used;
                        $shop->update();
                        $shop_point_log->save();
                    }

                    //code in default
                    $shop->current_balance = $shop->current_balance + $vorder->price;
                    $shop->update();

                    if($vorder->is_rebate_paid != 1){
                        //pay for rebate
                        $rebate_bonus = $consumer->rebate_bonus();
                        if($rebate_bonus > 0){

                            $point_log_rebate = new UserPointLog;
                            $vorder->rebate_bonus = $rebate_bonus;
                            $vorder->rebate_in = $gs->rebate_in;
                            $vorder->rebate_amount = $product_amount_for_rebate * $rebate_bonus / 100.0;
                            $point_log_rebate->rebate($data, $consumer, $vorder, $product_amount_for_rebate, $gs);
                            $consumer->save();
                            $vorder->is_rebate_paid = 1;
                            $point_log_rebate->save();
                            $vorder->save();
                        }

                        //pay for affiliate
                        $referral_head_users = explode(", ", $consumer->referral_user_ids);
                        for($index = 0; $index < $level_configs->count(); $index++)
                        {
                            $level_config = $level_configs[$index];
                            if($index < count($referral_head_users) && isset($level_config)){
                                if(is_numeric($referral_head_users[$index])){
                                    $level_user = User::findOrFail($referral_head_users[$index]);
                                    $level_package_config = DB::table('affiliate_level_package_configs as lvlp')
                                    ->where('affiliate_level_id','=', $level_config->id)
                                    ->join('package_configs as pc', 'lvlp.package_config_id', '=', 'pc.id')
                                    ->where('pc.name','=', $level_user->rank_name())
                                    ->select('pc.id', 'pc.name', 'lvlp.affiliate_bonus')
                                    ->first();

                                    if(isset($level_package_config)){
                                        if($level_package_config->affiliate_bonus > 0){
                                            $level_user_point_log = new UserPointLog;
                                            $level_user_point_log->user_id = $level_user->id;
                                            $level_user_point_log->log_type = 'Affiliate Bonus';
                                            $level_user_point_log->order_ref_id = $id;
                                            $level_user_point_log->reward_point_balance = isset($level_user->reward_point) ? $level_user->reward_point : 0;
                                            $level_user_point_log->shopping_point_balance = isset($level_user->shopping_point) ? $level_user->shopping_point : 0;
                                            $level_user_point_log->exchange_rate = $level_package_config->affiliate_bonus;
                                            $level_user_point_log->note = 'Paid from vendor_order ['.$vorder->id.'] in bonus level ['.$level_config->level.']';
                                            $level_user_point_log->descriptions = 'Bạn được hưởng [L'.$level_config->level.'] cho sản phẩm ['.$vorder->product_name.'] của đơn hàng số ['.$vorder->order_number.']';
                                            $level_user_point_log->reward_point = 0;
                                            $level_user_point_log->shopping_point = 0;
                                            $level_user_point_log->affiliate_exchange_in = $gs->affiliate_exchange_in;
                                            $level_user_point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;

                                            switch($gs->affiliate_exchange_in){
                                                case 0:
                                                    $level_user_point_log->reward_point = $product_amount_for_rebate * $level_user_point_log->exchange_rate / 100.0;
                                                    $level_user->reward_point = $level_user->reward_point + $level_user_point_log->reward_point;
                                                    break;
                                                case 1:
                                                    $level_user_point_log->shopping_point = $product_amount_for_rebate * $level_user_point_log->exchange_rate / 100.0;
                                                    // $level_user_point_log->shopping_point = $order_amount * $level_config->level_value / 100.0 / $gs->sp_vnd_exchange_rate;
                                                    $level_user->shopping_point = $level_user->shopping_point + $level_user_point_log->shopping_point;
                                                    break;
                                            }

                                            $level_user_point_log->amount = $product_amount_for_rebate;
                                            $level_user->save();
                                            $level_user_point_log->save();
                                        }
                                    }

                                }
                            }
                        } // end foreach pay for affiliate level
                    }// end is_rebate_paid
                } // end foreach vendor_orders

                if( User::where('id', $data->affilate_user)->exists() ){
                    $auser = User::where('id', $data->affilate_user)->first();
                    $auser->affilate_income += $data->affilate_charge;
                    $auser->update();
                }

                $gs = Generalsetting::findOrFail(1);
                if($gs->is_smtp == 1)
                {
                    $maildata = [
                        'to' => $data->customer_email,
                        'subject' => 'Your order '.$data->order_number.' is Confirmed!',
                        'body' => "Hello ".$data->customer_name.","."\n Thank you for shopping with us. We are looking forward to your next visit.",
                    ];

                    $mailer = new GeniusMailer();
                    $mailer->sendCustomMail($maildata);
                }
                else
                {
                    $to = $data->customer_email;
                    $subject = 'Your order '.$data->order_number.' is Confirmed!';
                    $msg = "Hello ".$data->customer_name.","."\n Thank you for shopping with us. We are looking forward to your next visit.";
                    $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                    mail($to,$subject,$msg,$headers);
                }
            } // end if status == completed
            else if ($input['status'] == "declined"){
                $cart = unserialize(bzdecompress(utf8_decode($data->cart)));

                try{
                    foreach($cart->items as $prod)
                    {
                        $x = (string)$prod['stock'];
                        if($x != null)
                        {
                            $product = Product::find($prod['item']['id']);
                            if(isset($product)){
                                $product->stock = $product->stock + $prod['qty'];
                                $product->update();
                            }
                        }
                    }
                    foreach($cart->items as $prod)
                    {
                        $x = (string)$prod['size_qty'];
                        if(!empty($x))
                        {
                            $product = Product::find($prod['item']['id']);
                            if(isset($product)){
                                $x = (int)$x;
                                $temp = $product->size_qty;
                                $temp[$prod['size_key']] = $x;
                                $temp1 = implode(',', $temp);
                                $product->size_qty =  $temp1;
                                $product->update();
                            }
                        }
                    }
                }
                catch (\Exception $e){
                    $msg = $e->getMessage();
                }
                if($gs->is_smtp == 1)
                {
                    $maildata = [
                        'to' => $data->customer_email,
                        'subject' => 'Your order '.$data->order_number.' is Declined!',
                        'body' => "Hello ".$data->customer_name.","."\n We are sorry for the inconvenience caused. We are looking forward to your next visit.",
                    ];
                    $mailer = new GeniusMailer();
                    $mailer->sendCustomMail($maildata);
                }
                else
                {
                    $to = $data->customer_email;
                    $subject = 'Your order '.$data->order_number.' is Declined!';
                    $msg = "Hello ".$data->customer_name.","."\n We are sorry for the inconvenience caused. We are looking forward to your next visit.";
                    $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                    mail($to,$subject,$msg,$headers);
                }

                $sp_declined =  $data->vendororders->where('status', 'declined')->sum('shopping_point_used');
                $sp_handling = $data->shopping_point_used - $sp_declined;
                if($sp_handling > 0){
                    $point_log_declined = new UserPointLog;
                    $point_log_declined->user_id = $consumer->id;
                    $point_log_declined->log_type = 'Order Declined';
                    $point_log_declined->order_ref_id = $id;
                    $point_log_declined->reward_point_balance = isset($consumer->reward_point) ? $consumer->reward_point : 0;
                    $point_log_declined->shopping_point_balance = isset($consumer->shopping_point) ? $consumer->shopping_point : 0;
                    $point_log_declined->exchange_rate = 0;
                    $point_log_declined->note = 'Return from order ['.$id.'] - SP Declined = '.$sp_declined;
                    $point_log_declined->descriptions = 'Bạn được hoàn trả shopping point của đơn hàng số ['.$data->order_number.']';
                    $point_log_declined->reward_point = 0;
                    $point_log_declined->shopping_point = $sp_handling;
                    $point_log_declined->amount = $data->pay_amount1;
                    $point_log_declined->sp_vnd_exchange_rate = $data->shopping_point_exchange_rate;
                    $consumer->shopping_point = $consumer->shopping_point + $sp_handling;
                    $consumer->save();
                    $point_log_declined->save();
                }

                $shippings = $data->orderconsumershippingcosts()->get();
                if($shippings->count() > 0){
                    $status_vtel = config('app.viettel_post.order_status');
                    foreach($shippings as $sp){
                        $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->updateorderstatus($sp->shipping_partner_code, $status_vtel['cancel_order'], 'Cancel due to declined order');
                    }
                }
                $vorder_update = VendorOrder::where('order_id','=',$id)->update(['status' => $input['status']]);
            }//end if ($input['status'] == "declined")
            else if ($input['status'] == "on delivery"){
                $input['ondelivery_at'] = date("Y-m-d H:m:s");
            }

            try{
                $data->update($input);
            }
            catch (\Exception $e){
                $msg = $e->getMessage();
                return response()->json($msg);
            }
            // if($request->track_text)
            // {
            //         $title = ucwords($data->status);
            //         $ck = OrderTrack::where('order_id','=',$id)->where('title','=',$title)->first();

            //         $track = new OrderTrack;
            //         $track->order_id = $id;
            //         $track->title = $title;
            //         $track->text = $request->track_text;
            //         $track->save();

            //         // if($ck){
            //         //     $ck->order_id = $id;
            //         //     $ck->title = $title;
            //         //     $ck->text = $request->track_text;
            //         //     $ck->update();
            //         // }
            //         // else {
            //         //     $data = new OrderTrack;
            //         //     $data->order_id = $id;
            //         //     $data->title = $title;
            //         //     $data->text = $request->track_text;
            //         //     $data->save();
            //         // }
            // }

            //$order = VendorOrder::where('order_id','=',$id)->update(['status' => $input['status']]);
            //--- Redirect Section
            $msg = 'Status Updated Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends
        }
        //--- Redirect Section
        $msg = 'Status Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }

    public function pending()
    {
        return view('admin.order.pending');
    }

    public function processing()
    {
        return view('admin.order.processing');
    }

    public function ondelivery()
    {
        return view('admin.order.ondelivery');
    }


    public function completed()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.completed', compact('now'));
    }

    public function declined()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.declined', compact('now'));
    }

    public function ordersreport()
    {
        return view('admin.order.reports');
    }

    public function sale_full_report()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.sale-full-report', compact('now'));
    }

    public function merchantp_report()
    {
        $from =  Carbon::now()->format('Y-m-d');
        // Carbon::parse(Config::get('global.formdate'))->format('Y-m-d');
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.merchant-payment-report', compact('from','now'));
    }

    public function delivery_partner()
    {
        return view('admin.order.delivery_partner');
    }

    public function remainingp()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.remainingp', compact('now'));
    }

    public function show($id)
    {

        if(!Order::where('id',$id)->exists())
        {
            return redirect()->route('admin.dashboard')->with('unsuccess',__('Sorry the page does not exist.'));
        }
        $order = Order::findOrFail($id);
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        return view('admin.order.details',compact('order','cart'));
    }

    public function invoice($id)
    {
        $order = Order::findOrFail($id);
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        // $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
        return view('admin.order.invoice',compact('order','cart'));
    }

    public function emailsub(Request $request)
    {
        $gs = Generalsetting::findOrFail(1);
        if($gs->is_smtp == 1)
        {
            $data = 0;
            $datas = [
                    'to' => $request->to,
                    'subject' => $request->subject,
                    'body' => $request->message,
            ];

            $mailer = new GeniusMailer();
            $mail = $mailer->sendCustomMail($datas);
            if($mail) {
                $data = 1;
            }
        }
        else
        {
            $data = 0;
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            $mail = mail($request->to,$request->subject,$request->message,$headers);
            if($mail) {
                $data = 1;
            }
        }

        return response()->json($data);
    }

    public function printpage($id)
    {
        $order = Order::findOrFail($id);
        // $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        return view('admin.order.print',compact('order','cart'));
    }

    public function license(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart->items[$request->license_key]['license'] = $request->license;
        $order->cart = utf8_encode(bzcompress(serialize($cart), 9));
        $order->update();
        $msg = 'Successfully Changed The License Key.';
        return response()->json($msg);
    }

    public function view_vnpay_log($id = -1)
    {
        $config = config('app.vnpay');
        $order = null;
        if($id == -1){
            $order = Order::orderByDesc('id')->take(1)->first();
        }
        else{
            $order = Order::find($id);
        }
        $url = VNPay::createPayment($order);
        $logs = OrderVNPayTrackLog::where('order_id', $order->id)->get();
        $l_ipn = null;
        foreach($logs as $l){
            if($l->title == 'IPN'){
                $l_ipn[] = $l;
                $l_ipn[] = unserialize($l->content);
            }
        }
        $ipn = DevelopmentNote::orderByDesc('id')->take(10)->get();
        $result = array($order, $url, $logs, $ipn, $config, $l_ipn);
        dd($result) ;
    }

    //*** JSON Request
    public function datatables_remainingp($status, $from = null, $to = null, $key = null, $delivery_code = 'null')
    {
        $statuss = array('completed');
        $query = Order::whereIn('status', $statuss);

        if($status == 1){
            $query = $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('order_vendor_infos')
                    ->whereColumn('order_vendor_infos.order_id', 'orders.id')
                    ->where('order_vendor_infos.is_paid', 1)
                    ;
            });
        }
        else if($status == 0){
            $query = $query->where(function ($q){
                $q = $q->where(function ($q){
                    $q = $q->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('order_vendor_infos')
                            ->whereColumn('order_vendor_infos.order_id', 'orders.id')
                            ;
                    });
                })
                ->orWhere(function ($q) {
                    $q = $q->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('order_vendor_infos')
                            ->whereColumn('order_vendor_infos.order_id', 'orders.id')
                            ->where('order_vendor_infos.is_paid', 0)
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

        $datas = $query->orderBy('id','desc')
            ->limit(200)
            ->get();
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
            ->editColumn('pay_amount2', function(Order $data) {
                return $data->currency_sign . number_format(round($data->pay_amount2 * $data->currency_value , 2));
            })
            ->addColumn('action', function(Order $data) {
                $orders = '<a href="javascript:;" data-href="'. route('admin-order-edit',$data->id) .'" class="delivery" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Delivery Status</a>';
                $resend_order_info = '<a href="javascript:;" data-href="'. route('admin-send-order-info',$data->id) .'" class="resend-order-info"><i class="fas fa-envelope"></i>Send Order Info To Customer</a>';
                $resend_order_info_vendor = '<a href="javascript:;" data-href="'. route('admin-send-order-info-vendors',$data->id) .'" class="resend-order-info-vendors"><i class="fas fa-envelope"></i>Send Order Info To Vendors</a>';
                // $invoice_update = '<a href="javascript:;" data-href="'. route('admin-order-edit-invoice',$data->id) .'" class="invoice-update" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Update Invoice Info</a>';
                return '<div class="godropdown"><button class="go-dropdown-toggle"> Actions<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show',$data->id) . '" > <i class="fas fa-eye"></i> Details</a><a href="javascript:;" class="send" data-email="'. $data->customer_email .'" data-toggle="modal" data-target="#vendorform"><i class="fas fa-envelope"></i> Send</a>'.$resend_order_info.$resend_order_info_vendor.'<a href="javascript:;" data-href="'. route('admin-order-track',$data->id) .'" class="track" data-toggle="modal" data-target="#modal1"><i class="fas fa-truck"></i> Track Order</a>'.$orders.'</div></div>';
            })
            ->rawColumns(['id','action'])
            ->toJson();
    }

    public function report_merchantp_data($status, $iscollected, $from = null, $to = null)
    {
        $statuss=array($status);
        if($status=='all'){
            $statuss = array('pending','processing','completed','declined','on delivery');
        }
        $iscollecteds=array($iscollected);
        if($iscollected=='all'){
            $iscollecteds = array(1,0);
        }

        $number = 'O20211205031201OY';

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
            ->whereIn('status',$statuss)
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
            );

        $order_details = DB::table('vendor_orders')
        ->joinSub($orders, 'o1', function ($join) {
            $join->on('vendor_orders.order_id', '=', 'o1.id');
        })
        ->join('users as shop', 'vendor_orders.user_id', '=', 'shop.id')
        ->leftJoin('products as prod', 'vendor_orders.product_id', '=', 'prod.id')
        ->leftJoin('categories as cat', 'prod.category_id', '=', 'cat.id')
        ->leftJoin('subcategories as sub_cat', 'prod.subcategory_id', '=', 'sub_cat.id')
        ->leftJoin('childcategories as child_cat', 'prod.childcategory_id', '=', 'child_cat.id')
        ->leftJoin('order_consumer_shipping_costs as spc', function ($join) {
            $join->on('o1.id', '=', 'spc.order_id')
                ->on('shop.id', '=', 'spc.shop_id');
        })
        ->join('order_vendor_infos as vinfo', function ($join) {
            $join->on('o1.id', '=', 'vinfo.order_id')
                ->on('shop.id', '=', 'vinfo.shop_id');
        })
        ->whereBetween('vinfo.payment_to_merchant_date',[$from, $to])
        ->where('vinfo.is_paid',1)
        ->whereIn('is_handlingfee_collected',$iscollecteds)
        ->select(
            'shop.affilate_code as shop_id',
            'o1.id as order_id',
            'vendor_orders.product_id',
            'prod.name as product_name',
            'shop.shop_name as shop_name',
            'vendor_orders.qty',
            'vendor_orders.price',
            DB::raw('vendor_orders.price + vendor_orders.price_shopping_point_amount as amount'),
            'vendor_orders.shopping_point_payment_remain',
            'shop.phone',
            'o1.is_online_payment',
            'o1.completed_at',
            'vendor_orders.is_handlingfee_collected',
            DB::raw('(CASE WHEN child_cat.handling_fee > 0 THEN child_cat.handling_fee '
                .'WHEN sub_cat.handling_fee > 0 THEN sub_cat.handling_fee '
                .'ELSE cat.handling_fee END) AS handling_fee'),
            'shop.affilate_code',
            DB::raw('DATE_FORMAT(o1.created_at,"%Y-%m-%d") as created_at'),
            'shop.name as shop',
            'shop.address',
            'shop.reg_number',
            'shop.email',
            'shop.BankAccountName',
            'shop.BankName',
            'shop.BankAccountNumber',
            'shop.BankAddress',
            'o1.order_number',
            'o1.status',
            'o1.method',
            'vendor_orders.product_final_amount',
            'vendor_orders.shopping_point_used',
            'o1.shopping_point_exchange_rate',
            'o1.tax',
            'o1.shipping_type',
            'vendor_orders.shopping_point_amount',
            'vendor_orders.shop_coupon_amount',
            DB::raw('vendor_orders.product_final_amount * o1.percent_discount'),
            DB::raw('(CASE WHEN o1.method = "VNPay" THEN ifnull(spc.shipping_cost, 0) '
                    .'ELSE 0 END) AS delivery_fee'),
            DB::raw('vendor_orders.product_final_amount * percent_discount as company_product_discount'),
            DB::raw('vendor_orders.product_final_amount - vendor_orders.product_final_amount * percent_discount as final_amount'),
            'spc.shipping_cost',
            'spc.shipping_partner',
            'spc.shipping_partner_code',
            'o1.payment_to_company_date',
            'o1.payment_to_company_partner',
            'o1.payment_to_company_amount',
            'vinfo.payment_to_merchant_date',
            'vinfo.payment_to_merchant_amount',
            'vinfo.is_debt',
            'vinfo.debt_amount',
            'o1.refund_date',
            'o1.refund_amount',
            'o1.refund_bank',
            'o1.refund_note',
            'o1.count_shop',

        );

        $order_result = DB::query()->fromSub($order_details, 'od')
            ->select('od.order_id','created_at', 'od.shop_id', 'shop_name', 'address','reg_number as TaxCode','email','order_number','status'
                , DB::raw('SUM(final_amount) as _9_Amount')
                , DB::raw('SUM(shopping_point_used) as _10_Point')
                , 'shopping_point_exchange_rate as _11_Rate'
                , DB::raw('SUM(shopping_point_amount) as _12_Amount_of_Point')
                , DB::raw('SUM(shop_coupon_amount) as _13_Voucher_of_Merchant')
                , DB::raw('SUM(company_product_discount) as _14_Voucher_of_Techhub')
                , 'delivery_fee AS _15_Delivery_fee'
                , DB::raw('SUM(amount) + SUM(amount * tax / 100) + delivery_fee as _16_Total_of_bill')
                , DB::raw('CASE WHEN method = "VNPay" THEN (SUM(final_amount) + delivery_fee) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _17_Other_cards')
                , DB::raw('0 as _18_VISA_JCB_MASTERCARD_UPI_AMEX')
                //, DB::raw('SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _19_Amount_Partner_Must_Pay')
                //, DB::raw('CASE WHEN payment_to_company_amount > 0 THEN payment_to_company_amount / count_shop ELSE SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / COUNT(DISTINCT shop_id)) ELSE 0 END END as _19_Amount_Partner_Must_Pay')
                , DB::raw('payment_to_company_amount / count_shop as _19_Amount_Partner_Must_Pay')
                , DB::raw('payment_to_company_date as _20_PAYMENT_TO_TECHHUB_Date')
                , DB::raw('CASE WHEN method = "VNPay" THEN method ELSE shipping_partner END as _21_PAYMENT_TO_TECHHUB_PARTNER')
                //, DB::raw('SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / count_shop) ELSE 0 END as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                //, DB::raw('CASE WHEN payment_to_company_amount > 0 THEN payment_to_company_amount / count_shop ELSE SUM(final_amount) + delivery_fee - CASE WHEN method = "VNPay" THEN SUM(final_amount) * 0.011 + (1650.0 / COUNT(DISTINCT shop_id)) ELSE 0 END END as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                , DB::raw('payment_to_company_amount / count_shop as _22_PAYMENT_TO_TECHHUB_AMOUNT')
                , DB::raw('SUM(amount * handling_fee / 100.0) as _23_CHARGE_FEES_VND')
                //, DB::raw('SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) as _24_Amount_Must_Pay_to_Merchant')
                //, DB::raw('CASE WHEN payment_to_merchant_amount > 0 THEN payment_to_merchant_amount ELSE SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) END as _24_Amount_Must_Pay_to_Merchant')
                , DB::raw('payment_to_merchant_amount as _24_Amount_Must_Pay_to_Merchant')
                , DB::raw('payment_to_merchant_date as _25_PAYMENT_to_Merchant_Date')
                //, DB::raw('SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) as _26_Payment_to_Merchant_Amount')
                //, DB::raw('CASE WHEN payment_to_merchant_amount > 0 THEN payment_to_merchant_amount ELSE SUM(final_amount) + SUM(company_product_discount) - SUM(amount * handling_fee / 100.0) END as _26_Payment_to_Merchant_Amount')
                , DB::raw('payment_to_merchant_amount as _26_Payment_to_Merchant_Amount')
                , 'BankAccountName'
                , 'BankName'
                , 'BankAddress'
                , 'BankAccountNumber'
                , DB::raw('"" as _31_Payment_to_Carrier_Date')
                , DB::raw('delivery_fee as _32_Payment_to_Carrier_Amount')
                , DB::raw('SUM(amount * handling_fee) / SUM(amount) as handling_fee_rate')
                , 'tax'
                , 'phone'
                , 'shipping_partner_code'
                , 'shipping_type'
                , 'is_handlingfee_collected'
                , 'is_debt as merchant_is_debt'
                , 'debt_amount as merchant_debt_amount'
                , 'refund_date'
                , 'refund_amount'
                , 'refund_bank'
                , 'refund_note'
                )
            ->groupBy('od.order_id', 'od.shop_id', 'shop_name', 'email'
                , 'phone', 'created_at', 'order_number','status', 'is_handlingfee_collected'
                , 'shopping_point_exchange_rate', 'delivery_fee', 'method'
                , 'shipping_partner', 'shipping_partner_code'
                , 'address', 'reg_number', 'email', 'shop', 'shipping_type',  'BankAccountName'
                , 'BankName'
                , 'BankAccountNumber'
                , 'BankAddress'
                , 'payment_to_company_amount'
                , 'payment_to_company_date'
                , 'payment_to_merchant_amount'
                , 'payment_to_merchant_date'
                , 'is_debt'
                , 'debt_amount'
                , 'refund_date'
                , 'refund_amount'
                , 'refund_bank'
                , 'refund_note'
                , 'count_shop'
                )
            ->orderByDesc('od.order_id')
            ->get();

        return $order_result;
    }

    public function datatables_report_merchantp($status, $iscollected, $from = null, $to = null)
    {
        $order_result = $this->report_merchantp_data($status, $iscollected, $from, $to);
        return Datatables::of($order_result)
            ->editColumn('_9_Amount', function($data) {
                return number_format(round($data->_9_Amount));
            })
            ->editColumn('_10_Point', function($data) {
                return number_format(round($data->_10_Point));
            })
            ->editColumn('_12_Amount_of_Point', function($data) {
                return number_format(round($data->_12_Amount_of_Point));
            })
            ->editColumn('_13_Voucher_of_Merchant', function($data) {
                return number_format(round($data->_13_Voucher_of_Merchant));
            })
            ->editColumn('_14_Voucher_of_Techhub', function($data) {
                return number_format(round($data->_14_Voucher_of_Techhub));
            })
            ->editColumn('_15_Delivery_fee', function($data) {
                return number_format(round($data->_15_Delivery_fee));
            })
            ->editColumn('_16_Total_of_bill', function($data) {
                return number_format(round($data->_16_Total_of_bill));
            })
            ->editColumn('_17_Other_cards', function($data) {
                return number_format(round($data->_17_Other_cards));
            })
            ->editColumn('_19_Amount_Partner_Must_Pay', function($data) {
                return number_format(round($data->_19_Amount_Partner_Must_Pay));
            })
            ->editColumn('_22_PAYMENT_TO_TECHHUB_AMOUNT', function($data) {
                return number_format(round($data->_22_PAYMENT_TO_TECHHUB_AMOUNT));
            })
            ->editColumn('_23_CHARGE_FEES_VND', function($data) {
                return number_format(round($data->_23_CHARGE_FEES_VND));
            })
            ->editColumn('_24_Amount_Must_Pay_to_Merchant', function($data) {
                return number_format(round($data->_24_Amount_Must_Pay_to_Merchant));
            })
            ->editColumn('_26_Payment_to_Merchant_Amount', function($data) {
                return number_format(round($data->_26_Payment_to_Merchant_Amount));
            })
            ->editColumn('_32_Payment_to_Carrier_Amount', function($data) {
                return number_format(round($data->_32_Payment_to_Carrier_Amount));
            })
            ->editColumn('merchant_debt_amount', function($data) {
                return number_format(round($data->merchant_debt_amount));
            })
            ->editColumn('handling_fee_rate', function($data) {
                return $data->handling_fee_rate.'%';
            })
            ->editColumn('tax', function($data) {
                return $data->tax.'%';
            })
            ->addColumn('action', function($data) {
                $orders = '<a href="javascript:;" data-href="'. route('admin-order-edit',$data->order_id) .'" class="delivery" data-toggle="modal" data-target="#modal1"><i class="fas fa-dollar-sign"></i> Delivery Status</a>';
                return '<div class="godropdown" ><button class="go-dropdown-toggle "> '.($data->is_handlingfee_collected == 1 ? 'Đã thu' : 'Chưa thu').'<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-order-show', $data->order_id) . '" > <i class="fas fa-eye"></i> Details</a>'.$orders.'</div></div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function export_report_merchantp($status, $iscollected, $from = null, $to = null)
    {
        $order_result = $this->report_merchantp_data($status, $iscollected, $from, $to);
        $file_name = 'merchant-payment-report_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new MerchantPaymentReport($order_result), $file_name, null, ['order_id']);
    }

    //*** FILE2  - Weekly Merchant Performance Report

    public function merchantPerformance()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.merchant-performance', compact('now'));
    }

    public function datatablesMerchantPerformance($from, $to)
    {
        $datas = $this->dataMerchantPerformance($from, $to)->get();

        return Datatables::of($datas)
            ->editColumn('created_at', function($data) {
                $rs = Carbon::parse($data->created_at)->format('d-m-Y H:m:s');
                return $rs;
            })
            ->editColumn('completed_at', function($data) {
                $rs = Carbon::parse($data->completed_at)->format('d-m-Y H:m:s');
                return $rs;
            })
            ->editColumn('amount', function($data) {
                return number_format($data->amount);
            })
            ->editColumn('price_shopping_point_amount', function($data) {
                return number_format($data->price_shopping_point_amount);
            })
            ->editColumn('total_amount', function($data) {
                return number_format($data->total_amount);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function dataMerchantPerformance($from, $to)
    {
        $query = $this->queryMerchantPerformance($from, $to);
        return $query->select(
                DB::raw('o.created_at')
                , 'o.completed_at'
                , 'o.order_number'
                , 'shop.shop_name'
                , 'consumer.name as consumer_name'
                , 'cp.name as consumer_city'
                , 'c.name as category'
                , 'p.name as product'
                , 'v.qty as quantity'
                // , 'v.unit_price'
                , 'v.price as amount'
                // , 'v.item_price_shopping_point'
                , 'v.price_shopping_point_amount'
                , DB::raw('v.price + v.price_shopping_point_amount as total_amount')
                )
            ->orderByDesc('o.completed_at');
    }

    public function queryMerchantPerformance($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $query =   DB::table('vendor_orders as v')
            ->join('orders as o', 'o.id', '=', 'v.order_id')
            ->leftJoin('users as shop', 'shop.id', '=', 'v.user_id')
            ->leftJoin('users as consumer', 'consumer.id', '=', 'o.user_id')
            ->leftJoin('provinces as cp', 'cp.id', '=', 'consumer.CityID')
            ->leftJoin('products as p', 'p.id', '=', 'v.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->whereBetween('o.completed_at',[$from, $to])
            ->where('o.status', '=', 'completed')
            ;
        return $query;
    }

    public function exportMerchantPerformance($from, $to)
    {
        $datas = $this->dataMerchantPerformance($from, $to)->get();
        $file_name = 'merchant_performance_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new MerchantPerformance($datas), $file_name, null, []);
    }

    //*** FILE2.1 -  Summaries  - Top 10 Performance Sales by Shop
    public function queryMerchantPerformanceSummariesTop10ByShop($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $query =   DB::table('vendor_orders as v')
            ->join('orders as o', 'o.id', '=', 'v.order_id')
            ->join('users as shop', 'shop.id', '=', 'v.user_id')
            ->leftJoin('provinces as sp', 'sp.id', '=', 'shop.CityID')
            ->whereBetween('o.completed_at',[$from, $to])
            ->where('o.status', '=', 'completed')
            ;
        return $query;
    }

    public function dataMerchantPerformanceSummariesTop10ByShop($from, $to, $top = 10, $isBySales = false)
    {
        $query = $this->queryMerchantPerformanceSummariesTop10ByShop($from, $to);
        $query = $query->select(
                DB::raw('shop.shop_name')
                , 'shop.email'
                , 'sp.name as city'
                , DB::raw('COUNT(DISTINCT o.id) as number_of_sales')
                , DB::raw('SUM(v.qty) as total_qty')
                , DB::raw('SUM(v.price + v.price_shopping_point_amount) as total_amount')
            )
            ->groupBy('shop.shop_name', 'shop.email', 'sp.name');
        if($isBySales){
            $query = $query->orderByDesc('number_of_sales')->limit($top);
        }
        else{
            $query = $query->orderByDesc('total_amount')->limit($top);
        }
        return $query;
    }

    public function datatablesMerchantPerformanceSummariesTop10ByShop($from, $to)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByShop($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('number_of_sales', function($data) {
                return number_format($data->number_of_sales);
            })
            ->editColumn('total_qty', function($data) {
                return number_format($data->total_qty);
            })
            ->editColumn('total_amount', function($data) {
                return number_format($data->total_amount);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function merchantPerformanceSummariesTop10ByShop()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.merchant-performance-summaries-top10byshop', compact('now'));
    }

    public function exportMerchantPerformanceSummariesTop10ByShop($from, $to, $top = 10, $isBySales = false)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByShop($from, $to, $top, $isBySales)->get();
        $file_name = 'merchant_performance_summaries_top10byshop_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new MerchantPerformanceSummariesTop10ByShop($datas), $file_name, null, []);
    }

    //*** FILE2.2 -  Summaries  - Top 10 Performance Sales by Product
    public function queryMerchantPerformanceSummariesTop10ByProduct($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $query =   DB::table('vendor_orders as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->join('orders as o', 'o.id', '=', 'v.order_id')
            ->join('users as shop', 'shop.id', '=', 'v.user_id')
            ->leftJoin('provinces as sp', 'sp.id', '=', 'shop.CityID')
            ->whereBetween('o.completed_at',[$from, $to])
            ->where('o.status', '=', 'completed')
            ;
        return $query;
    }

    public function dataMerchantPerformanceSummariesTop10ByProduct($from, $to)
    {
        $query = $this->queryMerchantPerformanceSummariesTop10ByProduct($from, $to);
        return $query->select(
                'p.name as product_name'
                , 'shop.shop_name'
                , 'shop.email'
                , DB::raw('COUNT(DISTINCT o.id) as number_of_sales')
                , DB::raw('SUM(v.qty) as total_qty')
                , DB::raw('SUM(v.price + v.price_shopping_point_amount) as total_amount')
                )
            ->groupBy('p.name', 'shop.shop_name', 'shop.email')
            ->orderByDesc('total_amount')
            ->limit(10)
        ;
    }

    public function datatablesMerchantPerformanceSummariesTop10ByProduct($from, $to)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByProduct($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('number_of_sales', function($data) {
                return number_format($data->number_of_sales);
            })
            ->editColumn('total_qty', function($data) {
                return number_format($data->total_qty);
            })
            ->editColumn('total_amount', function($data) {
                return number_format($data->total_amount);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function merchantPerformanceSummariesTop10ByProduct()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.merchant-performance-summaries-top10byproduct', compact('now'));
    }

    public function exportMerchantPerformanceSummariesTop10ByProduct($from, $to)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByProduct($from, $to)->get();
        $file_name = 'merchant_performance_summaries_top10byproduct_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new MerchantPerformanceSummariesTop10ByProduct($datas), $file_name, null, []);
    }

    //*** FILE2.3 -  Summaries  - Top 10 Performance Sales by Product Category
    public function queryMerchantPerformanceSummariesTop10ByProductCategory($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $query =   DB::table('vendor_orders as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->join('categories as cat', 'cat.id', '=', 'p.category_id')
            ->join('orders as o', 'o.id', '=', 'v.order_id')
            ->join('users as shop', 'shop.id', '=', 'v.user_id')
            ->leftJoin('provinces as sp', 'sp.id', '=', 'shop.CityID')
            ->whereBetween('o.completed_at',[$from, $to])
            ->where('o.status', '=', 'completed')
            ;
        return $query;
    }

    public function dataMerchantPerformanceSummariesTop10ByProductCategory($from, $to)
    {
        $query = $this->queryMerchantPerformanceSummariesTop10ByProductCategory($from, $to);
        return $query->select(
                'cat.name as category_name'
                , DB::raw('COUNT(DISTINCT o.id) as number_of_sales')
                , DB::raw('SUM(v.qty) as total_qty')
                , DB::raw('SUM(v.price + v.price_shopping_point_amount) as total_amount')
                )
            ->groupBy('cat.name')
            ->orderByDesc('total_amount')
            ->limit(10)
        ;
    }

    public function datatablesMerchantPerformanceSummariesTop10ByProductCategory($from, $to)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByProductCategory($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('number_of_sales', function($data) {
                return number_format($data->number_of_sales);
            })
            ->editColumn('total_qty', function($data) {
                return number_format($data->total_qty);
            })
            ->editColumn('total_amount', function($data) {
                return number_format($data->total_amount);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function merchantPerformanceSummariesTop10ByProductCategory()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.merchant-performance-summaries-top10byproductcategory', compact('now'));
    }

    public function exportMerchantPerformanceSummariesTop10ByProductCategory($from, $to)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByProductCategory($from, $to)->get();
        $file_name = 'merchant_performance_summaries_top10byproductcategory_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new MerchantPerformanceSummariesTop10ByProductCategory($datas), $file_name, null, []);
    }

    //*** FILE2.4 -  Summaries  - Top 10 Performance Sales by Province
    public function queryMerchantPerformanceSummariesTop10ByProvince($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $query =   DB::table('vendor_orders as v')
            ->join('orders as o', 'o.id', '=', 'v.order_id')
            ->join('users as customer', 'customer.id', '=', 'o.user_id')
            ->join('provinces as cp', 'cp.id', '=', 'customer.CityID')
            ->whereBetween('o.completed_at',[$from, $to])
            ->where('o.status', '=', 'completed')
            ;
        return $query;
    }

    public function dataMerchantPerformanceSummariesTop10ByProvince($from, $to)
    {
        $query = $this->queryMerchantPerformanceSummariesTop10ByProvince($from, $to);
        return $query->select(
                'cp.name as city'
                , DB::raw('COUNT(DISTINCT o.id) as number_of_sales')
                , DB::raw('SUM(v.qty) as total_qty')
                , DB::raw('SUM(v.price + v.price_shopping_point_amount) as total_amount')
                )
            ->groupBy('cp.name')
            ->orderByDesc('total_amount')
            ->limit(10)
        ;
    }

    public function datatablesMerchantPerformanceSummariesTop10ByProvince($from, $to)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByProvince($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('number_of_sales', function($data) {
                return number_format($data->number_of_sales);
            })
            ->editColumn('total_qty', function($data) {
                return number_format($data->total_qty);
            })
            ->editColumn('total_amount', function($data) {
                return number_format($data->total_amount);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function merchantPerformanceSummariesTop10ByProvince()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.merchant-performance-summaries-top10byprovince', compact('now'));
    }

    public function exportMerchantPerformanceSummariesTop10ByProvince($from, $to)
    {
        $datas = $this->dataMerchantPerformanceSummariesTop10ByProvince($from, $to)->get();
        $file_name = 'merchant_performance_summaries_top10byprovince_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new MerchantPerformanceSummariesTop10ByProvince($datas), $file_name, null, []);
    }
    //END FILE2.4 -  Summaries  - Top 10 Performance Sales by Province

    //*** Refund report START
    public function queryOrderRefundDetail($from, $to)
    {
        //$to = HTDUtils::addDays($to, 1);
        $query =   DB::table('orders as o')
            ->join('vendor_orders as v', 'o.id', '=', 'v.order_id')
            ->join('users as shop', 'shop.id', '=', 'v.user_id')
            ->join('users as customer', 'customer.id', '=', 'o.user_id')
            ->whereBetween('o.refund_date',[$from, $to])
            ;
        return $query;
    }

    public function dataOrderRefundDetail($from, $to)
    {
        $query = $this->queryOrderRefundDetail($from, $to);
        return $query->select(
                'o.created_at'
                , 'o.order_number'
                , 'o.pay_amount2'
                , 'o.shipping_cost'
                , 'shop.shop_name'
                , 'shop.email'
                , DB::raw('CONCAT(customer.name, " (", customer.email, ")") as refund_to')
                , 'o.refund_by_name'
                , 'o.refund_date'
                , 'o.refund_bank'
                , 'o.refund_amount'
                , 'o.refund_note'
                )
            ->orderByDesc('refund_date')
        ;
    }

    public function datatablesOrderRefundDetail($from, $to)
    {
        $datas = $this->dataOrderRefundDetail($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('refund_amount', function($data) {
                return number_format($data->refund_amount);
            })
            ->editColumn('pay_amount2', function($data) {
                return number_format($data->pay_amount2);
            })
            ->editColumn('shipping_cost', function($data) {
                return number_format($data->shipping_cost);
            })
            ->editColumn('created_at', function($data) {
                $rs = Carbon::parse($data->created_at)->format('d-m-Y H:m');
                return $rs;
            })
            ->editColumn('refund_date', function($data) {
                $rs = Carbon::parse($data->refund_date)->format('d-m-Y H:m');
                return $rs;
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function orderRefundDetail()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.order.reports.refund-detail', compact('now'));
    }

    public function exportOrderRefundDetail($from, $to)
    {
        $datas = $this->dataOrderRefundDetail($from, $to)->get();
        $file_name = 'order_refund_detail_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new RefundDetail($datas), $file_name, null, []);
    }

    //KOL Consumer Bonus

    public function datatablesKOLConsumerBonus($from)
    {

        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolConsumerBonusData($from);
        return Datatables::of($datas)
            ->editColumn('total_sales', function($data) {
                return number_format($data->total_sales);
            })
            ->editColumn('bonus', function($data) {
                return number_format($data->bonus);
            })
            ->addColumn('consumer_info', function($data) {
                $info = '<span>'. $data->consumer_id .'</span><br>'.'<span>'. $data->consumer_name .'</span><br>'.'<span>'. $data->consumer_email .'</span><br>';
                return $info;
            })
            ->addColumn('l1_info', function($data) {
                $info = '<span>'. $data->l1_id .'</span><br>'
                    .'<span>'. $data->l1_name .'</span><br>'
                    .'<span>'. $data->l1_email .'</span><br>'
                    .'<span>'. $data->l1_ranking .'</span>'
                    ;
                return $info;
            })
            ->addColumn('l1_bank_info', function($data) {
                $info = '<span>'. $data->l1_bankname .'</span><br>'.'<span>'. $data->l1_bankaccount .'</span><br>'.'<span>'. $data->l1_bankbumber .'</span><br>'.'<span>'. $data->l1_bankaddress .'</span>';
                return $info;
            })
            ->rawColumns(['consumer_info', 'l1_info', 'l1_bank_info'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function kolConsumerBonus()
    {
        return view('admin.order.reports.kol-consumer-bonus');
    }

    public function exportKOLConsumerBonus($from)
    {
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolConsumerBonusData($from);
        $file_name = 'order_kol_consumer_bonus_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new KOLConsumerBonus($datas), $file_name, null, []);
    }

    public function processPayKOLConsumerBonus($from)
    {
        $msg = app('App\Http\Controllers\Admin\ApiUserController')->kolcCalculate($from);
        return $msg;
    }
    //KOL Consumer Bonus ENDS

    //KOL Consumer Bonus Paid
    public function dataKOLConsumerBonusPaid($from,  $to)
    {
        try {
            $revenue = DB::table('orders as o1')
                ->join('users as consumer', 'o1.user_id', '=', 'consumer.id')
                ->join('users as referral', 'consumer.referral_user_id', '=', 'referral.id')
                ->join('user_point_logs as lo', function ($join) {
                    $join->on('o1.id', '=', 'lo.order_ref_id')
                        ->on('referral.id', '=', 'lo.user_id')
                        ->on('consumer.id', '=', 'lo.consumer_id')
                        ;
                })
                ->join('package_configs as pc', 'pc.user_rank_id', '='
                    , DB::raw('case when referral.ranking > referral.ranking_purchased then referral.ranking else referral.ranking_purchased end'))
                ->whereBetween('lo.created_at',[$from, $to])
                ->where('o1.kolc_calculated','=','1')
                ->select('o1.id as order_id'
                    , 'o1.order_number as order_number'
                    ,'consumer.id as consumer_id'
                    , 'consumer.name as consumer_name'
                    , 'consumer.email as consumer_email'
                    , 'consumer.referral_user_id as l1_id'
                    , 'referral.name as l1_name'
                    , 'referral.email as l1_email'
                    , 'pc.name as l1_ranking'
                    , 'referral.BankName as l1_bankname'
                    , 'referral.BankAccountName as l1_bankaccount'
                    , 'referral.BankAccountNumber as l1_bankbumber'
                    , 'referral.BankAddress as l1_bankaddress'
                    , 'lo.amount as total_sales'
                    , 'lo.amount_bonus as bonus'
                );
            $total_revenue = $revenue->get();
            return $total_revenue;
        }
        catch (\Exception $e){
            return response()->json($e->getMessage());
            // die($e->getMessage());
        }
    }

    public function datatablesKOLConsumerBonusPaid($from, $to)
    {
        $datas = $this->dataKOLConsumerBonusPaid($from, $to);
        return Datatables::of($datas)
            ->editColumn('total_sales', function($data) {
                return number_format($data->total_sales);
            })
            ->editColumn('bonus', function($data) {
                return number_format($data->bonus);
            })
            ->addColumn('consumer_info', function($data) {
                $info = '<span>'. $data->consumer_id .'</span><br>'.'<span>'. $data->consumer_name .'</span><br>'.'<span>'. $data->consumer_email .'</span><br>';
                return $info;
            })
            ->addColumn('l1_info', function($data) {
                $info = '<span>'. $data->l1_id .'</span><br>'
                    .'<span>'. $data->l1_name .'</span><br>'
                    .'<span>'. $data->l1_email .'</span><br>'
                    .'<span>'. $data->l1_ranking .'</span>'
                    ;
                return $info;
            })
            ->addColumn('l1_bank_info', function($data) {
                $info = '<span>'. $data->l1_bankname .'</span><br>'.'<span>'. $data->l1_bankaccount .'</span><br>'.'<span>'. $data->l1_bankbumber .'</span><br>'.'<span>'. $data->l1_bankaddress .'</span>';
                return $info;
            })
            ->rawColumns(['consumer_info', 'l1_info', 'l1_bank_info'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function kolConsumerBonusPaid()
    {
        return view('admin.order.reports.kol-consumer-bonus-paid');
    }

    public function exportKOLConsumerBonusPaid($from, $to)
    {
        $datas = $this->dataKOLConsumerBonusPaid($from, $to);
        $file_name = 'order_kol_consumer_bonus_paid_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new KOLConsumerBonusPaid($datas), $file_name, null, []);
    }


    public function kolConsumerBonus2w()
    {
        $config = KolConfig::where('kol_date', '=',  date('m-Y'))->first();

        if ($config == null  || $config->count()==0) {
            $errors = ['errors' => 'Please set config for '.date('m-Y')];
            return view('admin.order.reports.kol-consumer-bonus-2w',compact('errors','config'));

        }
        return view('admin.order.reports.kol-consumer-bonus-2w',compact('config'));
    }

    public function datatablesKOLConsumerBonus2weeks($from, $to)
    {
        $config = KolConfig::where('kol_date', '=', $from)->first();

        if ($config == null) {
            return response()->json(['errors' => 'Please set config for '.date('m-Y')], 400);
        }
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolConsumerBonusData2Weeks($from);

        if ($datas->count()>0) {
            foreach ($datas as  $data) {
                $l1 = $l2 = 0;

                if ($data->total_order < $config->number_orders_l1) {
                    $l1 +=1;
                }
                if ($data->total_order < $config->number_orders_l2) {
                    $l2 +=1;
                }

                if ($data->total_user < $config->number_users_l1) {
                    $l1 +=1;
                }
                if ($data->total_user < $config->number_users_l2) {
                    $l2 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l1) {
                    $l1 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l2) {
                    $l2 +=1;
                }

                if ($data->total_affiliate_member < $config->number_affiliate_member_l1) {
                    $l1 +=1;
                }
                if ($data->total_affiliate_member < $config->number_affiliate_member_l2) {
                    $l2 +=1;
                }

                $data->l1 = $l1;
                $data->l2 = $l2;

                if ($data->special_kol == 1) {
                    $data->bonus = ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $data->bonus = ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales <  $config->revenue_l1) {
                    $data->bonus = ($config->con_bonus_l2 *  $data->total_amount) / 100;
                } else {
                     if ($l1 == 0) {
                        $data->bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                    } elseif ($l2 == 0) {
                        $data->bonus =  ($config->con_bonus_l2 *  $data->total_amount) / 100;
                    } else {
                        $data->bonus = 0;
                    }
                }
               
                $data->total_bonus = $data->total_affiliate_bonus +  $data->bonus;
              
                if ($data->total_bonus >= static::VAT) {
                    $data->vat = 10;
                    $data->total_bonus = $data->total_bonus - (($data->total_bonus * 10) / 100);
                } else {
                    $data->vat = 0;
                }
            }
        }

       return  Datatables::of($datas)
            // ->editColumn('total_sales', function($data) {
            //     return number_format($data->total_sales);
            // })@2
            // ->editColumn('bonus', function($data) {
            //     return number_format($data->bonus);
            // })
            // ->addColumn('consumer_info', function($data) {
            //     $info = '<span>'. $data->consumer_id .'</span><br>'.'<span>'. $data->consumer_name .'</span><br>'.'<span>'. $data->consumer_email .'</span><br>';
            //     return $info;
            // })
            ->addColumn('kol_info', function($data) use ($config) {
                $info = '<span>'. $data->referral_id .'</span><br>'
                    .'<span>'. $data->name .'</span><br>'
                    .'<span>'. $data->kol_email .'</span><br>'
                    .'<span>'. $data->kol_bankname .'</span><br>'
                    .'<span>'. $data->kol_bankaccount .'</span><br>'
                    .'<span>'. $data->kol_bankbumber .'</span><br>'
                    .'<span>'. $data->kol_bankaddress .'</span><br>' ;
                    if ($data->special_kol == 1)  {
                        $info .= '<span style="color:red;font-weight:bold">Special KOL</span><br>' ;
                    } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                        $info .= '<span style="color:#6318ba;font-weight:bold">Shop Revenue L1</span><br>' ;
                    } elseif ($data->revenue_total_sales >  $config->revenue_l2) {
                        $info .= '<span style="color:#6318ba;font-weight:bold">Shop Revenue L2</span><br>' ;
                    }
                return $info;
            })
             ->addColumn('kol_info_bank_info', function($data) {
                $info = '<span>'. $data->kol_bankname .'</span><br>'.'<span>'. $data->kol_bankaccount .'</span><br>'.'<span>'. $data->kol_bankbumber .'</span><br>'.'<span>'. $data->kol_bankaddress .'</span>';
                return $info;
            })
            ->addColumn('total_order', function($data) use ($config) {    ///////////////  target 1
                $info = '<span>'. $data->total_order .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_user', function($data) use ($config) {             ///////////////  target 2
                $info = '<span>'. $data->total_user .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_new_shop', function($data) use ($config) {           ///////////////  target 3
                $info = '<span>'. $data->total_new_shop .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_affiliate_member', function($data) use ($config) {       ///////////////  target 4
                $info = '<span>'. $data->total_affiliate_member .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_amount', function($data) {
                $total =  number_format($data->total_amount, 0, ',', ',');
                $info = '<span>'. $total .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_order_user_new', function($data) {
                $info = '<span>'. $data->total_order_user_new .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_amount_user_new', function($data) {
                $total_amount_user_new =  number_format($data->total_amount_user_new, 0, ',', ',');
                $info = '<span>'. $total_amount_user_new .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_order_user_exits', function($data) {
                $info = '<span>'. $data->total_order_user_exits .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_amount_user_exits', function($data) {
                $total_amount_user_exits =  number_format($data->total_amount_user_exits, 0, ',', ',');
                $info = '<span>'. $total_amount_user_exits .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('kol_consumer_bonus_rate', function($data) use ($config) {
                if ($data->special_kol == 1) {
                    $info = '<span style="font-weight:bold">'. $config->con_bonus_l1 .'%</span><br>'.'<span>';
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $info = '<span style="font-weight:bold">'. $config->con_bonus_l1 .'%</span><br>'.'<span>';
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales < $config->revenue_l1) {
                    $info = '<span style="font-weight:bold">'. $config->con_bonus_l2 .'%</span><br>'.'<span>';
                } else {
                    if ($data->l1 === 0) {
                        $info = '<span style="font-weight:bold">'. $config->con_bonus_l1 .'%</span><br>'.'<span>';
                    } elseif ($data->l2 === 0) {
                        $info = '<span style="font-weight:bold">'. $config->con_bonus_l2 .'%</span><br>'.'<span>';
                    } else {
                        $info = '<span>0%</span><br>'.'<span>';
                    }
                }
                return $info;
            })
            ->addColumn('bonus', function($data) use ($config) {

                $data->bonus =  number_format($data->bonus, 0, ',', ',') . " đ";
                $info = '<span>'. $data->bonus .' </span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_affiliate_bonus', function($data) {
                $bonus =  number_format($data->total_affiliate_bonus, 0, ',', ',');
                $info = '<span>'.  $bonus  .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('revenue_total_sales', function($data) use ($config) {
                $data->revenue_total_sales =  number_format($data->revenue_total_sales, 0, ',', ','). " đ";
                $info = '<span>'.  $data->revenue_total_sales  .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('total_bonus', function($data) use ($config) {
                $data->total_bonus =  number_format($data->total_bonus, 0, ',', ','). " đ";
                $info = '<span>'.  $data->total_bonus  .'</span><br>'.'<span>';
                return $info;
            })
            ->addColumn('vat', function($data) {
                $info = '<span>'. $data->vat .'%</span><br>'.'<span>';
                return $info;
            })
            ->rawColumns([
                'kol_info',
                'total_user',
                'total_order',
                'total_new_shop',
                'total_amount',
                'total_affiliate_member',
                'total_affiliate_bonus',
                'total_order_user_new',
                'total_amount_user_new',
                'total_order_user_exits',
                'total_amount_user_exits',
                'revenue_total_sales',
                'kol_consumer_bonus_rate',
                'bonus',
                'vat',
                'total_bonus'
            ])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function exportKOLConsumerBonus2w($from)
    {
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolConsumerBonusData2Weeks($from);
        $config = KolConfig::where('kol_date', '=', $from)->first();

        if ($datas->count()>0) {

            foreach($datas as $data){
                $total_bonus = $l1 = $l2 = 0;

                if ($data->special_kol == 1) {
                    $data->special_kol = 'yes';
                } else {
                    $data->special_kol = '';
                }

                if ($data->total_order < $config->number_orders_l1) {
                    $l1 +=1;
                }
                if ($data->total_order < $config->number_orders_l2) {
                    $l2 +=1;
                }
                if ($data->total_user < $config->number_users_l1) {
                    $l1 +=1;
                }
                if ($data->total_user < $config->number_users_l2) {
                    $l2 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l1) {
                    $l1 +=1;
                }
                if ($data->total_new_shop < $config->total_shops_l2) {
                    $l2 +=1;
                }
                if ($data->total_affiliate_member < $config->number_affiliate_member_l1) {
                    $l1 +=1;
                }
                if ($data->total_affiliate_member < $config->number_affiliate_member_l2) {
                    $l2 +=1;
                }

                if ($data->total_new_shop == 0) {
                    $data->total_new_shop = '0';
                }

                if ($data->total_affiliate_member == 0) {
                    $data->total_affiliate_member = '0';
                }

                if ($data->total_order_user_new == 0) {
                    $data->total_order_user_new = '0';
                }

                if ($data->total_amount_user_new == 0) {
                    $data->total_amount_user_new = '0';
                }

                if ($data->total_order_user_exits == 0) {
                    $data->total_order_user_exits = '0';
                }

                if ($data->total_amount_user_exits == 0) {
                    $data->total_amount_user_exits = '0';
                }

                if ($data->total_amount == 0) {
                    $data->total_amount = '0';
                }
                if ($data->total_affiliate_bonus == 0) {
                    $data->total_affiliate_bonus = '0';
                } else {
                    $total_bonus += $data->total_affiliate_bonus;
                }


                if ($data->special_kol == 'yes') {
                    $data->con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $data->con_bonus = $config->con_bonus_l1."%";
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales < $config->revenue_l1) {
                    $data->con_bonus = $config->con_bonus_l2."%";  
                } else {
                    if ($l1 === 0) {
                        $data->con_bonus = $config->con_bonus_l1."%";
                    } elseif ($l2 === 0) {
                        $data->con_bonus = $config->con_bonus_l2."%";
                    } else {
                        $data->con_bonus = '0%';
                    }
                }

                $bonus = 0;
                if ($data->special_kol == 'yes') {
                    $bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l1) {
                    $bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                } elseif ($data->revenue_total_sales >  $config->revenue_l2 && $data->revenue_total_sales < $config->revenue_l1) {
                    $bonus =  ($config->con_bonus_l2 *  $data->total_amount) / 100;
                } else {
                     if ($l1 === 0) {
                        $bonus =  ($config->con_bonus_l1 *  $data->total_amount) / 100;
                    } elseif ($l2 === 0) {
                        $bonus =  ($config->con_bonus_l2 *  $data->total_amount) / 100;
                    } else {
                        $bonus = 0;
                    }
                }
                $total_bonus += $bonus;
                $data->bonus =  number_format($bonus, 0, ',', ',') . " đ";

                if ($total_bonus >= static::VAT) {
                    $data->vat = '10%';
                    $data->total_bonus = $total_bonus - (($total_bonus * 10) / 100);
                } else {
                    $data->vat = '0%';
                }
                $data->total_bonus =  number_format($total_bonus, 0, ',', ',') . " đ";

                $data->revenue_total_sales =  number_format($data->revenue_total_sales, 0, ',', ',') . " đ";

            }

        }
        $file_name = 'order_kol_consumer_bonus_for_'.$from.'___'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new KOLConsumerBonus2W($datas, $config), $file_name, null, []);
    }
}
