<?php

namespace App\Http\Controllers\Vendor;

use Auth;
use App\Models\Cart;
use App\Models\Order;
use App\Models\VendorOrder;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\Orders\Reports\VendorOrderExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    const LIMIT  = '200';

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        $status = '';
        $user =  Auth::user();

        if($request->from_date == null) {
            $from = Carbon::now()->format('Y-m-d');
            $fr1 = Carbon::now()->format('d/m/Y');
        } else {
            $from= Carbon::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $fr1 =$request->from_date;
        }

        if($request->to_date == null) {
            $to = Carbon::now()->format('Y-m-d');
            $to1 = Carbon::now()->format('d/m/Y');
        } else {
            $to = Carbon::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $to1 = $request->to_date;
        }

        // DB::enableQueryLog(); // Enable query log
        // dd(DB::getQueryLog());

        // $orders = VendorOrder::where('user_id', '=', $user->id)
        //     ->get()->groupBy('order_number');
        // $orders =  $orders ->sortByDesc(function ($item, $key) {
        //     return $item[0]->order->created_at;
        // });
        // foreach( $orders1 as $k => $v) {
        //     // order = Order::where('order_number', '=',$v->order_number)->first();
        //     echo $k;
        //     dd($v[0]->order->ordervendorinfosv2());
        // }
        // $orders =  DB::select(
        //     DB::raw("SELECT v.*, o.created_at as created_at_order
        //         , SUM(v.qty) as total_qty
        //         , SUM(v.price) as total_price
        //         , SUM(v.price_shopping_point_amount) as total_price_shopping_point_amount
        //         , SUM(v.shop_coupon_amount) as total_shop_coupon_amount
        //         , SUM(v.shopping_point_amount) as total_shopping_point_amount
        //        FROM vendor_orders  as v
        //        inner join orders as o on o.id = v.order_id
        //        where v.user_id = '".$user->id."'
        //        group by v.order_number
        //        order by  o.created_at desc
        //        limit 1
        // "));
        // $orders = collect($orders);
        // $orders->all();
        // $orders = VendorOrder::leftJoin('order_consumer_shipping_costs', function($join) {
        //     $join->on('vendor_orders.order_id', '=', 'order_consumer_shipping_costs.order_id');
        //   })
        //   ->where('vendor_orders.user_id','=',$user->id)->orderBy('vendor_orders.id','desc')->get()->groupBy('vendor_orders.order_number');
        //dd($orders);
        // $orders = DB::table('vendor_orders')
        //     ->join('order_consumer_shipping_costs', 'vendor_orders.order_id', '=', 'order_consumer_shipping_costs.order_id')
        //     ->select('vendor_orders.*', 'order_consumer_shipping_costs.shipping_partner_code')
        //     ->where('vendor_orders.user_id','=',$user->id)->orderBy('vendor_orders.id','desc')->get()->groupBy('vendor_orders.order_number')
        //     ;
        $orders = $this->getOrderData($user, $status, $from, $to);

        foreach($orders as $k => $v) {
            $orders[$k]->order = Order::find($v->order_id);
            $orders[$k]->vendor_info  =$orders[$k]->order->ordervendorinfosv2();
        }
        return view('vendor.order.index',compact('user','orders', 'status','fr1','to1'));
    }

    public function show($slug) {
        $user = Auth::user();
        $order = Order::where('order_number','=',$slug)->first();
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        return view('vendor.order.details',compact('user','order','cart'));
    }

    public function license(Request $request, $slug) {
        $order = Order::where('order_number','=',$slug)->first();
        $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart->items[$request->license_key]['license'] = $request->license;
        $order->cart = utf8_encode(bzcompress(serialize($cart), 9));
        $order->update();
        $msg = 'Successfully Changed The License Key.';
        return response()->json($msg);
    }

    public function invoice($slug) {
        $user = Auth::user();
        $order = Order::where('order_number','=',$slug)->first();
        // $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        return view('vendor.order.invoice',compact('user','order','cart'));
    }

    public function printpage($slug) {
        $user = Auth::user();
        $order = Order::where('order_number','=',$slug)->first();
        // $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        return view('vendor.order.print',compact('user','order','cart'));
    }

    public function status($slug=null,$status=null) {
        $mainorder = Order::where('order_number','=',$slug)->first();
        $user = Auth::user();
        $consumer = $mainorder->user()->first();
        $vorder_query = VendorOrder::where('order_number','=',$slug)->where('user_id','=',$user->id);
        if($status == "declined"){
            $vorders = $vorder_query->get();
            foreach ($vorders as $vo){
                if($vo->status != 'declined' && $vo->status != 'completed'){
                    if($vo->is_shopping_point_used == 1){
                        $point_log_declined = new UserPointLog;
                        $point_log_declined->user_id = $consumer->id;
                        $point_log_declined->log_type = 'Order - Shop Declined';
                        $point_log_declined->order_ref_id = $mainorder->id;
                        $point_log_declined->reward_point_balance = isset($consumer->reward_point) ? $consumer->reward_point : 0;
                        $point_log_declined->shopping_point_balance = isset($consumer->shopping_point) ? $consumer->shopping_point : 0;
                        $point_log_declined->exchange_rate = 0;
                        $point_log_declined->note = 'Return from vendor_order ['.$vo->id.']';
                        $point_log_declined->descriptions = 'Bạn được hoàn trả shopping point từ sản phẩm ['.$vo->product_name.'] của đơn hàng số ['.$vo->order_number.']';
                        $point_log_declined->reward_point = 0;
                        $point_log_declined->shopping_point = $vo->shopping_point_used;
                        $point_log_declined->amount = $vo->price;
                        $point_log_declined->sp_vnd_exchange_rate = $vo->exchange_rate;
                        $consumer->shopping_point = $consumer->shopping_point + $vo->shopping_point_used;
                        $consumer->save();
                        $point_log_declined->save();
                    } // end check vendor order user shopping point
                } // end check vendor order declined
            } //end foreach vendor order
            if($mainorder->shipping_type != 'negotiate'){
                $shipping = $mainorder->orderconsumershippingcosts()->where('shop_id',$user->id)->first();
                if($shipping != null){
                    $status_vtel = config('app.viettel_post.order_status');
                    $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->updateorderstatus($shipping->shipping_partner_code, $status_vtel['cancel_order'], 'Cancel due to shop declined order');
                }
            }
        }// //end check status input = declined

        $order_update = VendorOrder::where('order_number','=',$slug)
            ->where('user_id','=',$user->id)
            ->where('status', '<>', 'declined')
            ->where('status', '<>', 'completed')
            ->update(['status' => $status]);
        $rs[0] = 'Cập nhật tình trạng thành công!';
        $rs[1] = $status;
        return response()->json($rs);
        // return redirect()->route('vendor-order-index')->with('success','Order Status Updated Successfully');//end else order status
    }

    public function ordersStatus(Request $request, $status = null) {


        if($request->from_date == null) {
            $from = '2017-01-01';//Carbon::now()->format('Y-m-d');
            $fr1 = null;
        } else {
            $from= Carbon::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $fr1 =$request->from_date;
        }

        if($request->to_date == null) {
            $to = Carbon::now()->format('Y-m-d');
            $to1 = null;
        } else {
            $to = Carbon::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $to1 = $request->to_date;
        }

        $user = Auth::user();
        $orders  =  [];

        if ($request->from_date == null  && $request->to_date == null) {
            $orders = $this->getOrderDataNotDuration($user, $status);
        } else {
            $orders = $this->getOrderData($user, $status, $from, $to);
        }

        foreach( $orders as $k => $v) {
            $orders[$k]->order = Order::find($v->order_id);
            $orders[$k]->vendor_info  =$orders[$k]->order->ordervendorinfosv2();
        }

        return view('vendor.order.index',compact('user','orders','status','fr1','to1'));
    }

    public function getOrderData($user, $status = '', $from = null, $to = null) {

        $condition[] =  ['v.user_id', '=',  $user->id];
        if ($status != '' && $status != 'all' ) {
            $condition[] = ['v.status', '=', $status];
        }

        $orders = DB::table('vendor_orders as v')->select(
            'v.*'
            , 'o.created_at as created_at_order'
            , DB::raw(' SUM(v.qty) as total_qty')
            , DB::raw(' SUM(v.price) as total_price')
            , DB::raw(' SUM(v.price_shopping_point_amount) as total_price_shopping_point_amount')
            , DB::raw(' SUM(v.shop_coupon_amount) as total_shop_coupon_amount')
            , DB::raw(' SUM(v.shopping_point_amount) as total_shopping_point_amount')
        )
        ->join('orders as o', 'o.id', '=', 'v.order_id')
        // ->where('v.user_id', '=', $user->id)
        // ->where('v.status', '=', $status);
        ->where($condition)
        ->whereBetween('o.created_at', [$from, $to])
        ->groupBy('v.order_number')
        ->orderBy('o.created_at', 'DESC')
        ->limit(static::LIMIT)
        ->get();

        return $orders;
    }

    public function getOrderDataNotDuration($user, $status = '') {

        $condition[] =  ['v.user_id', '=',  $user->id];
        if ($status != '' && $status != 'all' ) {
            $condition[] = ['v.status', '=', $status];
        }

        $orders = DB::table('vendor_orders as v')->select(
            'v.*'
            , 'o.created_at as created_at_order'
            , DB::raw(' SUM(v.qty) as total_qty')
            , DB::raw(' SUM(v.price) as total_price')
            , DB::raw(' SUM(v.price_shopping_point_amount) as total_price_shopping_point_amount')
            , DB::raw(' SUM(v.shop_coupon_amount) as total_shop_coupon_amount')
            , DB::raw(' SUM(v.shopping_point_amount) as total_shopping_point_amount')
        )
        ->join('orders as o', 'o.id', '=', 'v.order_id')
        ->where($condition)
        ->groupBy('v.order_number')
        ->orderBy('o.created_at', 'DESC')
        ->limit(static::LIMIT)
        ->get();

        return $orders;
    }

    public function exportOrder(Request $request, $status = null, $from = null, $to = null) {

        $user = Auth::user();
        // dd($status, $from , $to);
        if ($from == null || $from == '' || $from == 'null') {
            $from = '2017-01-01';//Carbon::now()->format('Y-m-d');
        } else {
            $from= Carbon::createFromFormat('d-m-Y', $from)->format('Y-m-d');
        }

        if ($to == null || $to == '' || $to == 'null') {
            $to = Carbon::now()->format('Y-m-d');
        } else {
            $to = Carbon::createFromFormat('d-m-Y', $to)->format('Y-m-d');
        }

        $orders =  $this->getOrderData($user, $status, $from, $to);

        $datas = [];
        if ($orders->count()>0) {

            foreach ($orders  as $k => $order) {
                $orderTmp = Order::find($order->order_id);
                $vendor_info = $orderTmp->ordervendorinfosv2();

                $shipping_cost = 0;
                //$qty = $order->total_qty;
                $price = $order->total_price;
                $price_sp = $order->total_price_shopping_point_amount;
                $shop_discount = $order->total_shop_coupon_amount;
                $shopping_point = $order->total_shopping_point_amount;
                $tax_amount = ($price + $price_sp) * $orderTmp->tax / 100.0;

                $ma_van_don =  $orderTmp->orderconsumershippingcosts->count() > 0 ?
                    (
                    $orderTmp->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null  ? 'Lỗi: '.$orderTmp->id.'---'.Auth::user()->id :
                    $orderTmp->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_partner_code
                    ) :'';

                $datas[$k]['order_number'] = $order->order_number;
                $datas[$k]['total_qty'] =  $order->total_qty;
                $datas[$k]['tong_chi_phi'] = number_format(round(($price + $price_sp + $tax_amount + $shipping_cost  - $shop_discount - $shopping_point) * $orderTmp->currency_value, 2));
                $datas[$k]['phuong_thuc_thanh_toan'] = $orderTmp->method;
                $datas[$k]['ngay_thanh_toan'] = $vendor_info != null && $vendor_info->is_paid == 1 ? \Carbon\Carbon::parse($vendor_info->payment_to_merchant_date)->format('d/m/Y') : '';
                $datas[$k]['so_tien_thanh_toan'] =  $vendor_info != null && $vendor_info->is_paid == 1 ? number_format($vendor_info->payment_to_merchant_amount) : '';
                $datas[$k]['ma_van_don'] = $ma_van_don;
                $datas[$k]['ngay_dat_hang'] = \Carbon\Carbon::parse($order->created_at_order)->format('d/m/Y') ;
                $datas[$k]['ten_nguoi_mua']  = $orderTmp->user->name;
                $datas[$k]['email_nguoi_mua']  = $orderTmp->user->email;
                $datas[$k]['phone_nguoi_mua']  = $orderTmp->user->phone;
            }
        }

        $datas =  collect($datas);
        $file_name = 'orders_'.$status.'_'.$from.'_'.$to.'___'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new VendorOrderExport($datas), $file_name, null, []);
    }
}


// $data = VendorOrder::where('user_id', '=', $user->id)
//     ->where('status', '=', $status)
//     ->get()
//     ->groupBy('order_number');

// $orders = $data->sortByDesc(function ($item, $key) {
//     return $item[0]->order->created_at;
// });
// $orders1 =  DB::select(
//     DB::raw("SELECT v.*, o.created_at as created_at_order
//         , SUM(v.qty) as total_qty
//         , SUM(v.price) as total_price
//         , SUM(v.price_shopping_point_amount) as total_price_shopping_point_amount
//         , SUM(v.shop_coupon_amount) as total_shop_coupon_amount
//         , SUM(v.shopping_point_amount) as total_shopping_point_amount
//        FROM vendor_orders  as v
//        inner join orders as o on o.id = v.order_id
//        where v.user_id = '".$user->id."'  and  v.status = '" . $status . "'
//        group by v.order_number
//        order by  o.created_at desc
//        limit 10
// "));
// $orders = collect($orders);
// $vendor_info = $order->order->ordervendorinfosv2();
