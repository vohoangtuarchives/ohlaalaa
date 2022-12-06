<?php

namespace App\Http\Controllers\Front;


use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Currency;
use App\Models\CouponVendor;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{

    public function cart()
    {
        $this->code_image();
        if (!Session::has('cart')) {
            return view('front.cart');
        }
        if (Session::has('already')) {
            Session::forget('already');
        }
        if (Session::has('coupon')) {
            Session::forget('coupon');
        }
        if (Session::has('coupon_total')) {
            Session::forget('coupon_total');
        }
        if (Session::has('coupon_total1')) {
            Session::forget('coupon_total1');
        }
        if (Session::has('coupon_percentage')) {
            Session::forget('coupon_percentage');
        }
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::get('cart');
        //dd($oldCart);
        $cart = new Cart($oldCart);

        // dd($cart);
        $products = $cart->items;
        // dd($products);
        $totalPrice = $cart->totalPrice;
        $mainTotal = $totalPrice;
        $tx = $gs->tax;
        if($tx != 0)
        {
            $tax = ($totalPrice / 100) * $tx;
            $mainTotal = $totalPrice + $tax;
        }
        return view('front.cart', compact('products','totalPrice','mainTotal','tx'));
    }

    public function cartview()
    {
        return view('load.cart');
    }

    public function addtocart($id)
    {
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color',
            'price','stock','type','file','link','license','license_qty','measure','whole_sell_qty','whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);

        // Set Attrubutes

        if (Session::has('language'))
        {
            $data = \DB::table('languages')->find(Session::get('language'));
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $lang = json_decode($data_results);
        }
        else
        {
            $data = \DB::table('languages')->where('is_default','=',1)->first();
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $lang = json_decode($data_results);
        }

        $keys = '';
        $values = '';
        if(!empty($prod->license_qty))
        {
        $lcheck = 1;
            foreach($prod->license_qty as $ttl => $dtl)
            {
                if($dtl < 1)
                {
                    $lcheck = 0;
                }
                else
                {
                    $lcheck = 1;
                    break;
                }
            }
                if($lcheck == 0)
                {
                    return redirect()->route('front.cart')->with('unsuccess',$lang->out_stock);
                }
        }

        // Set Size

        $size = '';
        if(!empty($prod->size))
        {
            $size = trim($prod->size[0]);
        }
        $size = str_replace(' ','-',$size);

        // Set Color

        $color = '';
        if(!empty($prod->color))
        {
            $color = $prod->color[0];
            $color = str_replace('#','',$color);
        }

        if($prod->user_id != 0){
            $gs = Generalsetting::findOrFail(1);
            $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
            $prod->price = round($prc,2);
        }

        // Set Attribute

            if (!empty($prod->attributes))
            {
                $attrArr = json_decode($prod->attributes, true);

                $count = count($attrArr);
                $i = 0;
                $j = 0;
                      if (!empty($attrArr))
                      {
                          foreach ($attrArr as $attrKey => $attrVal)
                          {

                            if (is_array($attrVal) && array_key_exists("details_status",$attrVal) && $attrVal['details_status'] == 1) {
                                if($j == $count - 1){
                                    $keys .= $attrKey;
                                }else{
                                    $keys .= $attrKey.',';
                                }
                                $j++;

                                foreach($attrVal['values'] as $optionKey => $optionVal)
                                {

                                    $values .= $optionVal . ',';

                                    $prod->price += $attrVal['prices'][$optionKey];
                                    break;

                                }
                            }
                          }
                      }
                }
                $keys = rtrim($keys, ',');
                $values = rtrim($values, ',');


        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);

        $cart->add($prod, $prod->id, $size ,$color, $keys, $values);
        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['dp'] == 1)
        {
            return redirect()->route('front.cart')->with('unsuccess',$lang->already_cart);
        }
        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['stock'] < 0)
        {
            return redirect()->route('front.cart')->with('unsuccess',$lang->out_stock);
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
        {
            if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['qty'] > $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
            {
                return redirect()->route('front.cart')->with('unsuccess',$lang->out_stock);
            }
        }

        $cart->totalPrice = 0;
        $cart->totalSPUsed = 0;
        $cart->totalSPAmount = 0;
        $cart->totalSPPrice = 0;
        $cart->totalSPPriceAmount = 0;
        $cart->totalSPPriceRemainAmount = 0;
        $cart->totalProductSubAmount = 0;
        $cart->totalProductFinalAmount = 0;

        foreach($cart->items as $data){
            $cart->totalPrice += $data['price'];
            $cart->totalSPUsed += $data['shopping_point_used'];
            $cart->totalSPAmount += $data['shopping_point_amount'];
            $cart->totalSPPrice += $data['price_shopping_point'];
            $cart->totalSPPriceAmount += $data['price_shopping_point_amount'];
            $cart->totalSPPriceRemainAmount += $data['shopping_point_payment_remain'];
            $cart->totalProductSubAmount += $data['product_sub_amount'];
            $cart->totalProductFinalAmount += $data['product_final_amount'];
        }
        Session::put('cart',$cart);
        return redirect()->route('front.cart');
    }

    public function removeSP($id){
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $gs = Generalsetting::findOrFail(1);
        //dd($cart);
        $product_size_color_id =  $_GET['product_size_color_id'];
        //dd($product_size_color_id);
        $cart_item = $cart->items[$product_size_color_id];
        $cart_item["is_shopping_point_used"] = 0;
        $cart_item["shopping_point_used"] = 0;
        $cart_item["exchange_rate"] = 0;
        $cart_item["shopping_point_amount"] = 0;
        $cart_item["shopping_point_payment_remain"] = 0;
        $cart_item['product_sub_amount'] = $cart_item['price'] + $cart_item['price_shopping_point_amount'] - $cart_item['shopping_point_amount'];
        if($cart_item["shop_coupon_code"] != ''){
            $shop_coupon_data = $this->shop_coupon_data($cart_item["shop_coupon_code"], $cart_item);
            $cart_item["shop_coupon_amount"] = $shop_coupon_data['shop_coupon_amount'];
            $cart_item["shop_coupon_value"] = $shop_coupon_data['shop_coupon_value'];
        }
        else{
            $cart_item["shop_coupon_amount"] = 0;
            $cart_item["shop_coupon_value"] = 0;
            $cart_item["shop_coupon_code"] = '';
        }
        $cart_item["product_final_amount"] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"] < 0 ? 0 : $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
        $cart->items[$product_size_color_id] = $cart_item;
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

        $coupon_discount_data = $this->coupon_data($cart, $gs->tax);

        $province_id = $_GET['province_id'];
        $district_id = $_GET['district_id'];
        $is_online_payment = $_GET['is_online_payment'];
        $viettel_post_fee = 0;
        if($province_id > 0 && $district_id > 0){
            $viettel_post_fee = app('App\Http\Controllers\Front\ViettelPostController')->getfeeValue($province_id, $district_id, $gs->tax, 0, $is_online_payment);
        }
        $data[0] = 1;
        $data[1] = $cart_item;
        $data[2] = $cart->totalSPUsed;
        $data[3] = $cart->totalSPAmount;
        $data[4] = round( Auth::user()->shopping_point - $cart->totalSPUsed,0);
        $data[5] = $cart->totalShopCouponAmount;
        $data[6] = $coupon_discount_data;
        $data[7] = $viettel_post_fee;
        return response()->json($data);
    }

    public function updateSP($id, $point){
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $product_size_color_id =  $_GET['product_size_color_id'];
        $cart_item = $cart->items[$product_size_color_id];
        $cart_item["is_shopping_point_used"] = 1;
        $cart_item["shopping_point_used"] = $point;
        $cart_item["exchange_rate"] = $gs->sp_vnd_exchange_rate;
        $cart_item["shopping_point_amount"] = $point * $gs->sp_vnd_exchange_rate;
        $cart_item["shopping_point_payment_remain"] = $cart_item['price_shopping_point_amount'] - $cart_item["shopping_point_amount"];
        $cart_item['product_sub_amount'] = $cart_item['price'] + $cart_item['price_shopping_point_amount'] - $cart_item['shopping_point_amount'];

        if($cart_item["shop_coupon_code"] != ''){
            $shop_coupon_data = $this->shop_coupon_data($cart_item["shop_coupon_code"], $cart_item);
            $cart_item["shop_coupon_amount"] = $shop_coupon_data['shop_coupon_amount'];
            $cart_item["shop_coupon_value"] = $shop_coupon_data['shop_coupon_value'];
        }
        else{
            $cart_item["shop_coupon_amount"] = 0;
            $cart_item["shop_coupon_value"] = 0;
            $cart_item["shop_coupon_code"] = '';
        }

        $cart_item["product_final_amount"] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"] < 0 ? 0 : $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
        $cart->items[$product_size_color_id] = $cart_item;
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
        $cart->totalSPPriceRemainAmount = $cart_items->sum(function ($i) {
            return $i['shopping_point_payment_remain'];
        });
        $cart->totalProductSubAmount = $cart_items->sum(function ($i) {
            return $i['product_sub_amount'];
        });
        $cart->totalProductFinalAmount = $cart_items->sum(function ($i) {
            return $i['product_final_amount'];
        });

        Session::put('cart', $cart);

        $coupon_discount_data = $this->coupon_data($cart, $gs->tax);
        $province_id = $_GET['province_id'];
        $district_id = $_GET['district_id'];
        $is_online_payment = $_GET['is_online_payment'];
        $viettel_post_fee = 0;
        if($province_id > 0 && $district_id > 0){
            $viettel_post_fee = app('App\Http\Controllers\Front\ViettelPostController')->getfeeValue($province_id, $district_id, $gs->tax, 0, $is_online_payment);
        }

        $data[0] = 1;
        $data[1] = $cart_item;
        $data[2] = $cart->totalSPUsed;
        $data[3] = $cart->totalSPAmount;
        $data[4] = round(Auth::user()->shopping_point - $cart->totalSPUsed,0);
        $data[5] = $cart->totalShopCouponAmount;
        $data[6] = $coupon_discount_data;
        $data[7] = $viettel_post_fee;
        return response()->json($data);
    }

    public function coupon_data($cart, $tax)
    {
        $discount_amount = 0;
        $total_products_amount = $cart->totalPrice + $cart->totalSPPriceAmount;
        $tax_amount = $total_products_amount * $tax / 100.0;
        if(Session::has('coupon_code')){
            $total_cal_discount_amount = $cart->totalProductFinalAmount;
            $code = Session::get('coupon_code');
            $now = Carbon::now()->format('Y-m-d');
            $coupon = Coupon::where('code','=', $code)
                ->where(function ($query) {
                    $query->where('times', '>', 0)
                        ->orWhereNull('times');
                })
                ->where('status', '=', 1)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->first();
            if($coupon != null){
                $result[0] = 1;
                $result["coupon_value"] = $coupon->price;
                if($coupon->type == 0){
                    $discount_amount = round($total_cal_discount_amount * $coupon->price / 100.0, 0);

                }
                else{
                    $discount_amount = $coupon->price;
                }
            }
        }
        $data["coupon_amount"] = $discount_amount;
        $data['total_cost_amount'] = round($total_products_amount + $tax_amount - $cart->totalShopCouponAmount - $cart->totalSPAmount - $discount_amount, 0);
        Session::put('coupon', $discount_amount);
        Session::put('coupon_total1', $data['total_cost_amount']);
        Session::forget('coupon_total');
        return $data;
    }

    public function shop_coupon_data($code, $cart_item){
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
            $data[0] = 1;
            $data["shop_coupon_value"] = $coupon->price;
            $product_sub_amount = $cart_item['product_sub_amount'];
            if($coupon->type == 0){
                $data["shop_coupon_amount"] = $product_sub_amount * $coupon->price / 100.0;
            }
            else{
                $data["shop_coupon_amount"] = $coupon->price;
            }
        }
        else{
            $data["shop_coupon_amount"] = 0;
            $data["shop_coupon_value"] = 0;
            $data["shop_coupon_code"] = '';
            $data[0] = 0;
        }
        return $data;
    }

    public function clear_shop_coupon($id){
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $product_size_color_id =  $_GET['product_size_color_id'];
        $cart_item = $cart->items[$product_size_color_id];
        $cart_item["shop_coupon_code"] = '';
        $cart_item["shop_coupon_value"] = 0;
        $cart_item["shop_coupon_amount"] = 0;
        $cart_item["product_final_amount"] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"] < 0 ? 0 : $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
        $cart->items[$product_size_color_id] = $cart_item;
        $cart_items = collect($cart->items);
        $cart->totalShopCouponAmount = $cart_items->sum(function ($i) {
            return $i['shop_coupon_amount'];
        });
        $cart->totalProductFinalAmount = $cart_items->sum(function ($i) {
            return $i['product_final_amount'];
        });
        Session::put('cart',$cart);

        $coupon_discount_data = $this->coupon_data($cart, $gs->tax);
        $province_id = $_GET['province_id'];
        $district_id = $_GET['district_id'];
        $is_online_payment = $_GET['is_online_payment'];
        $viettel_post_fee = 0;
        if($province_id > 0 && $district_id > 0){
            $viettel_post_fee = app('App\Http\Controllers\Front\ViettelPostController')->getfeeValue($province_id, $district_id, $gs->tax, 0, $is_online_payment);
        }

        $data[0] = 1;
        $data[1] = $cart_item;
        $data[2] = $cart->totalShopCouponAmount;
        $data[6] = $coupon_discount_data;
        $data[7] = $viettel_post_fee;
        return response()->json($data);
    }

    public function apply_shop_coupon($id, $code){
        $gs = Generalsetting::findOrFail(1);
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $product_size_color_id =  $_GET['product_size_color_id'];
        $cart_item = $cart->items[$product_size_color_id];
        $cart_item["shop_coupon_code"] = $code;
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
            $data[0] = 1;
            $cart_item["shop_coupon_value"] = $coupon->price;
            $product_sub_amount = $cart_item['product_sub_amount'];
            if($coupon->type == 0){
                $cart_item["shop_coupon_amount"] = $product_sub_amount * $coupon->price / 100.0;
            }
            else{
                $cart_item["shop_coupon_amount"] = $coupon->price;
            }
        }
        else{
            $cart_item["shop_coupon_amount"] = 0;
            $cart_item["shop_coupon_value"] = 0;
            $cart_item["shop_coupon_code"] = '';
            $data[0] = 0;
        }
        $cart_item["product_final_amount"] = $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"] < 0 ? 0 : $cart_item['product_sub_amount'] - $cart_item["shop_coupon_amount"];
        $cart->items[$product_size_color_id] = $cart_item;
        $cart_items = collect($cart->items);
        $cart->totalShopCouponAmount = $cart_items->sum(function ($i) {
            return $i['shop_coupon_amount'];
        });
        $cart->totalProductFinalAmount = $cart_items->sum(function ($i) {
            return $i['product_final_amount'];
        });

        Session::put('cart',$cart);

        $coupon_discount_data = $this->coupon_data($cart, $gs->tax);
        $province_id = $_GET['province_id'];
        $district_id = $_GET['district_id'];
        $is_online_payment = $_GET['is_online_payment'];
        $viettel_post_fee = 0;
        if($province_id > 0 && $district_id > 0){
            $viettel_post_fee = app('App\Http\Controllers\Front\ViettelPostController')->getfeeValue($province_id, $district_id, $gs->tax, 0, $is_online_payment);
        }

        $data[1] = $cart_item;
        $data[2] = $cart->totalShopCouponAmount;
        $data[6] = $coupon_discount_data;
        $data[7] = $viettel_post_fee;
        return response()->json($data);
    }

    public function addcart($id)
    {
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measure',
            'whole_sell_qty','whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);

        // Set Attrubutes

        $keys = '';
        $values = '';
        if(!empty($prod->license_qty))
        {
            $lcheck = 1;
            foreach($prod->license_qty as $ttl => $dtl)
            {
                if($dtl < 1)
                {
                    $lcheck = 0;
                }
                else
                {
                    $lcheck = 1;
                    break;
                }
            }
            if($lcheck == 0)
            {
                return 0;
            }
        }

        // Set Size

        $size = '';
        if(!empty($prod->size))
        {
            $size = trim($prod->size[0]);
        }
        $size = str_replace(' ','-',$size);



        // Set Color

        $color = '';
        if(!empty($prod->color))
        {
            $color = $prod->color[0];
            $color = str_replace('#','',$color);
        }



        // Vendor Comission

        if($prod->user_id != 0){
            $gs = Generalsetting::findOrFail(1);
            $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission;
            $prod->price = round($prc,2);
        }

        // Set Attribute

        if (!empty($prod->attributes))
        {
            $attrArr = json_decode($prod->attributes, true);
            $count = count($attrArr);
            $i = 0;
            $j = 0;
            if (!empty($attrArr))
            {
                foreach ($attrArr as $attrKey => $attrVal)
                {
                    if (is_array($attrVal) && array_key_exists("details_status",$attrVal) && $attrVal['details_status'] == 1) {
                        if($j == $count - 1){
                            $keys .= $attrKey;
                        }else{
                            $keys .= $attrKey.',';
                        }
                        $j++;

                        foreach($attrVal['values'] as $optionKey => $optionVal)
                        {

                            $values .= $optionVal . ',';

                            $prod->price += $attrVal['prices'][$optionKey];
                            break;


                        }
                    }
                }
            }
        }
        $keys = rtrim($keys, ',');
        $values = rtrim($values, ',');




        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);

        $cart->add($prod, $prod->id,$size,$color,$keys,$values);

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['dp'] == 1)
        {
            return 'digital';
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['stock'] < 0)
        {
            return 0;
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
        {
            if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['qty'] > $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
            {
                return 0;
            }
        }
        $cart->totalPrice = 0;
        $cart->totalSPUsed = 0;
        $cart->totalSPAmount = 0;
        $cart->totalSPPrice = 0;
        $cart->totalSPPriceAmount = 0;
        $cart->totalSPPriceRemainAmount = 0;
        $cart->totalProductSubAmount = 0;
        $cart->totalProductFinalAmount = 0;

        foreach($cart->items as $data){
            $cart->totalPrice += $data['price'];
            $cart->totalSPUsed += $data['shopping_point_used'];
            $cart->totalSPAmount += $data['shopping_point_amount'];
            $cart->totalSPPrice += $data['price_shopping_point'];
            $cart->totalSPPriceAmount += $data['price_shopping_point_amount'];
            $cart->totalSPPriceRemainAmount += $data['shopping_point_payment_remain'];
            $cart->totalProductSubAmount += $data['product_sub_amount'];
            $cart->totalProductFinalAmount += $data['product_final_amount'];
        }

        // dd($cart);

        Session::put('cart',$cart);
        $data1[0] = count($cart->items);
        //dd($data1);
        return response()->json($data1);
    }

    public function addnumcart()
    {
        $id = $_GET['id'];
        $qty = $_GET['qty'];
        $size = str_replace(' ','-',$_GET['size']);
        $color = $_GET['color'];
        $size_qty = $_GET['size_qty'];
        $size_price = (double)$_GET['size_price'];
        $size_key = $_GET['size_key'];
        $keys =  $_GET['keys'];
        $values = $_GET['values'];
        $prices = $_GET['prices'];
        $keys = $keys == "" ? '' :implode(',',$keys);
        $values = $values == "" ? '' : implode(',',$values );
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        }
        else {
            $curr = Currency::where('is_default','=',1)->first();
        }

        $size_price = ($size_price / $curr->value);
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measure',
            'whole_sell_qty','whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);


        if($prod->user_id != 0){
        $gs = Generalsetting::findOrFail(1);
        $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
        $prod->price = round($prc,2);
        }
        if(!empty($prices))
        {
         foreach($prices as $data){
            $prod->price += ($data / $curr->value);
        }

        }

        if(!empty($prod->license_qty))
        {
        $lcheck = 1;
            foreach($prod->license_qty as $ttl => $dtl)
            {
                if($dtl < 1)
                {
                    $lcheck = 0;
                }
                else
                {
                    $lcheck = 1;
                    break;
                }
            }
                if($lcheck == 0)
                {
                    return 0;
                }
        }
        if(empty($size))
        {
            if(!empty($prod->size))
            {
            $size = trim($prod->size[0]);
            }
            $size = str_replace(' ','-',$size);
        }

        if(empty($color))
        {
            if(!empty($prod->color))
            {
            $color = $prod->color[0];

            }
        }
        $color = str_replace('#','',$color);
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);

       // dd($cart);

        $cart->addnum($prod, $prod->id, $qty, $size,$color,$size_qty,$size_price,$size_key,$keys,$values);


        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['dp'] == 1)
        {
            return 'digital';
        }
        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['stock'] < 0)
        {
            return 0;
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
        {
            if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['qty'] > $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
            {
                return 0;
            }
        }

        $cart->totalPrice = 0;
        $cart->totalSPUsed = 0;
        $cart->totalSPAmount = 0;
        $cart->totalSPPrice = 0;
        $cart->totalSPPriceAmount = 0;
        $cart->totalSPPriceRemainAmount = 0;
        $cart->totalProductSubAmount = 0;
        $cart->totalProductFinalAmount = 0;

        foreach($cart->items as $data){
            $cart->totalPrice += $data['price'];
            $cart->totalSPUsed += $data['shopping_point_used'];
            $cart->totalSPAmount += $data['shopping_point_amount'];
            $cart->totalSPPrice += $data['price_shopping_point'];
            $cart->totalSPPriceAmount += $data['price_shopping_point_amount'];
            $cart->totalSPPriceRemainAmount += $data['shopping_point_payment_remain'];
            $cart->totalProductSubAmount += $data['product_sub_amount'];
            $cart->totalProductFinalAmount += $data['product_final_amount'];
        }
         //dd($cart);
        Session::put('cart',$cart);
        $data1[0] = count($cart->items);
        return response()->json($data1);
    }

    public function addtonumcart()
    {
        $id = $_GET['id'];
        $qty = $_GET['qty'];
        $size = str_replace(' ','-',$_GET['size']);
        $color = $_GET['color'];
        $size_qty = $_GET['size_qty'];
        $size_price = (double)$_GET['size_price'];
        $size_key = $_GET['size_key'];
        $keys =  $_GET['keys'];
        $keys = explode(",",$keys);
        $values = $_GET['values'];
        $values = explode(",",$values);
        $prices = $_GET['prices'];
        $prices = explode(",",$prices);
        $keys = $keys == "" ? '' :implode(',',$keys);

        $values = $values == "" ? '' : implode(',',$values );
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        }
        else {
            $curr = Currency::where('is_default','=',1)->first();
        }

        $size_price = ($size_price / $curr->value);
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measure','whole_sell_qty',
            'whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);

        if (Session::has('language'))
        {
            $data = \DB::table('languages')->find(Session::get('language'));
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $lang = json_decode($data_results);

        }
        else
        {
            $data = \DB::table('languages')->where('is_default','=',1)->first();
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $lang = json_decode($data_results);

        }

        if($prod->user_id != 0){
        $gs = Generalsetting::findOrFail(1);
        $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
        $prod->price = round($prc,2);
        }
        if(!empty($prices)){
            if(!empty($prices[0])){
                foreach($prices as $data){
                    $prod->price += ($data / $curr->value);
                }
            }
        }

        if(!empty($prod->license_qty))
        {
        $lcheck = 1;
            foreach($prod->license_qty as $ttl => $dtl)
            {
                if($dtl < 1)
                {
                    $lcheck = 0;
                }
                else
                {
                    $lcheck = 1;
                    break;
                }
            }
                if($lcheck == 0)
                {
                    return redirect()->route('front.cart')->with('unsuccess',$lang->out_stock);
                }
        }
        if(empty($size))
        {
            if(!empty($prod->size))
            {
            $size = trim($prod->size[0]);
            }
            $size = str_replace(' ','-',$size);
        }

        if(empty($color))
        {
            if(!empty($prod->color))
            {
            $color = $prod->color[0];

            }
        }
        $color = str_replace('#','',$color);
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->addnum($prod, $prod->id, $qty, $size,$color,$size_qty,$size_price,$size_key,$keys,$values);
        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['dp'] == 1)
        {
            return redirect()->route('front.cart')->with('unsuccess',$lang->already_cart);
        }
        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['stock'] < 0)
        {
            return redirect()->route('front.cart')->with('unsuccess',$lang->out_stock);
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
        {
            if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['qty'] > $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
            {
                return redirect()->route('front.cart')->with('unsuccess',$lang->out_stock);
            }
        }

        $cart->totalPrice = 0;
        $cart->totalSPUsed = 0;
        $cart->totalSPAmount = 0;
        $cart->totalSPPrice = 0;
        $cart->totalSPPriceAmount = 0;
        $cart->totalSPPriceRemainAmount = 0;
        $cart->totalProductSubAmount = 0;
        $cart->totalProductFinalAmount = 0;

        foreach($cart->items as $data){
            $cart->totalPrice += $data['price'];
            $cart->totalSPUsed += $data['shopping_point_used'];
            $cart->totalSPAmount += $data['shopping_point_amount'];
            $cart->totalSPPrice += $data['price_shopping_point'];
            $cart->totalSPPriceAmount += $data['price_shopping_point_amount'];
            $cart->totalSPPriceRemainAmount += $data['shopping_point_payment_remain'];
            $cart->totalProductSubAmount += $data['product_sub_amount'];
            $cart->totalProductFinalAmount += $data['product_final_amount'];
        }

        Session::put('cart',$cart);
        return redirect()->route('front.cart');
    }

    public function addbyone()
    {
        if (Session::has('coupon')) {
            Session::forget('coupon');
        }
        $gs = Generalsetting::findOrFail(1);
        if (Session::has('currency'))
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        $id = $_GET['id'];
        $itemid = $_GET['itemid'];
        $size_qty = $_GET['size_qty'];
        $size_price = $_GET['size_price'];
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measure','whole_sell_qty','whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);

        if($prod->user_id != 0){
        $gs = Generalsetting::findOrFail(1);
        $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
        $prod->price = round($prc,2);
        }

            if (!empty($prod->attributes))
            {
                $attrArr = json_decode($prod->attributes, true);
                $count = count($attrArr);
                $j = 0;
                      if (!empty($attrArr))
                      {
                          foreach ($attrArr as $attrKey => $attrVal)
                          {

                            if (is_array($attrVal) && array_key_exists("details_status",$attrVal) && $attrVal['details_status'] == 1) {

                                foreach($attrVal['values'] as $optionKey => $optionVal)
                                {
                                    $prod->price += $attrVal['prices'][$optionKey];
                                    break;
                                }
                            }
                          }

                      }
                }

        if(!empty($prod->license_qty))
        {
        $lcheck = 1;
            foreach($prod->license_qty as $ttl => $dtl)
            {
                if($dtl < 1)
                {
                    $lcheck = 0;
                }
                else
                {
                    $lcheck = 1;
                    break;
                }
            }
                if($lcheck == 0)
                {
                    return 0;
                }
        }
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->adding($prod, $itemid,$size_qty,$size_price);
        if($cart->items[$itemid]['stock'] < 0)
        {
            return 0;
        }
        if(!empty($size_qty))
        {
            if($cart->items[$itemid]['qty'] > $cart->items[$itemid]['size_qty'])
            {
                return 0;
            }
        }
        $cart->totalPrice = 0;
        $cart->totalSPUsed = 0;
        $cart->totalSPAmount = 0;
        $cart->totalSPPrice = 0;
        $cart->totalSPPriceAmount = 0;
        $cart->totalSPPriceRemainAmount = 0;
        $cart->totalProductSubAmount = 0;
        $cart->totalProductFinalAmount = 0;

        foreach($cart->items as $data){
            $cart->totalPrice += $data['price'];
            $cart->totalSPUsed += $data['shopping_point_used'];
            $cart->totalSPAmount += $data['shopping_point_amount'];
            $cart->totalSPPrice += $data['price_shopping_point'];
            $cart->totalSPPriceAmount += $data['price_shopping_point_amount'];
            $cart->totalSPPriceRemainAmount += $data['shopping_point_payment_remain'];
            $cart->totalProductSubAmount += $data['product_sub_amount'];
            $cart->totalProductFinalAmount += $data['product_final_amount'];
        }
        Session::put('cart',$cart);
        $data[0] = $cart->totalPrice;

        $data[3] = $data[0];
        $tx = $gs->tax;
        if($tx != 0)
        {
            $tax = ($data[0] / 100) * $tx;
            $data[3] = $data[0] + $tax;
        }

        $data[1] = $cart->items[$itemid]['qty'];
        $data[2] = $cart->items[$itemid]['price'];
        $data[4] = $cart->items[$itemid]['item_price'];
        $data[0] = round($data[0] * $curr->value,2);
        $data[2] = round($data[2] * $curr->value,2);
        $data[3] = round($data[3] * $curr->value,2);
        $data[4] = round($data[4] * $curr->value,2);
        if($gs->currency_format == 0){
            $data[0] = $curr->sign.$data[0];
            $data[2] = $curr->sign.$data[2];
            $data[3] = $curr->sign.$data[3];
            $data[4] = $curr->sign.$data[4];
        }
        else{
            $data[0] = $data[0].$curr->sign;
            $data[2] = $data[2].$curr->sign;
            $data[3] = $data[3].$curr->sign;
            $data[4] = $data[4].$curr->sign;
        }
        return response()->json($data);
    }

    public function reducebyone()
    {
        if (Session::has('coupon')) {
            Session::forget('coupon');
        }
        $gs = Generalsetting::findOrFail(1);
        if (Session::has('currency'))
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        $id = $_GET['id'];
        $itemid = $_GET['itemid'];
        $size_qty = $_GET['size_qty'];
        $size_price = $_GET['size_price'];
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measure','whole_sell_qty','whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);
        if($prod->user_id != 0){
        $gs = Generalsetting::findOrFail(1);
        $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
        $prod->price = round($prc,2);
        }

            if (!empty($prod->attributes))
            {
                $attrArr = json_decode($prod->attributes, true);
                $count = count($attrArr);
                $j = 0;
                      if (!empty($attrArr))
                      {
                          foreach ($attrArr as $attrKey => $attrVal)
                          {
                            if (is_array($attrVal) && array_key_exists("details_status",$attrVal) && $attrVal['details_status'] == 1) {

                                foreach($attrVal['values'] as $optionKey => $optionVal)
                                {
                                    $prod->price += $attrVal['prices'][$optionKey];
                                    break;
                                }

                            }
                          }

                      }
                }

        if(!empty($prod->license_qty))
        {
        $lcheck = 1;
            foreach($prod->license_qty as $ttl => $dtl)
            {
                if($dtl < 1)
                {
                    $lcheck = 0;
                }
                else
                {
                    $lcheck = 1;
                    break;
                }
            }
            if($lcheck == 0)
            {
                return 0;
            }
        }
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->reducing($prod, $itemid,$size_qty,$size_price);
        $cart->totalPrice = 0;
        $cart->totalSPUsed = 0;
        $cart->totalSPAmount = 0;
        $cart->totalSPPrice = 0;
        $cart->totalSPPriceAmount = 0;
        $cart->totalSPPriceRemainAmount = 0;
        $cart->totalProductSubAmount = 0;
        $cart->totalProductFinalAmount = 0;

        foreach($cart->items as $data){
            $cart->totalPrice += $data['price'];
            $cart->totalSPUsed += $data['shopping_point_used'];
            $cart->totalSPAmount += $data['shopping_point_amount'];
            $cart->totalSPPrice += $data['price_shopping_point'];
            $cart->totalSPPriceAmount += $data['price_shopping_point_amount'];
            $cart->totalSPPriceRemainAmount += $data['shopping_point_payment_remain'];
            $cart->totalProductSubAmount += $data['product_sub_amount'];
            $cart->totalProductFinalAmount += $data['product_final_amount'];
        }

        Session::put('cart',$cart);
        $data[0] = $cart->totalPrice;

        $data[3] = $data[0];
        $tx = $gs->tax;
        if($tx != 0)
        {
            $tax = ($data[0] / 100) * $tx;
            $data[3] = $data[0] + $tax;
        }

        $data[1] = $cart->items[$itemid]['qty'];
        $data[2] = $cart->items[$itemid]['price'];
        $data[4] = $cart->items[$itemid]['item_price'];
        $data[0] = round($data[0] * $curr->value,2);
        $data[2] = round($data[2] * $curr->value,2);
        $data[3] = round($data[3] * $curr->value,2);
        $data[4] = round($data[4] * $curr->value,2);
        if($gs->currency_format == 0){
            $data[0] = $curr->sign.$data[0];
            $data[2] = $curr->sign.$data[2];
            $data[3] = $curr->sign.$data[3];
            $data[4] = $curr->sign.$data[4];
        }
        else{
            $data[0] = $data[0].$curr->sign;
            $data[2] = $data[2].$curr->sign;
            $data[3] = $data[3].$curr->sign;
            $data[4] = $data[4].$curr->sign;
        }
        return response()->json($data);
    }

    public function upcolor()
    {
         $id = $_GET['id'];
         $color = $_GET['color'];
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measure','whole_sell_qty','whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);
         $oldCart = Session::has('cart') ? Session::get('cart') : null;
         $cart = new Cart($oldCart);
         $cart->updateColor($prod,$id,$color);
         Session::put('cart',$cart);
    }


    public function removecart($id)
    {
        $gs = Generalsetting::findOrFail(1);
        if (Session::has('currency'))
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
                $data[0] = $cart->totalPrice;
                $data[3] = $data[0];
                    $tx = $gs->tax;
                    if($tx != 0)
                    {
                        $tax = ($data[0] / 100) * $tx;
                        $data[3] = $data[0] + $tax;
                    }

                if($gs->currency_format == 0){
                    $data[0] = $curr->sign.round($data[0] * $curr->value,2);
                    $data[3] = $curr->sign.round($data[3] * $curr->value,2);

                }
                else{
                    $data[0] = round($data[0] * $curr->value,2).$curr->sign;
                    $data[3] = round($data[3] * $curr->value,2).$curr->sign;
                }

            $data[1] = count($cart->items);
            return response()->json($data);
        } else {
            Session::forget('cart');
            Session::forget('already');
            Session::forget('coupon');
            Session::forget('coupon_total');
            Session::forget('coupon_total1');
            Session::forget('coupon_percentage');

            $data = 0;
            return response()->json($data);
        }
    }

    public function coupon()
    {
        $gs = Generalsetting::findOrFail(1);
        $code = $_GET['code'];
        $total = (float)preg_replace('/[^0-9\.]/ui','',$_GET['total']);;
        $fnd = Coupon::where('code','=',$code)->get()->count();
        if($fnd < 1){
        return response()->json(0);
        }
        else{
        $coupon = Coupon::where('code','=',$code)->first();
            if (Session::has('currency')) {
              $curr = Currency::find(Session::get('currency'));
            }
            else{
                $curr = Currency::where('is_default','=',1)->first();
            }
            if($coupon->times != null)
            {
                if($coupon->times == "0")
                {
                    return response()->json(0);
                }
            }
        $today = date('Y-m-d');
        $from = date('Y-m-d',strtotime($coupon->start_date));
        $to = date('Y-m-d',strtotime($coupon->end_date));
        if($from <= $today && $to >= $today)
        {
            if($coupon->status == 1)
            {
                $oldCart = Session::has('cart') ? Session::get('cart') : null;
                $val = Session::has('already') ? Session::get('already') : null;
                if($val == $code)
                {
                    return response()->json(2);
                }
                $cart = new Cart($oldCart);
                if($coupon->type == 0)
                {
                    Session::put('already', $code);
                    $coupon->price = (int)$coupon->price;
                    $val = $total / 100;
                    $sub = $val * $coupon->price;
                    $total = $total - $sub;
                    $data[0] = round($total,2);
                    if($gs->currency_format == 0){
                        $data[0] = $curr->sign.$data[0];
                    }
                    else{
                        $data[0] = $data[0].$curr->sign;
                    }
                    $data[1] = $code;
                    $data[2] = round($sub, 2);
                    Session::put('coupon', $data[2]);
                    Session::put('coupon_code', $code);
                    Session::put('coupon_id', $coupon->id);
                    Session::put('coupon_total', $data[0]);
                    $data[3] = $coupon->id;
                    $data[4] = $coupon->price."%";
                    $data[5] = 1;

                    Session::put('coupon_percentage', $data[4]);

                    return response()->json($data);
                }
                else{
                    Session::put('already', $code);
                    $total = $total - round($coupon->price * $curr->value, 2);
                    $data[0] = round($total,2);
                    $data[1] = $code;
                    $data[2] = round($coupon->price * $curr->value, 2);
                    Session::put('coupon', $data[2]);
                    Session::put('coupon_code', $code);
                    Session::put('coupon_id', $coupon->id);
                    Session::put('coupon_total', $data[0]);
                    $data[3] = $coupon->id;
                if($gs->currency_format == 0){
                    $data[4] = $curr->sign.$data[2];
                    $data[0] = $curr->sign.$data[0];
                }
                else{
                    $data[4] = $data[2].$curr->sign;
                    $data[0] = $data[0].$curr->sign;
                }

                    Session::put('coupon_percentage', 0);

                    $data[5] = 1;
                    return response()->json($data);
                }
            }
            else{
                return response()->json(0);
            }
        }
        else{
        return response()->json(0);
        }
        }
    }

    public function couponclear()
    {
        $gs = Generalsetting::findOrFail(1);
        $province_id = $_GET['province_id'];
        $district_id = $_GET['district_id'];
        $is_online_payment = $_GET['is_online_payment'];
        $viettel_post_fee = 0;
        $code = '';
        $tax = $_GET['tax'];
        Session::put('already', $code);
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        $total_products_amount = $cart->totalPrice + $cart->totalSPPriceAmount;
        $tax_amount = $total_products_amount * $tax / 100.0;
        $discount_amount = 0;
        $total_cost_amount = round($total_products_amount + $tax_amount - $cart->totalShopCouponAmount - $cart->totalSPAmount - $discount_amount, 0);
        $data[0] = $total_cost_amount;
        $data[1] = $code;
        $data[2] = $discount_amount;
        if (Session::has('currency'))
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        if($gs->currency_format == 0){
            $data[0] = $curr->sign.$data[0];
        }
        else{
            $data[0] = $data[0].$curr->sign;
        }
        Session::put('coupon', $data[2]);
        Session::put('coupon_code', $code);
        Session::put('coupon_id', '');
        Session::put('coupon_total1', $data[0]);
        Session::forget('coupon_total');
        if($province_id > 0 && $district_id > 0){
            $viettel_post_fee = app('App\Http\Controllers\Front\ViettelPostController')->getfeeValue($province_id, $district_id, $gs->tax, 0, $is_online_payment);
        }

        $data[7] = $viettel_post_fee;
        $data[0] = $total_cost_amount;
        $data[1] = $code;
        $data[2] = $discount_amount;
        $data[3] = '';
        $data[4] = 0;
        $data[5] = 1;
        Session::put('coupon_percentage', $data[4]);
        return response()->json($data);
    }

    public function couponcheck()
    {
        $gs = Generalsetting::findOrFail(1);
        $province_id = $_GET['province_id'];
        $district_id = $_GET['district_id'];
        $is_online_payment = $_GET['is_online_payment'];
        $viettel_post_fee = 0;
        $code = $_GET['code'];
        $tax = $_GET['tax'];
        $total = (float)preg_replace('/[^0-9\.]/ui','',$_GET['total']);
        $tax_value =$total * $tax / 100.0;
        $fnd = Coupon::where('code','=',$code)->get()->count();
        if($fnd < 1)
        {
            //no_coupon
            return response()->json(0);
        }
        else{
            $coupon = Coupon::where('code','=',$code)->first();
            if (Session::has('currency'))
            {
                $curr = Currency::find(Session::get('currency'));
            }
            else
            {
                $curr = Currency::where('is_default','=',1)->first();
            }
            if($coupon->times != null)
            {
                if($coupon->times == "0")
                {
                    return response()->json(0);
                }
            }
            $today = date('Y-m-d');
            $from = date('Y-m-d',strtotime($coupon->start_date));
            $to = date('Y-m-d',strtotime($coupon->end_date));
            if($from <= $today && $to >= $today)
            {
                if($coupon->status == 1)
                {
                    $oldCart = Session::has('cart') ? Session::get('cart') : null;
                    $val = Session::has('already') ? Session::get('already') : null;
                    $cart = new Cart($oldCart);
                    $total_products_amount = $cart->totalPrice + $cart->totalSPPriceAmount;
                    $tax_amount = $total_products_amount * $tax / 100.0;
                    $total_cal_discount_amount = $cart->totalProductFinalAmount;
                    if($coupon->type == 0)
                    {
                        Session::put('already', $code);
                        $coupon_percent = $coupon->price / 100.0;
                        $discount_amount = round($total_cal_discount_amount * $coupon_percent, 0);
                        $total_cost_amount = round($total_products_amount + $tax_amount - $cart->totalShopCouponAmount - $cart->totalSPAmount - $discount_amount, 0);
                        $data[0] = $total_cost_amount;
                        $data[1] = $code;
                        $data[2] = $discount_amount;
                        $data[0] = $curr->sign.$data[0];
                        Session::put('coupon', $data[2]);
                        Session::put('coupon_code', $code);
                        Session::put('coupon_id', $coupon->id);
                        Session::put('coupon_total1', $data[0]);
                        Session::forget('coupon_total');

                        if($province_id > 0 && $district_id > 0){
                            $viettel_post_fee = app('App\Http\Controllers\Front\ViettelPostController')->getfeeValue($province_id, $district_id, $gs->tax, 0, $is_online_payment);
                        }

                        $data[7] = $viettel_post_fee;
                        $data[0] = $total_cost_amount;
                        $data[1] = $code;
                        $data[2] = $discount_amount;
                        $data[3] = $coupon->id;
                        $data[4] = $coupon->price."%";
                        $data[5] = 1;
                        Session::put('coupon_percentage', $data[4]);
                        return response()->json($data);
                    }
                    else{
                        Session::put('already', $code);
                        $discount_amount = round($coupon->price);
                        $total_cost_amount = round($total_products_amount + $tax_amount - $cart->totalShopCouponAmount - $cart->totalSPAmount - $discount_amount, 0);
                        $data[0] = $total_cost_amount;
                        $data[1] = $code;
                        $data[2] = $discount_amount;
                        $data[3] = $coupon->id;
                        if($gs->currency_format == 0){
                            $data[4] = 0;
                            $data[0] = $curr->sign.$data[0];
                        }
                        else{
                            $data[4] = 0;
                            $data[0] = $data[0].$curr->sign;
                        }
                        Session::put('coupon', $data[2]);
                        Session::put('coupon_code', $code);
                        Session::put('coupon_id', $coupon->id);
                        Session::put('coupon_total1', $data[0]);
                        Session::forget('coupon_total');
                        if($province_id > 0 && $district_id > 0){
                            $viettel_post_fee = app('App\Http\Controllers\Front\ViettelPostController')->getfeeValue($province_id, $district_id, $gs->tax, 0, $is_online_payment);
                        }

                        $data[7] = $viettel_post_fee;
                        $data[0] = $total_cost_amount;
                        $data[1] = $code;
                        $data[2] = $discount_amount;
                        $data[3] = $coupon->id;
                        $data[5] = 1;
                        Session::put('coupon_percentage', $data[4]);
                        return response()->json($data);
                    }
                }
                else{
                    return response()->json(0);
                }
            }
            else{
                return response()->json(0);
            }
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

    public function addcart_import($id, $vorder)
    {
        $prod = Product::where('id','=',$id)->first(['id','user_id','slug','name','photo','size','size_qty','size_price','color','price','stock','type','file','link','license','license_qty','measure',
            'whole_sell_qty','whole_sell_discount','attributes',
            'weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            ]);

        // Set Attrubutes

        if(!$prod){
            $data[0] = 0;
            return response()->json($data);
        }

        $keys = '';
        $values = '';
        if(!empty($prod->license_qty))
        {
            $lcheck = 1;
            foreach($prod->license_qty as $ttl => $dtl)
            {
                if($dtl < 1)
                {
                    $lcheck = 0;
                }
                else
                {
                    $lcheck = 1;
                    break;
                }
            }
            if($lcheck == 0)
            {
                return 0;
            }
        }

        // Set Size

        $size = '';
        if(!empty($prod->size))
        {
            $size = trim($prod->size[0]);
        }
        $size = str_replace(' ','-',$size);

        // Set Color

        $color = '';
        if(!empty($prod->color))
        {
            $color = $prod->color[0];
            $color = str_replace('#','',$color);
        }

        // Vendor Comission

        if($prod->user_id != 0){
            $gs = Generalsetting::findOrFail(1);
            $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission;
            $prod->price = round($prc,2);
        }

        // Set Attribute

        if (!empty($prod->attributes))
        {
            $attrArr = json_decode($prod->attributes, true);
            $count = count($attrArr);
            $i = 0;
            $j = 0;
            if (!empty($attrArr))
            {
                foreach ($attrArr as $attrKey => $attrVal)
                {
                    if (is_array($attrVal) && array_key_exists("details_status",$attrVal) && $attrVal['details_status'] == 1) {
                        if($j == $count - 1){
                            $keys .= $attrKey;
                        }else{
                            $keys .= $attrKey.',';
                        }
                        $j++;

                        foreach($attrVal['values'] as $optionKey => $optionVal)
                        {

                            $values .= $optionVal . ',';

                            $prod->price += $attrVal['prices'][$optionKey];
                            break;


                        }
                    }
                }
            }
        }
        $keys = rtrim($keys, ',');
        $values = rtrim($values, ',');


        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);

        $cart->add($prod, $prod->id,$size,$color,$keys,$values);
        $cart->totalQty--;

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['dp'] == 1)
        {
            return 'digital';
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['stock'] < 0)
        {
            return 0;
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
        {
            if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['qty'] > $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
            {
                return 0;
            }
        }
        // $cart->totalPrice = 0;
        // $cart->totalSPUsed = 0;
        // $cart->totalSPAmount = 0;
        // $cart->totalSPPrice = 0;
        // $cart->totalSPPriceAmount = 0;
        // $cart->totalSPPriceRemainAmount = 0;
        // $cart->totalProductSubAmount = 0;
        // $cart->totalProductFinalAmount = 0;
        $data = $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)];
        $data['qty'] = $vorder->qty;
        $data['price'] = $vorder->price;
        $data['price_shopping_point'] = 0;
        $data['price_shopping_point_amount'] = 0;
        $data['percent_shopping_point'] = 0;
        $data['item_price'] = $vorder->unit_price;
        $data['item_price_shopping_point'] = 0;
        $data['is_shopping_point_used'] = $vorder->is_shopping_point_used;
        $data['shopping_point_used'] = $vorder->shopping_point_used;
        $data['shopping_point_amount'] = $vorder->shopping_point_amount;
        $data['shopping_point_payment_remain'] = $vorder->shopping_point_payment_remain;
        $data['exchange_rate'] = $vorder->exchange_rate;
        $data['is_rebate_paid'] = $vorder->is_rebate_paid;
        $data['rebate_bonus'] = $vorder->rebate_bonus;
        $data['rebate_amount'] = $vorder->rebate_amount;
        $data['rebate_in'] = $vorder->rebate_in;
        $data['shop_coupon_code'] = $vorder->shop_coupon_code;
        $data['shop_coupon_amount'] = $vorder->shop_coupon_amount;
        $data['shop_coupon_value'] = $vorder->shop_coupon_value;
        $data['shop_coupon_times'] = $vorder->shop_coupon_times;
        $data['shop_coupon_used'] = $vorder->shop_coupon_used;
        $data['product_sub_amount'] = $vorder->product_sub_amount;
        $data['product_final_amount'] = $vorder->product_final_amount;
        //dd($data);
        $cart->totalQty += $data['qty'];
        $cart->items[$data['item']->id] = $data;
        $cart->totalPrice += $data['price'];
        $cart->totalSPUsed += $data['shopping_point_used'];
        $cart->totalSPAmount += $data['shopping_point_amount'];
        $cart->totalSPPrice += $data['price_shopping_point'];
        $cart->totalSPPriceAmount += $data['price_shopping_point_amount'];
        $cart->totalSPPriceRemainAmount += $data['shopping_point_payment_remain'];
        $cart->totalProductSubAmount += $data['product_sub_amount'];
        $cart->totalProductFinalAmount += $data['product_final_amount'];
        // if($data['item']['id'] != 103790)
        //     dd($data);
        Session::put('cart',$cart);
        $data[0] = count($cart->items);
        return response()->json($data);
    }

}
