<?php

namespace App\Http\Controllers\Front;

use DB;
use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Alepay;
use App\Classes\VNPay;
use App\Classes\Onepay;
use App\Models\Coupon;
use App\Models\Pickup;
use App\Models\Product;
use App\Models\Currency;
use App\Enums\ModuleCode;
use App\Models\OrderTrack;
use App\Models\Pagesetting;
use App\Models\VendorOrder;
use Illuminate\Support\Str;
use App\Models\CouponVendor;
use App\Models\Notification;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use App\Models\DevelopmentNote;
use App\Models\UserNotification;
use App\Models\OrderVNPayTrackLog;
use App\Models\OrderOnepayTrackLog;
use App\Models\CouponVendorUsedLog;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    public function loadpayment($slug1,$slug2)
    {
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        }
        else {
            $curr = Currency::where('is_default','=',1)->first();
        }
        $payment = $slug1;
        $pay_id = $slug2;
        $gateway = '';
        if($pay_id != 0) {
            $gateway = PaymentGateway::findOrFail($pay_id);
        }
        return view('load.payment',compact('payment','pay_id','gateway','curr'));
    }

    public function checkout()
    {
        $this->code_image();
        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
        }
        $gs = Generalsetting::findOrFail(1);
        $dp = 1;
        $vendor_shipping_id = 0;
        $vendor_packing_id = 0;
        if (Session::has('currency'))
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }

// If a user is Authenticated then there is no problm user can go for checkout

        if(Auth::guard('web')->check())
        {
            $gateways =  PaymentGateway::where('status','=',1)->get();
            $pickups = Pickup::all();
            $oldCart = Session::get('cart');
            $cart = new Cart($oldCart);
            $products = $cart->items;

            // Shipping Method

            if($gs->multiple_shipping == 1)
            {
                $user = null;
                foreach ($cart->items as $prod) {
                    $user[] = $prod['item']['user_id'];
                }
                $users = array_unique($user);
                if(count($users) == 1)
                {
                    $shipping_data  = DB::table('shippings')->where('user_id','=',$users[0])->get();
                    if(count($shipping_data) == 0){
                        $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                    }
                    else{
                        $vendor_shipping_id = $users[0];
                    }
                }
                else {
                    $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                }

            }
            else{
                $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
            }

            // Packaging

            if($gs->multiple_packaging == 1)
            {
                $user = null;
                foreach ($cart->items as $prod) {
                    $user[] = $prod['item']['user_id'];
                }
                $users = array_unique($user);
                if(count($users) == 1)
                {
                    $package_data  = DB::table('packages')->where('user_id','=',$users[0])->get();
                    if(count($package_data) == 0){
                        $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                    }
                    else{
                        $vendor_packing_id = $users[0];
                    }
                }
                else {
                    $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                }
            }
            else{
                $package_data  = DB::table('packages')->where('user_id','=',0)->get();
            }

            foreach ($products as $prod) {
                if($prod['item']['type'] == 'Physical')
                {
                    $dp = 0;
                    break;
                }
            }
            if($dp == 1)
            {
                $ship  = 0;
            }
            $total = $cart->totalPrice + $cart->totalSPPriceAmount;
            $coupon = Session::has('coupon') ? Session::get('coupon') : 0;
            if($gs->tax != 0)
            {
                $tax = ($total / 100) * $gs->tax;
                $total = $total + $tax;
            }
            if(!Session::has('coupon_total'))
            {
                $total = $total - $coupon;
                $total = $total + 0;
            }
            else {
                $total = Session::get('coupon_total');
                $total = $total + round(0 * $curr->value, 0);
            }
            $cart_items = collect($cart->items);

            $cart->totalSPUsed = $cart_items->sum(function ($i) {
                return $i['shopping_point_used'];
            });
            $cart->totalSPAmount = $cart_items->sum(function ($i) {
                return $i['shopping_point_amount'];
            });
            $cart->totalShopCouponAmount = $cart_items->sum(function ($i) {
                return $i['shop_coupon_amount'];
            });
            $cart->totalSPPrice = $cart_items->sum(function ($i) {
                return $i['price_shopping_point'];
            });
            $cart->totalSPPriceAmount = $cart_items->sum(function ($i) {
                return $i['price_shopping_point_amount'];
            });
            $cart->totalSPPriceRemainAmount = $cart_items->sum(function ($i) {
                return $i['shopping_point_payment_remain'];
            });
            $cart->totalProductSubAmount = $cart_items->sum(function ($i) {
                return $i['product_sub_amount'];
            });
            $cart->totalProductFinalAmount = $cart_items->sum(function ($i) {
                return $i['product_final_amount'];
            });
            Session::put('cart',$cart);

            $total = $total - $cart->totalSPAmount - $cart->totalShopCouponAmount;
            $total =  $total > 0 ?  $total : 0;

            return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'digital' => $dp, 'curr' => $curr,'shipping_data' => $shipping_data,'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
        }
        else
        {
// If guest checkout is activated then user can go for checkout
           	if($gs->guest_checkout == 1)
            {
                $gateways =  PaymentGateway::where('status','=',1)->get();
                $pickups = Pickup::all();
                $oldCart = Session::get('cart');
                $cart = new Cart($oldCart);
                $products = $cart->items;
                // Shipping Method

                if($gs->multiple_shipping == 1)
                {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if(count($users) == 1)
                    {
                        $shipping_data  = DB::table('shippings')->where('user_id','=',$users[0])->get();
                        if(count($shipping_data) == 0){
                            $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                        }
                        else{
                            $vendor_shipping_id = $users[0];
                        }
                    }
                    else {
                        $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                    }
                }
                else{
                    $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                }
                // Packaging
                if($gs->multiple_packaging == 1)
                {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if(count($users) == 1)
                    {
                        $package_data  = DB::table('packages')->where('user_id','=',$users[0])->get();
                        if(count($package_data) == 0){
                            $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                        }
                        else{
                            $vendor_packing_id = $users[0];
                        }
                    }
                    else {
                        $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                    }
                }
                else{
                    $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                }

                foreach ($products as $prod) {
                    if($prod['item']['type'] == 'Physical')
                    {
                        $dp = 0;
                        break;
                    }
                }
                if($dp == 1)
                {
                    $ship  = 0;
                }
                $total = $cart->totalPrice;
                $coupon = Session::has('coupon') ? Session::get('coupon') : 0;
                if($gs->tax != 0)
                {
                    $tax = ($total / 100) * $gs->tax;
                    $total = $total + $tax;
                }
                if(!Session::has('coupon_total'))
                {
                    $total = $total - $coupon;
                    $total = $total + 0;
                }
                else {
                    $total = Session::get('coupon_total');
                    $total =  str_replace($curr->sign,'',$total) + round(0 * $curr->value, 0);
                }
                $total = $total - $cart->totalSPAmount - $cart->totalShopCouponAmount;
                $total =  $total > 0 ?  $total : 0;
                foreach ($products as $prod) {
                    if($prod['item']['type'] != 'Physical')
                    {
                        if(!Auth::guard('web')->check())
                        {
                            $ck = 1;
                            return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'checked' => $ck, 'digital' => $dp, 'curr' => $curr,'shipping_data' => $shipping_data,'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
                        }
                    }
                }
                return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'digital' => $dp, 'curr' => $curr,'shipping_data' => $shipping_data,'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
            }

// If guest checkout is Deactivated then display pop up form with proper error message

            else{
                $gateways =  PaymentGateway::where('status','=',1)->get();
                $pickups = Pickup::all();
                $oldCart = Session::get('cart');
                $cart = new Cart($oldCart);
                $products = $cart->items;

                // Shipping Method

                if($gs->multiple_shipping == 1)
                {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if(count($users) == 1)
                    {
                        $shipping_data  = DB::table('shippings')->where('user_id','=',$users[0])->get();

                        if(count($shipping_data) == 0){
                            $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                        }
                        else{
                            $vendor_shipping_id = $users[0];
                        }
                    }
                    else {
                        $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                    }
                }
                else{
                    $shipping_data  = DB::table('shippings')->where('user_id','=',0)->get();
                }

                // Packaging

                if($gs->multiple_packaging == 1)
                {
                    $user = null;
                    foreach ($cart->items as $prod) {
                        $user[] = $prod['item']['user_id'];
                    }
                    $users = array_unique($user);
                    if(count($users) == 1)
                    {
                        $package_data  = DB::table('packages')->where('user_id','=',$users[0])->get();

                        if(count($package_data) == 0){
                            $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                        }
                        else{
                            $vendor_packing_id = $users[0];
                        }
                    }
                    else {
                        $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                    }

                }
                else{
                    $package_data  = DB::table('packages')->where('user_id','=',0)->get();
                }


                $total = $cart->totalPrice;
                $coupon = Session::has('coupon') ? Session::get('coupon') : 0;
                if($gs->tax != 0)
                {
                    $tax = ($total / 100) * $gs->tax;
                    $total = $total + $tax;
                }
                if(!Session::has('coupon_total'))
                {
                    $total = $total - $coupon;
                    $total = $total + 0;
                }
                else {
                    $total = Session::get('coupon_total');
                    $total = $total + round(0 * $curr->value, 0);
                }
                $total = $total - $cart->totalSPAmount - $cart->totalShopCouponAmount;
                $total =  $total > 0 ?  $total : 0;
                $ck = 1;
                return view('front.checkout', ['products' => $cart->items, 'totalPrice' => $total, 'pickups' => $pickups, 'totalQty' => $cart->totalQty, 'gateways' => $gateways, 'shipping_cost' => 0, 'checked' => $ck, 'digital' => $dp, 'curr' => $curr,'shipping_data' => $shipping_data,'package_data' => $package_data, 'vendor_shipping_id' => $vendor_shipping_id, 'vendor_packing_id' => $vendor_packing_id]);
            }
        }
    }

    public function encartorderimport($orderId)
    {
        //dd(Session::get('cart'));
        if (!Session::has('cart')) {
            return null;
        }
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        $encart = utf8_encode(bzcompress(serialize($cart), 9));

        $track = new OrderTrack;
        $order = Order::findOrFail($orderId);
        $track->title = $order->status;
        $track->text = 'Import Cart done.';
        $track->order_id = $orderId;
        $track->save();
        Session::put('temporder_id',$orderId);
        Session::put('tempcart',$cart);
        Session::forget('cart');
        Session::forget('already');
        Session::forget('coupon');
        Session::forget('coupon_total');
        Session::forget('coupon_total1');
        Session::forget('coupon_percentage');
        return $encart;
    }


    ///////////////////////////
    ///////////////////////////
    ///////////////////////////
    ///////////////////////////                                     CASH ON DELIVERY
    ///////////////////////////
    /////////////////////////
    //////////////////////////
    ///////////////////////////
    public function cashondelivery(Request $request)
    {
        $order_id = '';
        try{

            if(!Auth::check()){
                return redirect()->back()->with('unsuccess',"Unauthenticated!");
            }

            if($request->shipping_type == 'negotiate'){
                return redirect()->back()->with('unsuccess',"Please select a valid payment!");
            }

            if($request->pass_check) {
                $users = User::where('email','=',$request->personal_email)->get();
                if(count($users) == 0) {
                    if ($request->personal_pass == $request->personal_confirm){
                        $user = new User;
                        $user->name = $request->personal_name;
                        $user->email = $request->personal_email;
                        $user->password = bcrypt($request->personal_pass);
                        $token = md5(time().$request->personal_name.$request->personal_email);
                        $user->verification_link = $token;
                        $user->affilate_code = md5($request->name.$request->email);
                        $user->emai_verified = 'Yes';
                        //$user->save();
                        Auth::guard('web')->login($user);
                    }else{
                        return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");
                    }
                }
                else {
                    return redirect()->back()->with('unsuccess',"This Email Already Exist.");
                }
            }

            if (!Session::has('cart')) {
                return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
            }
            if (Session::has('currency'))
            {
                $curr = Currency::find(Session::get('currency'));
            }
            else
            {
                $curr = Currency::where('is_default','=',1)->first();
            }
            $handling_user = Auth::user();
            $gs = Generalsetting::findOrFail(1);
            $oldCart = Session::get('cart');
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
            $tax_amount = $products_amount * $request->tax / 100.0;

            $cart->totalQty = $cart_items->sum(function ($i) {
                return $i['qty'];
            });

            foreach($cart->items as $cart_item)
            {
                if($cart_item["is_shopping_point_used"] == 1){
                    $maxpoint = intval($cart_item["price_shopping_point"]);
                    $point = $cart_item["shopping_point_used"];
                    if($point > $maxpoint){
                        return redirect()->back()->with('unsuccess','Có sản phẩm ['.$cart_item["item"]["name"].'] sử dụng shopping point không hợp lệ!');
                    }
                    $cart_item["exchange_rate"] = $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_amount"] = $point * $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'] - $cart_item["shopping_point_amount"];
                }
                else{
                    $cart_item["shopping_point_used"] = 0;
                    $cart_item["exchange_rate"] = 0;
                    $cart_item["shopping_point_amount"] = 0;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'];
                }
                $cart_item['product_sub_amount'] = $cart_item['price'] + $cart_item['price_shopping_point_amount'] - $cart_item['shopping_point_amount'];
                $size = $cart_item['size'];
                $color =  $cart_item['color'];
                $values = $cart_item['values'];
                $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
                // $cart->items[$cart_item['item']->id] = $cart_item;
            }

            $cart->totalSPUsed = $cart_items->sum(function ($i) {
                return $i['shopping_point_used'];
            });
            $cart->totalSPAmount = $cart_items->sum(function ($i) {
                return $i['shopping_point_amount'];
            });
            $cart->totalProductSubAmount = $cart_items->sum(function ($i) {
                return $i['product_sub_amount'];
            });

            $sp_amount = $cart->totalSPAmount;
            $totalSPRemain = Auth::user()->shopping_point - $cart->totalSPUsed;
            if($totalSPRemain < 0){
                return redirect()->back()->with('unsuccess','Số dư ví shopping point không đủ!');
            }

            //check all shop's coupon are available
            $used_coupons = array();
            foreach($cart->items as $cart_item)
            {
                if($cart_item["shop_coupon_amount"] > 0){
                    if(!empty($cart_item["shop_coupon_code"])){
                        $code = $cart_item["shop_coupon_code"];
                        $now = Carbon::now()->format('Y-m-d');
                        $coupon = CouponVendor::where('code','=', $code)
                            ->where(function ($query) {
                                $query->where('times', '>', 0)
                                    ->orWhereNull('times');
                            })
                            ->where('status', '=', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now)
                            ->where('vendor_id','=',$cart_item["item"]["user_id"])
                            ->first();
                        if($coupon != null){
                            $product_sub_amount = $cart_item['product_sub_amount'];
                            $cart_item["shop_coupon_value"] = $coupon->price;
                            if($coupon->type == 0){
                                $cart_item["shop_coupon_amount"] = $product_sub_amount * $coupon->price / 100.0;
                            }
                            else{
                                $cart_item["shop_coupon_amount"] = $coupon->price;
                            }
                            //subtract coupon times
                            if($coupon->times != null){
                                $coupon->times = $coupon->times - 1;
                            }
                            $coupon->used = $coupon->used + 1;
                            $cart_item["shop_coupon_times"] = $coupon->times;
                            $cart_item["shop_coupon_used"] = $coupon->used;
                            $coupon->save();
                            array_push($used_coupons, $coupon);
                            $coupon_vendor_log = new CouponVendorUsedLog;
                            $coupon_vendor_log->writeLog($coupon, $handling_user->id, null, null);
                        }
                        else{
                            //roll back used coupon
                            foreach($used_coupons as $u_cp){
                                $coupon_vendor_log = CouponVendorUsedLog::where('user_id', '=', $handling_user->id)
                                    ->where('coupon_id','=',$u_cp->id)
                                    ->where('used','=',$u_cp->used)
                                    ->first();
                                    ;
                                $coupon_vendor_log->delete();
                                if($u_cp->times != null){
                                    $u_cp->times = $u_cp->times + 1;
                                }
                                $u_cp->used = $u_cp->used - 1;
                                $u_cp->save();
                            }
                            return redirect()->back()->with('unsuccess','Số lượng Mã giảm giá đã hết! Vui lòng thử lại!');
                        }
                    }
                    else{
                        $cart_item["shop_coupon_amount"] = 0;
                        $cart_item["shop_coupon_value"] = 0;
                        $cart_item["shop_coupon_code"] = '';
                    }
                    $cart_item['product_final_amount'] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
                    $size = $cart_item['size'];
                    $color =  $cart_item['color'];
                    $values = $cart_item['values'];
                    $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
                    // $cart->items[$cart_item['item']->id] = $cart_item;
                }
            }

            $cart->totalShopCouponAmount = $cart_items->sum(function ($i) {
                return $i['shop_coupon_amount'];
            });

            $cart->totalProductFinalAmount = $cart->totalProductSubAmount - $cart->totalShopCouponAmount ;
            $vendor_coupon_discount = $cart->totalShopCouponAmount;
            $coupon_discount = $request->coupon_discount;
            $order_amount1 = $products_amount - $vendor_coupon_discount - $coupon_discount - $sp_amount;
            if($order_amount1 < 0){
                return redirect()->back()->with('unsuccess','Giá trị khuyến mãi vượt quá giá trị đơn hàng! Vui lòng kiểm tra lại');
            }

            foreach($cart->items as $key => $prod)
            {
                if(!empty($prod['item']['license']) && !empty($prod['item']['license_qty']))
                {
                    foreach($prod['item']['license_qty']as $ttl => $dtl)
                    {
                        if($dtl != 0)
                        {
                            $dtl--;
                            $produc = Product::findOrFail($prod['item']['id']);
                            $temp = $produc->license_qty;
                            $temp[$ttl] = $dtl;
                            $final = implode(',', $temp);
                            $produc->license_qty = $final;
                            $produc->update();
                            $temp =  $produc->license;
                            $license = $temp[$ttl];
                            $oldCart = Session::has('cart') ? Session::get('cart') : null;
                            $cart = new Cart($oldCart);
                            $cart->updateLicense($prod['item']['id'],$license);
                            Session::put('cart',$cart);
                            break;
                        }
                    }
                }
            }

            $order = new Order;
            $success_url = action('Front\PaymentController@payreturn');
            $item_name = $gs->title." Order";
            $item_number = Str::random(10);
            // $order_number = date("Ym").Str::random(10);
            $order_number = $this->generateOrderNumber($request->user_id);
            $order['user_id'] = $request->user_id;
            $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
            $order['totalQty'] = $cart->totalQty;
            $order['pay_amount'] = round($request->total / $curr->value, 0);
            $order['method'] = $request->method;
            $order['shipping'] = $request->shipping;
            $order['shipping_type'] = $request->shipping_type;
            $order['pickup_location'] = $request->pickup_location;
            $order['customer_email'] = $request->email;
            $order['customer_name'] = $request->name;
            $order['shipping_cost'] = 0;
            $order['packing_cost'] = $request->packing_cost;
            $order['tax'] = $request->tax;
            $order['customer_phone'] = $request->phone;
            $order['order_number'] = $order_number;
            $order['customer_address'] = $request->address;
            $order['customer_country'] = $request->customer_country;
            $order['customer_city'] = $request->city;
            $order['customer_zip'] = $request->zip;
            $order['shipping_email'] = $request->shipping_email;
            $order['shipping_name'] = $request->shipping_name;
            $order['shipping_phone'] = $request->shipping_phone;
            $order['shipping_address'] = $request->shipping_address;
            $order['shipping_country'] = $request->shipping_country;
            $order['shipping_city'] = $request->shipping_city;
            $order['shipping_zip'] = $request->shipping_zip;
            $order['order_note'] = $request->order_notes;
            $order['coupon_code'] = $request->coupon_code;
            $order['coupon_discount'] = $request->coupon_discount;
            $order['dp'] = $request->dp;
            $order['payment_status'] = "Pending";
            $order['currency_sign'] = $curr->sign;
            $order['currency_value'] = $curr->value;
            $order['vendor_shipping_id'] = $request->vendor_shipping_id;
            $order['vendor_packing_id'] = $request->vendor_packing_id;
            $order['customer_province_id'] = $request->customer_province;
            $order['customer_district_id'] = $request->customer_district;
            $order['customer_ward_id'] = $request->customer_ward;
            $order['shipping_province_id'] = $request->shipping_province;
            $order['shipping_district_id'] = $request->shipping_district;
            $order['shipping_ward_id'] = $request->shipping_ward;
            $order['is_shipdiff'] = $request->is_shipdiff == 'true' ? 1 : 0;
            $order['is_online_payment'] = 0;
            $order['products_amount'] = $products_amount;
            $order['vendor_discount_amount'] = $vendor_coupon_discount;
            $order['is_shopping_point_used'] = $cart->totalSPUsed > 0 ? 1 : 0;
            $order['shopping_point_used'] = $cart->totalSPUsed;
            $order['shopping_point_exchange_rate'] = $gs->sp_vnd_exchange_rate;
            $order['shopping_point_amount'] = $cart->totalSPAmount;
            $order['shopping_point_payment_remain'] = $cart->totalSPPriceRemainAmount;
            $order['total_sp_price'] = $cart->totalSPPrice;
            $order['total_sp_price_amount'] = $cart->totalSPPriceAmount;
            $order['total_sp_price_remain_amount'] = $cart->totalSPPriceRemainAmount;
            $order['total_product_sub_amount'] = $cart->totalProductSubAmount;
            $order['total_product_final_amount'] = $cart->totalProductFinalAmount;

            if (Session::has('affilate'))
            {
                $val = $request->total / $curr->value;
                $val = $val / 100;
                $sub = $val * $gs->affilate_charge;
                $order['affilate_user'] = Session::get('affilate');
                $order['affilate_charge'] = $sub;
            }

            $order->save();

            $order_id =  $order->id;

            if($order['shipping_type'] == 'viettelpost'){
                $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->createorder($order, $cart);
                if(!$result_viettel_post[0]){
                    //$order['status'] = 'declined';
                    $order['order_note'] = 'Viettel Post Failed!';
                    $order->save();
                    Session::flash('unsuccess', 'Không thể gửi yêu cầu đến đơn vị vận chuyển! Hãy thử phương thức thanh toán khác! Code: '. $result_viettel_post[2]);
                    return redirect()->back()->withInput();
                }
                else{
                    $order = $result_viettel_post[1];
                }
            }

            $shipping_cost = $order->shipping_cost;
            $order->pay_amount = $products_amount + $tax_amount + $shipping_cost - $vendor_coupon_discount - $coupon_discount;
            $order->pay_amount1 = $order->pay_amount;
            $order->pay_amount2 = round($order->pay_amount1 - $sp_amount, 0);
            $order->percent_discount = $order->total_product_final_amount > 0 ? $coupon_discount / $order->total_product_final_amount : 0;
            $order->pay_cost = $tax_amount + $shipping_cost;
            $order->pay_discount = $vendor_coupon_discount + $coupon_discount + $sp_amount;
            $order->pay_amount3 = $products_amount - $order->pay_discount;
            $order->pay_amount4 = round($order->pay_amount3 + $order->pay_cost, 0);

            $order->save();

            if($order['shopping_point_used'] > 0) {
                $consumer = Auth::user();
                $point_log = new UserPointLog;
                $point_log->user_id = $consumer->id;
                $point_log->log_type = 'Use Shopping';
                $point_log->order_ref_id = $order->id;
                $point_log->reward_point_balance = isset($consumer->reward_point) ? $consumer->reward_point : 0;
                $point_log->shopping_point_balance = isset($consumer->shopping_point) ? $consumer->shopping_point : 0;
                $point_log->exchange_rate = 0;
                $point_log->note = 'Pay for order ['.$order->id.']';
                $point_log->reward_point = 0;
                $point_log->shopping_point = -$order['shopping_point_used'];
                $point_log->descriptions = 'Bạn đã đổi điểm shopping point cho đơn hàng số ['.$order->order_number.']';
                $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                $point_log->amount = $order['pay_amount1'];
                $consumer->shopping_point = $consumer->shopping_point - $cart->totalSPUsed;
                $consumer->save();
                $point_log->save();
            }

            $track = new OrderTrack;
            $track->title = 'Pending';
            $track->text = 'You have successfully placed your order.';
            $track->order_id = $order->id;
            $track->save();

            $notification = new Notification;
            $notification->order_id = $order->id;
            $notification->save();
            if($request->coupon_id != "")
            {
                $coupon = Coupon::findOrFail($request->coupon_id);
                $coupon->used++;
                if($coupon->times != null)
                {
                    $i = (int)$coupon->times;
                    $i--;
                    $coupon->times = (string)$i;
                }
                $coupon->update();

            }

            try{
                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['size_qty'];
                    if(!empty($x))
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $x = (int)$x;
                        $x = $x - $prod['qty'];
                        $temp = $product->size_qty;
                        $temp[$prod['size_key']] = $x;
                        $temp1 = implode(',', $temp);
                        $product->size_qty =  $temp1;
                        $product->update();
                    }
                }

                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['stock'];
                    if($x != null)
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $product->stock =  $prod['stock'];
                        $product->update();
                        if($product->stock <= 5)
                        {
                            $notification = new Notification;
                            $notification->product_id = $product->id;
                            $notification->save();
                        }
                    }
                }
            }
            catch (\Exception $e){
                $msg = $e->getMessage();
            }

            $notf = null;

            $item_count = count($cart->items);

            foreach($cart->items as $prod)
            {
                if($prod['item']['user_id'] != 0)
                {
                    $vorder =  new VendorOrder;
                    $vorder->order_id = $order->id;
                    $vorder->user_id = $prod['item']['user_id'];
                    $notf[] = $prod['item']['user_id'];
                    $vorder->qty = $prod['qty'];
                    $vorder->price = $prod['price'];
                    $vorder->unit_price = $prod['item_price'];
                    $vorder->product_id = $prod['item']['id'];
                    $vorder->product_name = $prod['item']['name'];
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

            if(!empty($notf))
            {
                $users = array_unique($notf);
                foreach ($users as $user) {
                    $notification = new UserNotification;
                    $notification->user_id = $user;
                    $notification->order_number = $order->order_number;
                    $notification->save();
                }
            }

            Session::put('temporder_id',$order->id);
            Session::put('tempcart',$cart);
            Session::forget('cart');
            Session::forget('already');
            Session::forget('coupon');
            Session::forget('coupon_total');
            Session::forget('coupon_total1');
            Session::forget('coupon_percentage');

            //Sending Email To Buyer
            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $request->email,
                    'type' => "new_order",
                    'cname' => $request->name,
                    'oamount' => "",
                    'aname' => "",
                    'aemail' => "",
                    'wtitle' => "",
                    'onumber' => $order->order_number,
                ];

                $mailer = new GeniusMailer();
                $mailer->sendAutoOrderMail1($data,$order->id);

                $mailer = new GeniusMailer();
                $rs = $mailer->sendAutoOrderMail2($order->id);
            }
            else
            {
            $to = $request->email;
            $subject = "Your Order Placed!!";
            $msg = "Hello ".$request->name."!\nYou have placed a new order.\nYour order number is ".$order->order_number.".Please wait for your delivery. \nThank you.";
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
            }

            //Sending Email To Admin
            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => Pagesetting::find(1)->contact_email,
                    'subject' => "New Order Recieved!!",
                    'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is ".$order->order_number.".Please login to your panel to check. <br>Thank you.",
                ];

                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);
            }
            else
            {
            $to = Pagesetting::find(1)->contact_email;
            $subject = "New Order Recieved!!";
            $msg = "Hello Admin!\nYour store has recieved a new order.\nOrder Number is ".$order->order_number.".Please login to your panel to check. \nThank you.";
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
            }
            return redirect($success_url);

        }
        catch (\Exception $e){
            $msg = $e->getMessage();
            return redirect()->back()->with('unsuccess', $msg);
            $note = new DevelopmentNote;
            $note->title = 'CheckOut';
            $note->code = $order_id;
            $note->content = serialize($request->all());
            $note->note = 'Checkout on delivery bug';
            $note->save();
        }
    }

    ////////////////////////////////////
    ////////////////////////////////////
    ////////////////////////////////////
    //////////////////////////////////////                                      gateway start
    ////////////////////////////////////
    ////////////////////////////////////
    ////////////////////////////////////

    public function gateway(Request $request)
    {
        $order_id = '';
        try{
            if(!Auth::check()){
                return redirect()->back()->with('unsuccess',"Unauthenticated!");
            }
            $input = $request->all();

            $rules = [
                'txn_id4' => 'required',
            ];
            $messages = [
                'required' => 'The Transaction ID field is required.',
            ];

            $validator = Validator::make($input, $rules, $messages);
            if ($validator->fails()) {
                    Session::flash('unsuccess', $validator->messages()->first());
                    return redirect()->back()->withInput();
            }

            if($request->pass_check) {
                $users = User::where('email','=',$request->personal_email)->get();
                if(count($users) == 0) {
                    if ($request->personal_pass == $request->personal_confirm){
                        $user = new User;
                        $user->name = $request->personal_name;
                        $user->email = $request->personal_email;
                        $user->password = bcrypt($request->personal_pass);
                        $token = md5(time().$request->personal_name.$request->personal_email);
                        $user->verification_link = $token;
                        $user->affilate_code = md5($request->name.$request->email);
                        $user->email_verified = 'Yes';
                        $user->save();
                        Auth::guard('web')->login($user);
                    }else{
                        return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");
                    }
                }
                else {
                    return redirect()->back()->with('unsuccess',"This Email Already Exist.");
                }
            }

            $gs = Generalsetting::findOrFail(1);
            if (!Session::has('cart')) {
                return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
            }
            $oldCart = Session::get('cart');
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
            $cart->totalQty = $cart_items->sum(function ($i) {
                return $i['qty'];
            });

            $tax_amount = $products_amount * $request->tax / 100.0;
            $digital = 1;
            $handling_user = Auth::user();

            //check shopping point
            foreach($cart->items as $cart_item)
            {
                if($cart_item["is_shopping_point_used"] == 1){
                    $maxpoint = intval($cart_item["price_shopping_point"]);
                    $point = $cart_item["shopping_point_used"];
                    if($point > $maxpoint){
                        return redirect()->back()->with('unsuccess','Có sản phẩm ['.$cart_item["item"]["name"].'] sử dụng shopping point không hợp lệ!');
                    }
                    $cart_item["exchange_rate"] = $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_amount"] = $point * $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'] - $cart_item["shopping_point_amount"];
                }
                else{
                    $cart_item["shopping_point_used"] = 0;
                    $cart_item["exchange_rate"] = 0;
                    $cart_item["shopping_point_amount"] = 0;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'];
                }
                $cart_item['product_sub_amount'] = $cart_item['price'] + $cart_item['price_shopping_point_amount'] - $cart_item['shopping_point_amount'];
                $size = $cart_item['size'];
                $color =  $cart_item['color'];
                $values = $cart_item['values'];
                $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
            }

            $cart->totalSPUsed = $cart_items->sum(function ($i) {
                return $i['shopping_point_used'];
            });
            $cart->totalSPAmount = $cart_items->sum(function ($i) {
                return $i['shopping_point_amount'];
            });
            $cart->totalProductSubAmount = $cart_items->sum(function ($i) {
                return $i['product_sub_amount'];
            });

            $sp_amount = $cart->totalSPAmount;

            $totalSPRemain = Auth::user()->shopping_point - $cart->totalSPUsed;
            if($totalSPRemain < 0){
                return redirect()->back()->with('unsuccess','Số dư ví shopping point không đủ!');
            }

            //check all shop's coupon are available
            $used_coupons = array();
            foreach($cart->items as $cart_item)
            {
                if($cart_item["shop_coupon_amount"] > 0){
                    if(!empty($cart_item["shop_coupon_code"])){
                        $code = $cart_item["shop_coupon_code"];
                        $now = Carbon::now()->format('Y-m-d');
                        $coupon = CouponVendor::where('code','=', $code)
                            ->where(function ($query) {
                                $query->where('times', '>', 0)
                                    ->orWhereNull('times');
                            })
                            ->where('status', '=', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now)
                            ->where('vendor_id','=',$cart_item["item"]["user_id"])
                            ->first();
                            if($coupon != null){
                                $product_sub_amount = $cart_item['product_sub_amount'];
                                $cart_item["shop_coupon_value"] = $coupon->price;
                                if($coupon->type == 0){
                                    $cart_item["shop_coupon_amount"] = $product_sub_amount * $coupon->price / 100.0;
                                }
                                else{
                                    $cart_item["shop_coupon_amount"] = $coupon->price;
                                }
                                //subtract coupon times
                                if($coupon->times != null){
                                    $coupon->times = $coupon->times - 1;
                                }
                                $coupon->used = $coupon->used + 1;
                                $cart_item["shop_coupon_times"] = $coupon->times;
                                $cart_item["shop_coupon_used"] = $coupon->used;
                                $coupon->save();
                                array_push($used_coupons, $coupon);
                                $coupon_vendor_log = new CouponVendorUsedLog;
                                $coupon_vendor_log->writeLog($coupon, $handling_user->id, null, null);
                            }
                            else{
                                //roll back used coupon
                                foreach($used_coupons as $u_cp){
                                    $coupon_vendor_log = CouponVendorUsedLog::where('user_id', '=', $handling_user->id)
                                        ->where('coupon_id','=',$u_cp->id)
                                        ->where('used','=',$u_cp->used)
                                        ->first();
                                        ;
                                    $coupon_vendor_log->delete();
                                    if($u_cp->times != null){
                                        $u_cp->times = $u_cp->times + 1;
                                    }
                                    $u_cp->used = $u_cp->used - 1;
                                    $u_cp->save();
                                }
                                return redirect()->back()->with('unsuccess','Số lượng Mã giảm giá đã hết! Vui lòng thử lại!');
                            }
                    }
                    else{
                        $cart_item["shop_coupon_amount"] = 0;
                        $cart_item["shop_coupon_value"] = 0;
                        $cart_item["shop_coupon_code"] = '';
                    }
                    $cart_item['product_final_amount'] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
                    $size = $cart_item['size'];
                    $color =  $cart_item['color'];
                    $values = $cart_item['values'];
                    $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
                }
                if($cart_item['item']->type == 'Physical')
                {
                    $digital = 0;
                }
            }

            $cart->totalProductFinalAmount = $cart->totalProductSubAmount - $cart->totalShopCouponAmount ;
            $vendor_coupon_discount = $cart->totalShopCouponAmount;
            $coupon_discount = $request->coupon_discount;

            if($digital == 1){
                $request->shipping_type = 'negotiate';
            }

            $order_amount1 = $products_amount - $vendor_coupon_discount - $coupon_discount - $sp_amount;
            if($order_amount1 < 0){
                return redirect()->back()->with('unsuccess','Giá trị khuyến mãi cộng Shopping Point vượt quá giá trị đơn hàng! Vui lòng kiểm tra lại');
            }

            if (Session::has('currency'))
            {
                $curr = Currency::find(Session::get('currency'));
            }
            else
            {
                $curr = Currency::where('is_default','=',1)->first();
            }

            foreach($cart->items as $key => $prod)
            {
                if(!empty($prod['item']['license']) && !empty($prod['item']['license_qty']))
                {
                    foreach($prod['item']['license_qty']as $ttl => $dtl)
                    {
                        if($dtl != 0)
                        {
                            $dtl--;
                            $produc = Product::findOrFail($prod['item']['id']);
                            $temp = $produc->license_qty;
                            $temp[$ttl] = $dtl;
                            $final = implode(',', $temp);
                            $produc->license_qty = $final;
                            $produc->update();
                            $temp =  $produc->license;
                            $license = $temp[$ttl];
                                $oldCart = Session::has('cart') ? Session::get('cart') : null;
                                $cart = new Cart($oldCart);
                                $cart->updateLicense($prod['item']['id'],$license);
                                Session::put('cart',$cart);
                            break;
                        }
                    }
                }
            }
            $settings = Generalsetting::findOrFail(1);
            $order = new Order;
            // $success_url = action('Front\PaymentController@payreturn');

            $item_name = $settings->title." Order";
            $item_number = Str::random(10);
            $order_number = $this->generateOrderNumber($request->user_id);
            $order['user_id'] = $request->user_id;
            $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
            $order['totalQty'] = $cart->totalQty;
            $order['pay_amount'] = round($request->total / $curr->value, 0);
            $order['method'] = $request->method;
            $order['shipping'] = $request->shipping;
            $order['shipping_type'] = $request->shipping_type;
            $order['pickup_location'] = $request->pickup_location;
            $order['customer_email'] = $request->email;
            $order['customer_name'] = $request->name;
            $order['shipping_cost'] = 0;
            $order['packing_cost'] = $request->packing_cost;
            $order['tax'] = $request->tax;
            $order['customer_phone'] = $request->phone;
            $order['order_number'] = $order_number;
            $order['customer_address'] = $request->address;
            $order['customer_country'] = $request->customer_country;
            $order['customer_city'] = $request->city;
            $order['customer_zip'] = $request->zip;
            $order['shipping_email'] = $request->shipping_email;
            $order['shipping_name'] = $request->shipping_name;
            $order['shipping_phone'] = $request->shipping_phone;
            $order['shipping_address'] = $request->shipping_address;
            $order['shipping_country'] = $request->shipping_country;
            $order['shipping_city'] = $request->shipping_city;
            $order['shipping_zip'] = $request->shipping_zip;
            $order['order_note'] = $request->order_notes;
            $order['txnid'] = $request->txn_id4;
            $order['coupon_code'] = $request->coupon_code;
            $order['coupon_discount'] = $request->coupon_discount;
            $order['dp'] = $request->dp;
            $order['payment_status'] = "Pending";
            $order['currency_sign'] = $curr->sign;
            $order['currency_value'] = $curr->value;
            $order['vendor_shipping_id'] = $request->vendor_shipping_id;
            $order['vendor_packing_id'] = $request->vendor_packing_id;
            $order['customer_province_id'] = $request->customer_province;
            $order['customer_district_id'] = $request->customer_district;
            $order['customer_ward_id'] = $request->customer_ward;
            $order['shipping_province_id'] = $request->shipping_province;
            $order['shipping_district_id'] = $request->shipping_district;
            $order['shipping_ward_id'] = $request->shipping_ward;
            $order['is_shipdiff'] = $request->is_shipdiff == 'true' ? 1 : 0;
            $order['is_online_payment'] = 1;
            $order['payment_bank'] = $request->payment_bank;
            $order['products_amount'] = $products_amount;
            $order['vendor_discount_amount'] = $vendor_coupon_discount;
            $order['is_shopping_point_used'] = $cart->totalSPUsed > 0 ? 1 : 0;
            $order['shopping_point_used'] = $cart->totalSPUsed;
            $order['shopping_point_exchange_rate'] = $gs->sp_vnd_exchange_rate;
            $order['shopping_point_amount'] = $cart->totalSPAmount;
            $order['shopping_point_payment_remain'] = $cart->totalSPPriceRemainAmount;
            $order['total_sp_price'] = $cart->totalSPPrice;
            $order['total_sp_price_amount'] = $cart->totalSPPriceAmount;
            $order['total_sp_price_remain_amount'] = $cart->totalSPPriceRemainAmount;
            $order['total_product_sub_amount'] = $cart->totalProductSubAmount;
            $order['total_product_final_amount'] = $cart->totalProductFinalAmount;
            $order['percent_discount'] = $order->total_product_final_amount > 0 ? $coupon_discount / $order->total_product_final_amount : 0;
            if (Session::has('affilate'))
            {
                $val = $request->total / $curr->value;
                $val = $val / 100;
                $sub = $val * $gs->affilate_charge;
                $order['affilate_user'] = Session::get('affilate');
                $order['affilate_charge'] = $sub;
            }

            $order->save();
            $order_id = $order->id;

            if($order['shopping_point_used'] > 0) {
                $consumer = Auth::user();
                $point_log = new UserPointLog;
                $point_log->user_id = $consumer->id;
                $point_log->log_type = 'Use Shopping';
                $point_log->order_ref_id = $order->id;
                $point_log->reward_point_balance = isset($consumer->reward_point) ? $consumer->reward_point : 0;
                $point_log->shopping_point_balance = isset($consumer->shopping_point) ? $consumer->shopping_point : 0;
                $point_log->exchange_rate = 0;
                $point_log->note = 'Pay for order ['.$order->id.']';
                $point_log->descriptions = 'Bạn đã đổi điểm shopping point cho đơn hàng số ['.$order->order_number.']';
                $point_log->reward_point = 0;
                $point_log->shopping_point = -$order['shopping_point_used'];
                $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                $point_log->amount = $order['pay_amount1'];
                $consumer->shopping_point = $consumer->shopping_point - $cart->totalSPUsed;
                $consumer->save();
                $point_log->save();
            }

            $track = new OrderTrack;
            $track->title = 'Pending';
            $track->text = 'You have successfully placed your order.';
            $track->order_id = $order->id;
            $track->save();
            $notification = new Notification;
            $notification->order_id = $order->id;
            $notification->save();
            if($request->coupon_id != "")
            {
                $coupon = Coupon::findOrFail($request->coupon_id);
                $coupon->used++;
                if($coupon->times != null)
                {
                    $i = (int)$coupon->times;
                    $i--;
                    $coupon->times = (string)$i;
                }
                $coupon->update();
            }

            try{
                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['size_qty'];
                    if(!empty($x))
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $x = (int)$x;
                        $x = $x - $prod['qty'];
                        $temp = $product->size_qty;
                        $temp[$prod['size_key']] = $x;
                        $temp1 = implode(',', $temp);
                        $product->size_qty =  $temp1;
                        $product->update();
                    }
                }

                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['stock'];
                    if($x != null)
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $product->stock =  $prod['stock'];
                        $product->update();
                        if($product->stock <= 5)
                        {
                            $notification = new Notification;
                            $notification->product_id = $product->id;
                            $notification->save();
                        }
                    }
                }
            }
            catch (\Exception $e){
                $msg = $e->getMessage();
            }
            $notf = null;
            $order->shipping_cost = 0;
            if($order['shipping_type'] == 'viettelpost'){
                $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->createorder($order, $cart);
                if($result_viettel_post[0]){
                    $order = $result_viettel_post[1];
                }
                else{
                    //$order['status'] = 'declined';
                    $order['order_note'] = 'Viettel Post Failed!';
                    $order->save();
                    Session::flash('unsuccess', 'Không thể gửi yêu cầu đến đơn vị vận chuyển! Hãy thử phương thức thanh toán khác!');
                    return redirect()->back()->withInput();
                }
            }
            $shipping_cost = $order->shipping_cost;

            $item_count = count($cart->items);
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

            if(!empty($notf))
            {
                $users = array_unique($notf);
                foreach ($users as $user) {
                    $notification = new UserNotification;
                    $notification->user_id = $user;
                    $notification->order_number = $order->order_number;
                    $notification->save();
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
            $vnpay_data = VNPay::createPayment($order);
            if($vnpay_data['code'] == '00'){
                Session::put('temporder_id',$order->id);
                Session::put('tempcart',$cart);
                Session::forget('cart');
                Session::forget('already');
                Session::forget('coupon');
                Session::forget('coupon_total');
                Session::forget('coupon_total1');
                Session::forget('coupon_percentage');
                $ref_url = $vnpay_data['data'];
                $vnpay_log = new OrderVNPayTrackLog;
                $vnpay_log->save_url($order->id, $ref_url);
                return redirect($ref_url);
            }
            Session::flash('unsuccess', 'Hệ thống thanh toán online đang gặp sự cố!');
            return redirect()->back()->withInput();
        }
        catch (\Exception $e){
            $msg = $e->getMessage();
            return redirect()->back()->with('unsuccess', $msg);
            $note = new DevelopmentNote;
            $note->title = 'CheckOut';
            $note->code = $order_id;
            $note->content = serialize($request->all());
            $note->note = 'Checkout Gate Way bug';
            $note->save();
        }
    }

    // Capcha Code Image
    private function  code_image()
    {
        $actual_path = str_replace('project','',base_path());
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image,0,0,200,50,$background_color);

        $pixel = imagecolorallocate($image, 0,0,255);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixel);
        }

        $font = $actual_path.'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $letter = $allowed_letters[rand(0, $length-1)];
        $word='';
        //$text_color = imagecolorallocate($image, 8, 186, 239);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $cap_length=6;// No. of character in image
        for ($i = 0; $i< $cap_length;$i++)
        {
            $letter = $allowed_letters[rand(0, $length-1)];
            imagettftext($image, 25, 1, 35+($i*25), 35, $text_color, $font, $letter);
            $word.=$letter;
        }
        $pixels = imagecolorallocate($image, 8, 186, 239);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixels);
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path."assets/images/capcha_code.png");
    }

    // alepay
    public function alepay(Request $request){
        $order_id = '';
        try{
            if(!Auth::check()){
                return redirect()->back()->with('unsuccess',"Unauthenticated!");
            }
            $input = $request->all();

            $rules = [
                'txn_id4' => 'required',
            ];
            $messages = [
                'required' => 'The Transaction ID field is required.',
            ];

            $validator = Validator::make($input, $rules, $messages);
            if ($validator->fails()) {
                    Session::flash('unsuccess', $validator->messages()->first());
                    return redirect()->back()->withInput();
            }

            if($request->pass_check) {
                $users = User::where('email','=',$request->personal_email)->get();
                if(count($users) == 0) {
                    if ($request->personal_pass == $request->personal_confirm){
                        $user = new User;
                        $user->name = $request->personal_name;
                        $user->email = $request->personal_email;
                        $user->password = bcrypt($request->personal_pass);
                        $token = md5(time().$request->personal_name.$request->personal_email);
                        $user->verification_link = $token;
                        $user->affilate_code = md5($request->name.$request->email);
                        $user->email_verified = 'Yes';
                        $user->save();
                        Auth::guard('web')->login($user);
                    }else{
                        return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");
                    }
                }
                else {
                    return redirect()->back()->with('unsuccess',"This Email Already Exist.");
                }
            }

            $gs = Generalsetting::findOrFail(1);
            if (!Session::has('cart')) {
                return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
            }
            $oldCart = Session::get('cart');
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
            $cart->totalQty = $cart_items->sum(function ($i) {
                return $i['qty'];
            });

            $tax_amount = $products_amount * $request->tax / 100.0;
            $digital = 1;
            $handling_user = Auth::user();

            //check shopping point
            foreach($cart->items as $cart_item)
            {
                if($cart_item["is_shopping_point_used"] == 1){
                    $maxpoint = intval($cart_item["price_shopping_point"]);
                    $point = $cart_item["shopping_point_used"];
                    if($point > $maxpoint){
                        return redirect()->back()->with('unsuccess','Có sản phẩm ['.$cart_item["item"]["name"].'] sử dụng shopping point không hợp lệ!');
                    }
                    $cart_item["exchange_rate"] = $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_amount"] = $point * $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'] - $cart_item["shopping_point_amount"];
                }
                else{
                    $cart_item["shopping_point_used"] = 0;
                    $cart_item["exchange_rate"] = 0;
                    $cart_item["shopping_point_amount"] = 0;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'];
                }
                $cart_item['product_sub_amount'] = $cart_item['price'] + $cart_item['price_shopping_point_amount'] - $cart_item['shopping_point_amount'];
                $size = $cart_item['size'];
                $color =  $cart_item['color'];
                $values = $cart_item['values'];
                $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
            }

            $cart->totalSPUsed = $cart_items->sum(function ($i) {
                return $i['shopping_point_used'];
            });
            $cart->totalSPAmount = $cart_items->sum(function ($i) {
                return $i['shopping_point_amount'];
            });
            $cart->totalProductSubAmount = $cart_items->sum(function ($i) {
                return $i['product_sub_amount'];
            });

            $sp_amount = $cart->totalSPAmount;

            $totalSPRemain = Auth::user()->shopping_point - $cart->totalSPUsed;
            if($totalSPRemain < 0){
                return redirect()->back()->with('unsuccess','Số dư ví shopping point không đủ!');
            }

            //check all shop's coupon are available
            $used_coupons = array();
            foreach($cart->items as $cart_item)
            {
                if($cart_item["shop_coupon_amount"] > 0){
                    if(!empty($cart_item["shop_coupon_code"])){
                        $code = $cart_item["shop_coupon_code"];
                        $now = Carbon::now()->format('Y-m-d');
                        $coupon = CouponVendor::where('code','=', $code)
                            ->where(function ($query) {
                                $query->where('times', '>', 0)
                                    ->orWhereNull('times');
                            })
                            ->where('status', '=', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now)
                            ->where('vendor_id','=',$cart_item["item"]["user_id"])
                            ->first();
                            if($coupon != null){
                                $product_sub_amount = $cart_item['product_sub_amount'];
                                $cart_item["shop_coupon_value"] = $coupon->price;
                                if($coupon->type == 0){
                                    $cart_item["shop_coupon_amount"] = $product_sub_amount * $coupon->price / 100.0;
                                }
                                else{
                                    $cart_item["shop_coupon_amount"] = $coupon->price;
                                }
                                //subtract coupon times
                                if($coupon->times != null){
                                    $coupon->times = $coupon->times - 1;
                                }
                                $coupon->used = $coupon->used + 1;
                                $cart_item["shop_coupon_times"] = $coupon->times;
                                $cart_item["shop_coupon_used"] = $coupon->used;
                                $coupon->save();
                                array_push($used_coupons, $coupon);
                                $coupon_vendor_log = new CouponVendorUsedLog;
                                $coupon_vendor_log->writeLog($coupon, $handling_user->id, null, null);
                            }
                            else{
                                //roll back used coupon
                                foreach($used_coupons as $u_cp){
                                    $coupon_vendor_log = CouponVendorUsedLog::where('user_id', '=', $handling_user->id)
                                        ->where('coupon_id','=',$u_cp->id)
                                        ->where('used','=',$u_cp->used)
                                        ->first();
                                        ;
                                    $coupon_vendor_log->delete();
                                    if($u_cp->times != null){
                                        $u_cp->times = $u_cp->times + 1;
                                    }
                                    $u_cp->used = $u_cp->used - 1;
                                    $u_cp->save();
                                }
                                return redirect()->back()->with('unsuccess','Số lượng Mã giảm giá đã hết! Vui lòng thử lại!');
                            }
                    }
                    else{
                        $cart_item["shop_coupon_amount"] = 0;
                        $cart_item["shop_coupon_value"] = 0;
                        $cart_item["shop_coupon_code"] = '';
                    }
                    $cart_item['product_final_amount'] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
                    $size = $cart_item['size'];
                    $color =  $cart_item['color'];
                    $values = $cart_item['values'];
                    $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
                }
                if($cart_item['item']->type == 'Physical')
                {
                    $digital = 0;
                }
            }

            $cart->totalProductFinalAmount = $cart->totalProductSubAmount - $cart->totalShopCouponAmount ;
            $vendor_coupon_discount = $cart->totalShopCouponAmount;
            $coupon_discount = $request->coupon_discount;

            if($digital == 1){
                $request->shipping_type = 'negotiate';
            }

            $order_amount1 = $products_amount - $vendor_coupon_discount - $coupon_discount - $sp_amount;
            if($order_amount1 < 0){
                return redirect()->back()->with('unsuccess','Giá trị khuyến mãi cộng Shopping Point vượt quá giá trị đơn hàng! Vui lòng kiểm tra lại');
            }

            if (Session::has('currency'))
            {
                $curr = Currency::find(Session::get('currency'));
            }
            else
            {
                $curr = Currency::where('is_default','=',1)->first();
            }

            foreach($cart->items as $key => $prod)
            {
                if(!empty($prod['item']['license']) && !empty($prod['item']['license_qty']))
                {
                    foreach($prod['item']['license_qty']as $ttl => $dtl)
                    {
                        if($dtl != 0)
                        {
                            $dtl--;
                            $produc = Product::findOrFail($prod['item']['id']);
                            $temp = $produc->license_qty;
                            $temp[$ttl] = $dtl;
                            $final = implode(',', $temp);
                            $produc->license_qty = $final;
                            $produc->update();
                            $temp =  $produc->license;
                            $license = $temp[$ttl];
                                $oldCart = Session::has('cart') ? Session::get('cart') : null;
                                $cart = new Cart($oldCart);
                                $cart->updateLicense($prod['item']['id'],$license);
                                Session::put('cart',$cart);
                            break;
                        }
                    }
                }
            }
            $settings = Generalsetting::findOrFail(1);
            $order = new Order;
            // $success_url = action('Front\PaymentController@payreturn');

            $item_name = $settings->title." Order";
            $item_number = Str::random(10);
            $order_number = $this->generateOrderNumber($request->user_id);
            $order['user_id'] = $request->user_id;
            $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
            $order['totalQty'] = $cart->totalQty;
            $order['pay_amount'] = round($request->total / $curr->value, 0);
            $order['payment_to_company_partner'] = "Alepay";
            $order['method'] = $request->method;
            $order['shipping'] = $request->shipping;
            $order['shipping_type'] = $request->shipping_type;
            $order['pickup_location'] = $request->pickup_location;
            $order['customer_email'] = $request->email;
            $order['customer_name'] = $request->name;
            $order['shipping_cost'] = 0;
            $order['packing_cost'] = $request->packing_cost;
            $order['tax'] = $request->tax;
            $order['customer_phone'] = $request->phone;
            $order['order_number'] = $order_number;
            $order['customer_address'] = $request->address;
            $order['customer_country'] = $request->customer_country;
            $order['customer_city'] = $request->city;
            $order['customer_zip'] = $request->zip;
            $order['shipping_email'] = $request->shipping_email;
            $order['shipping_name'] = $request->shipping_name;
            $order['shipping_phone'] = $request->shipping_phone;
            $order['shipping_address'] = $request->shipping_address;
            $order['shipping_country'] = $request->shipping_country;
            $order['shipping_city'] = $request->shipping_city;
            $order['shipping_zip'] = $request->shipping_zip;
            $order['order_note'] = $request->order_notes;
            $order['txnid'] = $request->txn_id4;
            $order['coupon_code'] = $request->coupon_code;
            $order['coupon_discount'] = $request->coupon_discount;
            $order['dp'] = $request->dp;
            $order['payment_status'] = "Pending";
            $order['currency_sign'] = $curr->sign;
            $order['currency_value'] = $curr->value;
            $order['vendor_shipping_id'] = $request->vendor_shipping_id;
            $order['vendor_packing_id'] = $request->vendor_packing_id;
            $order['customer_province_id'] = $request->customer_province;
            $order['customer_district_id'] = $request->customer_district;
            $order['customer_ward_id'] = $request->customer_ward;
            $order['shipping_province_id'] = $request->shipping_province;
            $order['shipping_district_id'] = $request->shipping_district;
            $order['shipping_ward_id'] = $request->shipping_ward;
            $order['is_shipdiff'] = $request->is_shipdiff == 'true' ? 1 : 0;
            $order['is_online_payment'] = 1;
            $order['payment_bank'] = $request->payment_bank;
            $order['products_amount'] = $products_amount;
            $order['vendor_discount_amount'] = $vendor_coupon_discount;
            $order['is_shopping_point_used'] = $cart->totalSPUsed > 0 ? 1 : 0;
            $order['shopping_point_used'] = $cart->totalSPUsed;
            $order['shopping_point_exchange_rate'] = $gs->sp_vnd_exchange_rate;
            $order['shopping_point_amount'] = $cart->totalSPAmount;
            $order['shopping_point_payment_remain'] = $cart->totalSPPriceRemainAmount;
            $order['total_sp_price'] = $cart->totalSPPrice;
            $order['total_sp_price_amount'] = $cart->totalSPPriceAmount;
            $order['total_sp_price_remain_amount'] = $cart->totalSPPriceRemainAmount;
            $order['total_product_sub_amount'] = $cart->totalProductSubAmount;
            $order['total_product_final_amount'] = $cart->totalProductFinalAmount;
            $order['percent_discount'] = $order->total_product_final_amount > 0 ? $coupon_discount / $order->total_product_final_amount : 0;
            if (Session::has('affilate'))
            {
                $val = $request->total / $curr->value;
                $val = $val / 100;
                $sub = $val * $gs->affilate_charge;
                $order['affilate_user'] = Session::get('affilate');
                $order['affilate_charge'] = $sub;
            }

            $order->save();
            $order_id = $order->id;

            if($order['shopping_point_used'] > 0) {
                $consumer = Auth::user();
                $point_log = new UserPointLog;
                $point_log->user_id = $consumer->id;
                $point_log->log_type = 'Use Shopping';
                $point_log->order_ref_id = $order->id;
                $point_log->reward_point_balance = isset($consumer->reward_point) ? $consumer->reward_point : 0;
                $point_log->shopping_point_balance = isset($consumer->shopping_point) ? $consumer->shopping_point : 0;
                $point_log->exchange_rate = 0;
                $point_log->note = 'Pay for order ['.$order->id.']';
                $point_log->descriptions = 'Bạn đã đổi điểm shopping point cho đơn hàng số ['.$order->order_number.']';
                $point_log->reward_point = 0;
                $point_log->shopping_point = -$order['shopping_point_used'];
                $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                $point_log->amount = $order['pay_amount1'];
                $consumer->shopping_point = $consumer->shopping_point - $cart->totalSPUsed;
                $consumer->save();
                $point_log->save();
            }

            $track = new OrderTrack;
            $track->title = 'Pending';
            $track->text = 'You have successfully placed your order.';
            $track->order_id = $order->id;
            $track->save();
            $notification = new Notification;
            $notification->order_id = $order->id;
            $notification->save();
            if($request->coupon_id != "")
            {
                $coupon = Coupon::findOrFail($request->coupon_id);
                $coupon->used++;
                if($coupon->times != null)
                {
                    $i = (int)$coupon->times;
                    $i--;
                    $coupon->times = (string)$i;
                }
                $coupon->update();
            }

            try{
                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['size_qty'];
                    if(!empty($x))
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $x = (int)$x;
                        $x = $x - $prod['qty'];
                        $temp = $product->size_qty;
                        $temp[$prod['size_key']] = $x;
                        $temp1 = implode(',', $temp);
                        $product->size_qty =  $temp1;
                        $product->update();
                    }
                }

                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['stock'];
                    if($x != null)
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $product->stock =  $prod['stock'];
                        $product->update();
                        if($product->stock <= 5)
                        {
                            $notification = new Notification;
                            $notification->product_id = $product->id;
                            $notification->save();
                        }
                    }
                }
            }
            catch (\Exception $e){
                $msg = $e->getMessage();
            }
            $notf = null;
            $order->shipping_cost = 0;
            if($order['shipping_type'] == 'viettelpost'){
                $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->createorder($order, $cart);
                if($result_viettel_post[0]){
                    $order = $result_viettel_post[1];
                }
                else{
                    //$order['status'] = 'declined';
                    $order['order_note'] = 'Viettel Post Failed!';
                    $order->save();
                    Session::flash('unsuccess', 'Không thể gửi yêu cầu đến đơn vị vận chuyển! Hãy thử phương thức thanh toán khác!');
                    return redirect()->back()->withInput();
                }
            }
            $shipping_cost = $order->shipping_cost;

            $item_count = count($cart->items);
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

            if(!empty($notf))
            {
                $users = array_unique($notf);
                foreach ($users as $user) {
                    $notification = new UserNotification;
                    $notification->user_id = $user;
                    $notification->order_number = $order->order_number;
                    $notification->save();
                }
            }

            $order->pay_amount = $products_amount + $tax_amount + $shipping_cost - $vendor_coupon_discount - $coupon_discount;
            $order->pay_amount1 = $order->pay_amount;
            $order->pay_amount2 = round($order->pay_amount1 - $sp_amount, 0);
            $order->pay_cost = $tax_amount + $shipping_cost;
            $order->pay_discount = $vendor_coupon_discount + $coupon_discount + $sp_amount;
            $order->pay_amount3 = $products_amount - $order->pay_discount;
            $order->pay_amount4 = round($order->pay_amount3 + $order->pay_cost, 0);
            $order->payment_to_company_amount= 0;
            $order->save();
            session()->put('orderNumber',$order_id);
            $alepay = new Alepay;
            $result = $alepay->createAlepay($request,$order);
            if($result->code == '000'){
                if (isset($result) && !empty($result->checkoutUrl)) {
                    Session::put('temporder_id',$order->id);
                    Session::put('tempcart',$cart);
                    Session::forget('cart');
                    Session::forget('already');
                    Session::forget('coupon');
                    Session::forget('coupon_total');
                    Session::forget('coupon_total1');
                    Session::forget('coupon_percentage');
                    return redirect($result->checkoutUrl);
                } else {
                    echo $result->errorDescription;
                }
            }
            Session::flash('unsuccess', '[Alepay] '.$result->message);
            return redirect()->back()->withInput();
        }
        catch (\Exception $e){
            $msg = $e->getMessage();
            return redirect()->back()->with('unsuccess', $msg);
            $note = new DevelopmentNote;
            $note->title = 'CheckOut';
            $note->code = $order_id;
            $note->content = serialize($request->all());
            $note->note = 'Checkout Gate Way bug';
            $note->save();
        }


    }
    
     public function onepay(Request $request){
        $order_id = '';
        try{
            if(!Auth::check()){
                return redirect()->back()->with('unsuccess',"Unauthenticated!");
            }
            $input = $request->all();

            $rules = [
                'txn_id4' => 'required',
            ];
            $messages = [
                'required' => 'The Transaction ID field is required.',
            ];

            $validator = Validator::make($input, $rules, $messages);
            if ($validator->fails()) {
                    Session::flash('unsuccess', $validator->messages()->first());
                    return redirect()->back()->withInput();
            }

            if($request->pass_check) {
                $users = User::where('email','=',$request->personal_email)->get();
                if(count($users) == 0) {
                    if ($request->personal_pass == $request->personal_confirm){
                        $user = new User;
                        $user->name = $request->personal_name;
                        $user->email = $request->personal_email;
                        $user->password = bcrypt($request->personal_pass);
                        $token = md5(time().$request->personal_name.$request->personal_email);
                        $user->verification_link = $token;
                        $user->affilate_code = md5($request->name.$request->email);
                        $user->email_verified = 'Yes';
                        $user->save();
                        Auth::guard('web')->login($user);
                    }else{
                        return redirect()->back()->with('unsuccess',"Confirm Password Doesn't Match.");
                    }
                }
                else {
                    return redirect()->back()->with('unsuccess',"This Email Already Exist.");
                }
            }

            $gs = Generalsetting::findOrFail(1);
            if (!Session::has('cart')) {
                return redirect()->route('front.cart')->with('success',"You don't have any product to checkout.");
            }
            $oldCart = Session::get('cart');
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
            $cart->totalQty = $cart_items->sum(function ($i) {
                return $i['qty'];
            });

            $tax_amount = $products_amount * $request->tax / 100.0;
            $digital = 1;
            $handling_user = Auth::user();

            //check shopping point
            foreach($cart->items as $cart_item)
            {
                if($cart_item["is_shopping_point_used"] == 1){
                    $maxpoint = intval($cart_item["price_shopping_point"]);
                    $point = $cart_item["shopping_point_used"];
                    if($point > $maxpoint){
                        return redirect()->back()->with('unsuccess','Có sản phẩm ['.$cart_item["item"]["name"].'] sử dụng shopping point không hợp lệ!');
                    }
                    $cart_item["exchange_rate"] = $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_amount"] = $point * $gs->sp_vnd_exchange_rate;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'] - $cart_item["shopping_point_amount"];
                }
                else{
                    $cart_item["shopping_point_used"] = 0;
                    $cart_item["exchange_rate"] = 0;
                    $cart_item["shopping_point_amount"] = 0;
                    $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'];
                }
                $cart_item['product_sub_amount'] = $cart_item['price'] + $cart_item['price_shopping_point_amount'] - $cart_item['shopping_point_amount'];
                $size = $cart_item['size'];
                $color =  $cart_item['color'];
                $values = $cart_item['values'];
                $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
            }

            $cart->totalSPUsed = $cart_items->sum(function ($i) {
                return $i['shopping_point_used'];
            });
            $cart->totalSPAmount = $cart_items->sum(function ($i) {
                return $i['shopping_point_amount'];
            });
            $cart->totalProductSubAmount = $cart_items->sum(function ($i) {
                return $i['product_sub_amount'];
            });

            $sp_amount = $cart->totalSPAmount;

            $totalSPRemain = Auth::user()->shopping_point - $cart->totalSPUsed;
            if($totalSPRemain < 0){
                return redirect()->back()->with('unsuccess','Số dư ví shopping point không đủ!');
            }

            //check all shop's coupon are available
            $used_coupons = array();
            foreach($cart->items as $cart_item)
            {
                if($cart_item["shop_coupon_amount"] > 0){
                    if(!empty($cart_item["shop_coupon_code"])){
                        $code = $cart_item["shop_coupon_code"];
                        $now = Carbon::now()->format('Y-m-d');
                        $coupon = CouponVendor::where('code','=', $code)
                            ->where(function ($query) {
                                $query->where('times', '>', 0)
                                    ->orWhereNull('times');
                            })
                            ->where('status', '=', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now)
                            ->where('vendor_id','=',$cart_item["item"]["user_id"])
                            ->first();
                            if($coupon != null){
                                $product_sub_amount = $cart_item['product_sub_amount'];
                                $cart_item["shop_coupon_value"] = $coupon->price;
                                if($coupon->type == 0){
                                    $cart_item["shop_coupon_amount"] = $product_sub_amount * $coupon->price / 100.0;
                                }
                                else{
                                    $cart_item["shop_coupon_amount"] = $coupon->price;
                                }
                                //subtract coupon times
                                if($coupon->times != null){
                                    $coupon->times = $coupon->times - 1;
                                }
                                $coupon->used = $coupon->used + 1;
                                $cart_item["shop_coupon_times"] = $coupon->times;
                                $cart_item["shop_coupon_used"] = $coupon->used;
                                $coupon->save();
                                array_push($used_coupons, $coupon);
                                $coupon_vendor_log = new CouponVendorUsedLog;
                                $coupon_vendor_log->writeLog($coupon, $handling_user->id, null, null);
                            }
                            else{
                                //roll back used coupon
                                foreach($used_coupons as $u_cp){
                                    $coupon_vendor_log = CouponVendorUsedLog::where('user_id', '=', $handling_user->id)
                                        ->where('coupon_id','=',$u_cp->id)
                                        ->where('used','=',$u_cp->used)
                                        ->first();
                                        ;
                                    $coupon_vendor_log->delete();
                                    if($u_cp->times != null){
                                        $u_cp->times = $u_cp->times + 1;
                                    }
                                    $u_cp->used = $u_cp->used - 1;
                                    $u_cp->save();
                                }
                                return redirect()->back()->with('unsuccess','Số lượng Mã giảm giá đã hết! Vui lòng thử lại!');
                            }
                    }
                    else{
                        $cart_item["shop_coupon_amount"] = 0;
                        $cart_item["shop_coupon_value"] = 0;
                        $cart_item["shop_coupon_code"] = '';
                    }
                    $cart_item['product_final_amount'] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
                    $size = $cart_item['size'];
                    $color =  $cart_item['color'];
                    $values = $cart_item['values'];
                    $cart->items[$cart_item['item']->id.$size.$color.str_replace(str_split(' ,'),'',$values)] = $cart_item;
                }
                if($cart_item['item']->type == 'Physical')
                {
                    $digital = 0;
                }
            }

            $cart->totalProductFinalAmount = $cart->totalProductSubAmount - $cart->totalShopCouponAmount ;
            $vendor_coupon_discount = $cart->totalShopCouponAmount;
            $coupon_discount = $request->coupon_discount;

            if($digital == 1){
                $request->shipping_type = 'negotiate';
            }

            $order_amount1 = $products_amount - $vendor_coupon_discount - $coupon_discount - $sp_amount;
            if($order_amount1 < 0){
                return redirect()->back()->with('unsuccess','Giá trị khuyến mãi cộng Shopping Point vượt quá giá trị đơn hàng! Vui lòng kiểm tra lại');
            }

            if (Session::has('currency'))
            {
                $curr = Currency::find(Session::get('currency'));
            }
            else
            {
                $curr = Currency::where('is_default','=',1)->first();
            }

            foreach($cart->items as $key => $prod)
            {
                if(!empty($prod['item']['license']) && !empty($prod['item']['license_qty']))
                {
                    foreach($prod['item']['license_qty']as $ttl => $dtl)
                    {
                        if($dtl != 0)
                        {
                            $dtl--;
                            $produc = Product::findOrFail($prod['item']['id']);
                            $temp = $produc->license_qty;
                            $temp[$ttl] = $dtl;
                            $final = implode(',', $temp);
                            $produc->license_qty = $final;
                            $produc->update();
                            $temp =  $produc->license;
                            $license = $temp[$ttl];
                                $oldCart = Session::has('cart') ? Session::get('cart') : null;
                                $cart = new Cart($oldCart);
                                $cart->updateLicense($prod['item']['id'],$license);
                                Session::put('cart',$cart);
                            break;
                        }
                    }
                }
            }
            $settings = Generalsetting::findOrFail(1);
            $order = new Order;
            // $success_url = action('Front\PaymentController@payreturn');

            $item_name = $settings->title." Order";
            $item_number = Str::random(10);
            $order_number = $this->generateOrderNumber($request->user_id);
            $order['user_id'] = $request->user_id;
            $order['cart'] = utf8_encode(bzcompress(serialize($cart), 9));
            $order['totalQty'] = $cart->totalQty;
            $order['pay_amount'] = round($request->total / $curr->value, 0);
            $order['payment_to_company_partner'] = "Onepay";
            $order['method'] = $request->method;
            $order['shipping'] = $request->shipping;
            $order['shipping_type'] = $request->shipping_type;
            $order['pickup_location'] = $request->pickup_location;
            $order['customer_email'] = $request->email;
            $order['customer_name'] = $request->name;
            $order['shipping_cost'] = 0;
            $order['packing_cost'] = $request->packing_cost;
            $order['tax'] = $request->tax;
            $order['customer_phone'] = $request->phone;
            $order['order_number'] = $order_number;
            $order['customer_address'] = $request->address;
            $order['customer_country'] = $request->customer_country;
            $order['customer_city'] = $request->city;
            $order['customer_zip'] = $request->zip;
            $order['shipping_email'] = $request->shipping_email;
            $order['shipping_name'] = $request->shipping_name;
            $order['shipping_phone'] = $request->shipping_phone;
            $order['shipping_address'] = $request->shipping_address;
            $order['shipping_country'] = $request->shipping_country;
            $order['shipping_city'] = $request->shipping_city;
            $order['shipping_zip'] = $request->shipping_zip;
            $order['order_note'] = $request->order_notes;
            $order['txnid'] = $request->txn_id4;
            $order['coupon_code'] = $request->coupon_code;
            $order['coupon_discount'] = $request->coupon_discount;
            $order['dp'] = $request->dp;
            $order['payment_status'] = "Pending";
            $order['currency_sign'] = $curr->sign;
            $order['currency_value'] = $curr->value;
            $order['vendor_shipping_id'] = $request->vendor_shipping_id;
            $order['vendor_packing_id'] = $request->vendor_packing_id;
            $order['customer_province_id'] = $request->customer_province;
            $order['customer_district_id'] = $request->customer_district;
            $order['customer_ward_id'] = $request->customer_ward;
            $order['shipping_province_id'] = $request->shipping_province;
            $order['shipping_district_id'] = $request->shipping_district;
            $order['shipping_ward_id'] = $request->shipping_ward;
            $order['is_shipdiff'] = $request->is_shipdiff == 'true' ? 1 : 0;
            $order['is_online_payment'] = 1;
            $order['payment_bank'] = $request->payment_bank;
            $order['products_amount'] = $products_amount;
            $order['vendor_discount_amount'] = $vendor_coupon_discount;
            $order['is_shopping_point_used'] = $cart->totalSPUsed > 0 ? 1 : 0;
            $order['shopping_point_used'] = $cart->totalSPUsed;
            $order['shopping_point_exchange_rate'] = $gs->sp_vnd_exchange_rate;
            $order['shopping_point_amount'] = $cart->totalSPAmount;
            $order['shopping_point_payment_remain'] = $cart->totalSPPriceRemainAmount;
            $order['total_sp_price'] = $cart->totalSPPrice;
            $order['total_sp_price_amount'] = $cart->totalSPPriceAmount;
            $order['total_sp_price_remain_amount'] = $cart->totalSPPriceRemainAmount;
            $order['total_product_sub_amount'] = $cart->totalProductSubAmount;
            $order['total_product_final_amount'] = $cart->totalProductFinalAmount;
            $order['percent_discount'] = $order->total_product_final_amount > 0 ? $coupon_discount / $order->total_product_final_amount : 0;
            if (Session::has('affilate'))
            {
                $val = $request->total / $curr->value;
                $val = $val / 100;
                $sub = $val * $gs->affilate_charge;
                $order['affilate_user'] = Session::get('affilate');
                $order['affilate_charge'] = $sub;
            }

            $order->save();
            $order_id = $order->id;

            if($order['shopping_point_used'] > 0) {
                $consumer = Auth::user();
                $point_log = new UserPointLog;
                $point_log->user_id = $consumer->id;
                $point_log->log_type = 'Use Shopping';
                $point_log->order_ref_id = $order->id;
                $point_log->reward_point_balance = isset($consumer->reward_point) ? $consumer->reward_point : 0;
                $point_log->shopping_point_balance = isset($consumer->shopping_point) ? $consumer->shopping_point : 0;
                $point_log->exchange_rate = 0;
                $point_log->note = 'Pay for order ['.$order->id.']';
                $point_log->descriptions = 'Bạn đã đổi điểm shopping point cho đơn hàng số ['.$order->order_number.']';
                $point_log->reward_point = 0;
                $point_log->shopping_point = -$order['shopping_point_used'];
                $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                $point_log->amount = $order['pay_amount1'];
                $consumer->shopping_point = $consumer->shopping_point - $cart->totalSPUsed;
                $consumer->save();
                $point_log->save();
            }

            $track = new OrderTrack;
            $track->title = 'Pending';
            $track->text = 'You have successfully placed your order.';
            $track->order_id = $order->id;
            $track->save();
            $notification = new Notification;
            $notification->order_id = $order->id;
            $notification->save();
            if($request->coupon_id != "")
            {
                $coupon = Coupon::findOrFail($request->coupon_id);
                $coupon->used++;
                if($coupon->times != null)
                {
                    $i = (int)$coupon->times;
                    $i--;
                    $coupon->times = (string)$i;
                }
                $coupon->update();
            }

            try{
                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['size_qty'];
                    if(!empty($x))
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $x = (int)$x;
                        $x = $x - $prod['qty'];
                        $temp = $product->size_qty;
                        $temp[$prod['size_key']] = $x;
                        $temp1 = implode(',', $temp);
                        $product->size_qty =  $temp1;
                        $product->update();
                    }
                }

                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['stock'];
                    if($x != null)
                    {
                        $product = Product::findOrFail($prod['item']['id']);
                        $product->stock =  $prod['stock'];
                        $product->update();
                        if($product->stock <= 5)
                        {
                            $notification = new Notification;
                            $notification->product_id = $product->id;
                            $notification->save();
                        }
                    }
                }
            }
            catch (\Exception $e){
                $msg = $e->getMessage();
            }
            $notf = null;
            $order->shipping_cost = 0;
            if($order['shipping_type'] == 'viettelpost'){
                $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->createorder($order, $cart);
                if($result_viettel_post[0]){
                    $order = $result_viettel_post[1];
                }
                else{
                    //$order['status'] = 'declined';
                    $order['order_note'] = 'Viettel Post Failed!';
                    $order->save();
                    Session::flash('unsuccess', 'Không thể gửi yêu cầu đến đơn vị vận chuyển! Hãy thử phương thức thanh toán khác!');
                    return redirect()->back()->withInput();
                }
            }
            $shipping_cost = $order->shipping_cost;

            $item_count = count($cart->items);
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

            if(!empty($notf))
            {
                $users = array_unique($notf);
                foreach ($users as $user) {
                    $notification = new UserNotification;
                    $notification->user_id = $user;
                    $notification->order_number = $order->order_number;
                    $notification->save();
                }
            }

            $order->pay_amount = $products_amount + $tax_amount + $shipping_cost - $vendor_coupon_discount - $coupon_discount;
            $order->pay_amount1 = $order->pay_amount;
            $order->pay_amount2 = round($order->pay_amount1 - $sp_amount, 0);
            $order->pay_cost = $tax_amount + $shipping_cost;
            $order->pay_discount = $vendor_coupon_discount + $coupon_discount + $sp_amount;
            $order->pay_amount3 = $products_amount - $order->pay_discount;
            $order->pay_amount4 = round($order->pay_amount3 + $order->pay_cost, 0);
            $order->payment_to_company_amount= 0;
            $order->save();
            session()->put('orderNumber',$order_id);
            
            $onepay = new Onepay;
            $ref_url = $onepay->createOnepay($request,$order);
            // if($vnpay_data['code'] == '00'){
                Session::put('temporder_id',$order->id);
                Session::put('tempcart',$cart);
                Session::forget('cart');
                Session::forget('already');
                Session::forget('coupon');
                Session::forget('coupon_total');
                Session::forget('coupon_total1');
                Session::forget('coupon_percentage');
                $onepay_log = new OrderOnepayTrackLog;
                $onepay_log->save_url($order->id, $ref_url);
            return redirect($ref_url);
            // }
            // Session::flash('unsuccess', 'Hệ thống thanh toán online đang gặp sự cố!');
            // return redirect()->back()->withInput();
        }
        catch (\Exception $e){
            $msg = $e->getMessage();
            return redirect()->back()->with('unsuccess', $msg);
            $note = new DevelopmentNote;
            $note->title = 'CheckOut';
            $note->code = $order_id;
            $note->content = serialize($request->all());
            $note->note = 'Checkout Gate Way bug';
            $note->save();
        }
    }


    protected function generateOrderNumber($userId){
        return ModuleCode::getKey(ModuleCode::O).str_pad($userId, 6,'0',STR_PAD_LEFT).date("YmdHms");
    }

}
