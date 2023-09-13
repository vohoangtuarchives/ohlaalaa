<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;

use App\Models\OrderTrack;
use App\Models\VendorOrder;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use App\Http\Controllers\Controller;

class OrderTrackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }


   //*** GET Request
    public function index($id)
    {
    	$order = Order::findOrFail($id);
        return view('admin.order.track',compact('order'));
    }

   //*** GET Request
    public function load($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.order.track-load',compact('order'));
    }


    public function add()
    {


        //--- Logic Section

        $title = $_GET['title'];

        $ck = OrderTrack::where('order_id','=',$_GET['id'])->where('title','=',$title)->first();
        if($ck){
            $ck->order_id = $_GET['id'];
            $ck->title = $_GET['title'];
            $ck->text = $_GET['text'];
            $ck->update();
        }
        else {
            $data = new OrderTrack;
            $data->order_id = $_GET['id'];
            $data->title = $_GET['title'];
            $data->text = $_GET['text'];
            $data->save();
        }


        //--- Logic Section Ends


    }


    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        // $rules = [
        //        'title' => 'unique:order_tracks',
        //         ];
        // $customs = [
        //        'title.unique' => 'This title has already been taken.',
        //            ];
        // $validator = Validator::make($request->all(), $rules, $customs);
        // if ($validator->fails()) {
        //   return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        // }
        //--- Validation Section Ends

        //--- Logic Section

        $title = $request->title;
        $ck = OrderTrack::where('order_id','=',$request->order_id)->where('title','=',$title)->first();
        if($ck) {
            $ck->order_id = $request->order_id;
            $ck->title = $request->title;
            $ck->text = $request->text;
            $ck->update();

        //--- Redirect Section
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

        }
        else {
            $data = new OrderTrack;
            $input = $request->all();
            $data->fill($input)->save();
        }

        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }


    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
               'title' => 'unique:order_tracks,title,'.$id
                ];
        $customs = [
               'title.unique' => 'This title has already been taken.',
                   ];
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = OrderTrack::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }

    //*** GET Request
    public function delete($id)
    {
        $data = OrderTrack::findOrFail($id);
        $data->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    public function cartItems($id){
        $order = Order::find($id);

        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);

        dd($cart->items);
    }

    public function checkout($id){
        $order = Order::find($id);
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);

        foreach($cart->items as $prod)
        {
            $x = (string)$prod['stock'];
            // dd($prod['stock'] );
            if($x != null)
            {
                $product = Product::findOrFail($prod['item']['id']);
                $product->stock =  $prod['stock'];
                // $product->update();
                if($product->stock <= 5)
                {
                    $notification = new Notification;
                    $notification->product_id = $product->id;
                    // $notification->save();
                }
            }
        }

        foreach($cart->items as $prod)
        {
            dd($prod['size_qty']);
            $x = (string)$prod['size_qty'];
            dd($x);
            if(!empty($x))
            {
                $product = Product::findOrFail($prod['item']['id']);
                dd($product);
                $x = (int)$x;
                $x = $x - $prod['qty'];
                $temp = $product->size_qty;
                $temp[$prod['size_key']] = $x;
                $temp1 = implode(',', $temp);
                $product->size_qty =  $temp1;
                //$product->update();
            }
        }

        dd($cart->items);
    }

    public function recoverVendorOrder($id)
    {
        $order = Order::findOrFail($id);
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);

        $products_amount = 0;
        $tax_amount = 0;
        $coupon_discount = 0;
        $vendor_coupon_discount = 0;
        $sp_amount = 0;
        $shipping_cost = 0;
		$cart_items = collect($cart->items);
		$products_amount = $cart_items->sum(function ($i) {
            return $i['item_price'] * $i['qty'] + $i['price_shopping_point_amount'];
        });
        $tax_amount = $products_amount * $order->tax / 100.0;
        $sp_amount = $cart->totalSPAmount;
		$cart->totalProductFinalAmount = $cart->totalProductSubAmount - $cart->totalShopCouponAmount ;
        $vendor_coupon_discount = $cart->totalShopCouponAmount;
        $coupon_discount = $order->coupon_discount;
        $order->shipping_cost = 0;
        $shipping_cost = $order->shipping_cost;
        $item_count = count($cart->items);

        if($order->vendororders()->count() == 0){
            foreach($cart->items as $prod)
            {
                if($prod['item']['user_id'] != 0)
                {
                    $vorder =  new VendorOrder;
                    $vorder->order_id = $order->id;
                    $vorder->user_id = $prod['item']['user_id'];
                    $vorder->product_id = $prod['item']['id'];
                    $vorder->product_name = $prod['item']['name'];
                    $notf[] = $prod['item']['user_id'];
                    $vorder->qty = $prod['qty'];
                    $vorder->price = $prod['price'];
                    $vorder->unit_price = $prod['item_price'];
                    $vorder->order_number = $order->order_number;
                    $vorder->is_shopping_point_used = $prod['is_shopping_point_used'];
                    $vorder->shopping_point_used = $prod['shopping_point_used'];
                    $vorder->shopping_point_amount = $prod['shopping_point_amount'];
                    $vorder->shopping_point_payment_remain = $prod['shopping_point_payment_remain'];
                    $vorder->exchange_rate = $prod['exchange_rate'];
                    $vorder->shop_coupon_code = $prod['shop_coupon_code'];
                    $vorder->shop_coupon_amount = $prod['shop_coupon_amount'];
                    $vorder->shop_coupon_value = $prod['shop_coupon_value'];
                    $vorder->shop_coupon_times = $prod['shop_coupon_times'];
                    $vorder->shop_coupon_used = $prod['shop_coupon_used'];
                    $vorder->price_shopping_point = $prod['price_shopping_point'];
                    $vorder->price_shopping_point_amount = $prod['price_shopping_point_amount'];
                    $vorder->percent_shopping_point = $prod['percent_shopping_point'];
                    $vorder->item_price_shopping_point = $prod['item_price_shopping_point'];
                    $vorder->product_sub_amount = $prod['product_sub_amount'];
                    $vorder->product_final_amount = $prod['product_final_amount'];
                    $vorder->shipping_cost = $shipping_cost / $item_count;
                    $vorder->save();
                }
            }
        }

        $order->pay_amount = $products_amount + $tax_amount + $shipping_cost - $vendor_coupon_discount - $coupon_discount;
        $order->pay_amount1 = $order->pay_amount;
        $order->pay_amount2 = round($order->pay_amount1 - $sp_amount, 0);
        $order->pay_cost = $tax_amount + $shipping_cost;
        $order->pay_discount = $vendor_coupon_discount + $coupon_discount + $sp_amount;
        $order->pay_amount3 = $products_amount - $order->pay_discount;
        $order->pay_amount4 = round($order->pay_amount3 + $order->pay_cost, 0);
        $order->save();

        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
    }

}
