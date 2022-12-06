<?php

namespace App\Http\Controllers\Front;

use Auth;
use App\Models\Cart;
use App\Models\User;
use App\Models\Ward;
use App\Models\Order;
use App\Models\District;
use App\Models\Province;
use App\Enums\ModuleCode;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Classes\CometChatHTD;
use App\Models\PackageConfig;
use App\Classes\HTTPRequester;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\MemberPackageRegister;
use App\Models\UserPointLog;
use Illuminate\Support\Facades\Session;

class ViettelPostController extends Controller
{
    public function index(){

        $count = MemberPackageRegister::whereNotNull('approval_at')
        ->whereDate('approval_at', '<', '2021-12-09 08:27:39')
        ->where('user_id', '=', 69245 )
        ->get()
        //->count()
        ;


        $test = DB::table('user_point_logs')->where('user_id','=', 69245)->limit(10);
        dd($test->paginate(9));

        $sql = "select * from user_point_logs where user_id = ?";

        $data = (new Collection(DB::select($sql, [69245])))->limit(10)->paginate(9);



        $data = collect(DB::raw($sql, [69245]));

        // dd($data->get());




        $data = collect(DB::select($sql, [69245]));


        dd($data->limit(10));


        return view('front.vnpay-test-page');
    }

    public function getfeeValue($to_province, $to_district, $tax = 0, $discount = 0, $is_online_payment = 0){
        $discount = Session::has('coupon') ? Session::get('coupon') : 0;
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        $user = null;
        $digital = 1;
        foreach ($cart->items as $prod) {
            if($prod['item']->type == 'Physical')
            {
                $user[] = $prod['item']['user_id'];
                $digital = 0;
            }
        }
        //digital item no need to delivery
        if($digital == 1 || $user == null){
            return 0;
        }

        $users = array_unique($user);
        $city = null;
        $district = null;
        $items = null;
        $responses = null;
        $rqs = null;
        $weight = 0;
        $product_price = null;
        $url =  config('app.viettel_post.price_url');
        $result = collect();
        $to_province_for_viettelpost = Province::find($to_province);
        $to_district_for_viettelpost = District::find($to_district);

        $total_amount = collect($cart->items)->sum(function ($product) {
            return $product['product_final_amount'];
        });
        $discount_percent = $total_amount > 0 ? $discount / $total_amount : 0;

        $token = $this->gettoken();
        if (!$token) return 0;

        foreach($users as $userid){
            $user = User::find($userid);
            $city = Province::find($user->CityID);
            $district = District::find($user->DistrictID);
            $items = collect($cart->items)->filter(function($item) use($userid) {
                return $item['item']->user_id === $userid && $item['item']->type == 'Physical' ;
            });
            $amount = $items->sum(function ($product) {
                return $product['qty'] * $product['item_price'];
            });
            $total_price_sp = $items->sum(function ($product) {
                return $product['qty'] * $product['item_price_shopping_point'];
            });

            $product_final_amount = $items->sum(function ($product) {
                return $product['product_final_amount'];
            });

            $product_price = $product_final_amount + (($amount + $total_price_sp) * $tax / 100.0) - ($product_final_amount * $discount_percent);
            $product_price = $product_price > 0 ? $product_price : 0;
            $weight = $items->sum(function ($product) {
                return $product['qty'] * $product['item']->weight;
            });
            $weight = $weight <= 0 ? 1000 : $weight;
            $service = $this->get_online_service($city->viettelpost_id, $district->viettelpost_id,$to_province_for_viettelpost->viettelpost_id,$to_district_for_viettelpost->viettelpost_id,
                $weight, $product_price, $is_online_payment ? 0 : $product_price, $token);
            if($service == null){
                return 0;
            }

            $response = null;
            $fee['shop_id'] = $userid;
            $fee['shipping_cost'] = 0;
            $fee['srv'] = '';
            $fee['fromcity'] = 0;
            $fee['tocity'] = 0;
            $fee['data'] = null;
            $fee['rq'] = null;
            $fee['is_online_payment'] = $is_online_payment;

            $requestArr = array(
                "PRODUCT_WEIGHT" => $weight,
                "PRODUCT_PRICE" => $product_price,
                "MONEY_COLLECTION" => $is_online_payment ? 0 : $product_price,
                "ORDER_SERVICE" => $service,
                "ORDER_SERVICE_ADD" => "",
                "SENDER_PROVINCE" => $city->viettelpost_id,
                "SENDER_DISTRICT" => $district->viettelpost_id,
                "RECEIVER_PROVINCE" => $to_province_for_viettelpost->viettelpost_id,
                "RECEIVER_DISTRICT" => $to_district_for_viettelpost->viettelpost_id,
                "PRODUCT_TYPE" => "HH",
                "NATIONAL_TYPE" => "1",
            );

            $rqs [] = $requestArr;
            $response = HTTPRequester::HTTPPostBody($url, $requestArr);
            $responses []=$response;

            if(!$response){
                return 0;
            }
            else{
                if(!$response['error']){
                    $fee['shipping_cost'] = $response["data"]["MONEY_TOTAL"];
                    $fee['srv'] = $service;
                    $fee['fromcity'] = $city->id;
                    $fee['tocity'] = $to_province_for_viettelpost->id;
                    $fee['data'] = $response["data"];
                    $fee['rq'] = $requestArr;
                }
            }

            if($fee['shipping_cost'] == 0)
                return 0;
            $result->push($fee);
        }
        if(Session::has('temp_shipping_info')){
            Session::forget('temp_shipping_info');
        }
        Session::put('temp_shipping_info',$result);
        return $result->sum('shipping_cost');
    }

    public function getfeeValue1($to_province, $to_district, $tax = 0, $discount = 0, $is_online_payment = 0){
        $discount = Session::has('coupon') ? Session::get('coupon') : 0;
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);
        $user = null;
        $digital = 1;
        foreach ($cart->items as $prod) {
            if($prod['item']->type == 'Physical')
            {
                $user[] = $prod['item']['user_id'];
                $digital = 0;
            }
        }
        //digital item no need to delivery
        if($digital == 1){
            return 0;
        }

        $users = array_unique($user);
        $city = null;
        $district = null;
        $items = null;
        $responses = null;
        $rqs = null;
        $weight = 0;
        $product_price = null;
        $url =  config('app.viettel_post.price_url');
        $result = collect();
        $to_province_for_viettelpost = Province::find($to_province);
        $to_district_for_viettelpost = District::find($to_district);

        $total_amount = collect($cart->items)->sum(function ($product) {
            return $product['product_final_amount'];
        });
        $discount_percent = $total_amount > 0 ? $discount / $total_amount : 0;

        $show['total_amount'] = $total_amount;
        $show['discount'] = $discount;
        $show['discount_percent'] = $discount_percent;

        foreach($users as $userid){

            $user = User::find($userid);
            $city = Province::find($user->CityID);
            $district = District::find($user->DistrictID);

            $items = collect($cart->items)->filter(function($item) use($userid) {
                return $item['item']->user_id === $userid && $item['item']->type == 'Physical' ;
            });
            $amount = $items->sum(function ($product) {
                return $product['qty'] * $product['item_price'];
            });
            $total_price_sp = $items->sum(function ($product) {
                return $product['qty'] * $product['item_price_shopping_point'];
            });

            $product_final_amount = $items->sum(function ($product) {
                return $product['product_final_amount'];
            });

            $tv =  ($amount + $total_price_sp) * $tax / 100.0;
            $product_discount = ($product_final_amount * $discount_percent);
            $product_price = $product_final_amount + (($amount + $total_price_sp) * $tax / 100.0) - ($product_final_amount * $discount_percent);
            $product_price = $product_price > 0 ? $product_price : 0;
            $show[$userid]['items'] = $items;
            $show[$userid]['amount'] = $amount;
            $show[$userid]['total_price_sp'] = $total_price_sp;
            $show[$userid]['product_final_amount'] = $product_final_amount;
            $show[$userid]['tax'] = $tv;
            $show[$userid]['product_discount'] = $product_discount;
            $show[$userid]['product_price'] = $product_price;
            $weight = $items->sum(function ($product) {
                return $product['qty'] * $product['item']->weight;
            });
            $weight = $weight <= 0 ? 1000 : $weight;
            //private function get_online_services($from_p, $from_d, $to_p, $to_d, $w, $product_price, $collection_fee, $token){

            //$order_services = $this->getservices($is_online_payment, $to_province == $city->id);
            $order_services = $this->get_online_services($city->viettelpost_id, $district->viettelpost_id,$to_province_for_viettelpost->viettelpost_id,$to_district_for_viettelpost->viettelpost_id,
                $weight, $product_price, );

            $response = null;
            $fee['shop_id'] = $userid;
            $fee['shipping_cost'] = 0;
            $fee['srv'] = '';
            $fee['fromcity'] = 0;
            $fee['tocity'] = 0;
            $fee['data'] = null;
            $fee['rq'] = null;
            $fee['is_online_payment'] = $is_online_payment;

            foreach($order_services as $srv){
                $requestArr = array(
                    "PRODUCT_WEIGHT" => $weight,
                    "PRODUCT_PRICE" => $product_price,
                    "MONEY_COLLECTION" => $is_online_payment ? 0 : $product_price,
                    "ORDER_SERVICE" => $srv,
                    "ORDER_SERVICE_ADD" => "",
                    "SENDER_PROVINCE" => $city->viettelpost_id,
                    "SENDER_DISTRICT" => $district->viettelpost_id,
                    "RECEIVER_PROVINCE" => $to_province_for_viettelpost->viettelpost_id,
                    "RECEIVER_DISTRICT" => $to_district_for_viettelpost->viettelpost_id,
                    "PRODUCT_TYPE" => "HH",
                    "NATIONAL_TYPE" => "1",
                );

                $rqs [] = $requestArr;
                $response = HTTPRequester::HTTPPostBody($url, $requestArr);
                $responses []=$response;
                //dd($response);
                if(!$response){
                    continue;
                }
                else{
                    if(!$response['error']){
                        $fee['shipping_cost'] = $response["data"]["MONEY_TOTAL"];
                        $fee['srv'] = $srv;
                        $fee['fromcity'] = $city->id;
                        $fee['tocity'] = $to_province_for_viettelpost->id;
                        $fee['data'] = $response["data"];
                        $fee['rq'] = $requestArr;
                        if($fee['shipping_cost'] > 0)
                            break;
                    }
                    else{
                        continue;
                    }
                }
            }
            if($fee['shipping_cost'] == 0)
                return 0;
            $result->push($fee);
        }
        if(Session::has('temp_shipping_info')){
            Session::forget('temp_shipping_info');
        }
        Session::put('temp_shipping_info',$result);
        return $show;
        return $result->sum('shipping_cost');
    }

    private function get_online_service($from_p, $from_d, $to_p, $to_d, $w, $product_price, $collection_fee, $token){
        $url =  config('app.viettel_post.service_url');
        $requestArr = array(
            "SENDER_PROVINCE" => $from_p,
            "SENDER_DISTRICT" => $from_d,
            "RECEIVER_PROVINCE" => $to_p,
            "RECEIVER_DISTRICT" => $to_d,
            "PRODUCT_TYPE" => "HH",
            "PRODUCT_WEIGHT" => $w,
            "PRODUCT_PRICE" => $product_price,
            "MONEY_COLLECTION" => $collection_fee,
            "TYPE" => 1,
        );
        $header = array(
            'Content-Type: application/json',
            'Token: '.$token
        );

        $response = HTTPRequester::HTTPPostBodyHeader($url, $requestArr, $header);
        if(!$response){
            return null;
        }
        else{
            if(!isset($response['error'])){
                $data = collect($response);
                if($data->count() > 0){
                    $rs = $data->where('MA_DV_CHINH', 'PTN')->first();
                    if(isset($rs))
                        return $rs['MA_DV_CHINH'];
                    $rs = $data->where('MA_DV_CHINH', 'PHS')->first();
                    if(isset($rs))
                        return $rs['MA_DV_CHINH'];
                    $rs = $data->where('MA_DV_CHINH', 'VTK')->first();
                    if(isset($rs))
                        return $rs['MA_DV_CHINH'];
                    $rs = $data->first();
                    return $rs['MA_DV_CHINH'];
                }
            }
        }
        return null;
    }

    private function getservices($is_online_payment, $is_local){
        $order_services = array();
        if(!$is_online_payment){
            if($is_local){
                array_push($order_services, 'PHS');
                array_push($order_services, 'PTN');
                array_push($order_services, 'V02');
            }
            else{
                array_push($order_services, 'LCOD');
                array_push($order_services, 'NCOD');
            }
        }
        else{
            if($is_local){
                array_push($order_services, 'VCN');
                array_push($order_services, 'VHT');
            }
            else{
                array_push($order_services, 'VCN');
                array_push($order_services, 'VTK');
                array_push($order_services, 'VHT');
            }
        }
        return $order_services;
    }

    public function getfee($to_province, $to_district, $tax = 0, $discount = 0, $is_online_payment = 0)
    {
        return $this->getfeeValue($to_province, $to_district,$tax, $discount, $is_online_payment);
    }

    public function gettoken()
    {
        $url = config('app.viettel_post.login_url');
        $token = '';
        $requestArr = array(
            "USERNAME" => config('app.viettel_post.login_name'),
            "PASSWORD" => config('app.viettel_post.login_password')
        );
        $response = HTTPRequester::HTTPPostBody($url, $requestArr);
        if(!$response['error']){
            $token = $response["data"]["token"];
        }
        return $token;
    }

    public function createorder($order, $cart){
        // dd($temp_shipping_info);
        $url = config('app.viettel_post.order_url');
        $user = null;
        $digital = 1;
        foreach ($cart->items as $prod) {
            if($prod['item']->type == 'Physical')
            {
                $user[] = $prod['item']['user_id'];
                $digital = 0;
            }
        }
        //digital item no need to delivery
        if($digital == 1){
            return array(true, $order, "Digital All");
        }

        $discount_percent = 0;
        if($order->coupon_discount > 0){
            $discount_items = collect($cart->items);
            $discount_product_price = $discount_items->sum(function ($product) {
                return $product['product_final_amount'];
            });
            //dd([$discount_product_price, $order->coupon_discount]);
            $discount_percent = $discount_product_price > 0 ? $order->coupon_discount / $discount_product_price : 0;
        }

        $users = array_unique($user);
        $token = $this->gettoken();
        if (!$token) return array(false, $order, "Get token failed");

        $to_province = Province::find($order->is_shipdiff ? $order->shipping_province_id : $order->customer_province_id);
        $to_district = District::find($order->is_shipdiff ? $order->shipping_district_id : $order->customer_district_id);
        $to_ward = Ward::find($order->is_shipdiff ? $order->shipping_ward_id : $order->customer_ward_id);

        if (!$to_province || !$to_district) return array(false, $order, "To destination null");
        $receiver_address = $order->is_shipdiff ? $order->shipping_address : $order->customer_address;
        $receiver_address = $receiver_address.','.$to_ward->name.','.$to_district->name.','.$to_province->name;
        $order_shipping_cost = 0;

        $rqs = null;
        $rps = null;
        $rrs = null;
        $auto_num = 1;
        foreach($users as $userid){

            $user = User::find($userid);
            $pick_city = Province::find($user->CityID);
            $pick_district = District::find($user->DistrictID);
            $pick_ward = Ward::find($user->ward_id);
            if (!$pick_city || !$pick_district) return array(false, $order, "Pick destination null");

            $weight = 0;
            $quantity = 0;
            $totalamount = 0;
            $totalweight = 0;
            $listproduct = [];
            $listname = '';

            $items = collect($cart->items)->filter(function($item) use($userid) {
                return $item['item']->user_id === $userid && $item['item']->type == 'Physical' ;
            });

            $temp_shipping_info = null;
            if(Session::has('temp_shipping_info')){
                $temp_shipping_info = Session::get('temp_shipping_info')->filter(function($r) use($userid) {
                    return $r['shop_id'] === $userid ;
                })->first();
            }

            $products_amount = 0;
            $tax_amount = 0;
            $discount_amount = 0;
            $total_qty = 0;
            $total_item = $items->count();

            foreach($items as $it){
                $quantity += $it['qty'];
                //$totalfeeo += list[j].Fee;
                $item_amount = $it['product_final_amount'];
                $products_amount += $item_amount;
                $tax_value = ($it['price'] + $it['price_shopping_point_amount']) * $order->tax / 100.0;
                $tax_amount += $tax_value;
                $discount_value = $item_amount * $discount_percent;
                $discount_amount += $discount_value;
                $totalamount += $item_amount + $tax_value - $discount_value;
                $weight = $it['qty'] * $it['item']->weight;
                $total_qty += $it['qty'];
                $totalweight+=$weight;
                $arr = array(
                    "PRODUCT_NAME" => $it['item']->name,
                    "PRODUCT_WEIGHT" => $weight,
                    "PRODUCT_PRICE" =>  $it['item_price'] + $it['item_price_shopping_point'] ,
                    "PRODUCT_QUANTITY" => $it['qty']
                );
                array_push($listproduct, $arr);
                if($listname != ''){
                    $listname = $listname.','.$it['item']->name;
                }
                else{
                    $listname = $it['item']->name;
                }
            }
            $totalamount = $totalamount > 0 ? $totalamount : 0;
            $weight = $weight <= 0 ? 1000 : $weight;

            //find shop service and send request
            $sender_address = $user->address.
                ($pick_ward != null ? ','.$pick_ward->name : '')
                .','.$pick_district->name.','.$pick_city->name;

            $requestArr = array(
                "ORDER_NUMBER" => $order->order_number.$auto_num,
                // "GROUPADDRESS_ID" => config('app.viettel_post.group_address_id'),
                // "CUS_ID" => config('app.viettel_post.cus_id'),
                "GROUPADDRESS_ID" => 0,
                "CUS_ID" => 0,
                "SENDER_FULLNAME" => $user->name,
                "SENDER_ADDRESS" => $sender_address,
                "SENDER_PHONE" => $user->phone,
                "SENDER_EMAIL" => $user->email,
                "SENDER_WARD" => ($pick_ward != null ?  $pick_ward->viettelpost_id : 0),
                "SENDER_DISTRICT" => $pick_district->viettelpost_id,
                "SENDER_PROVINCE" => $pick_city->viettelpost_id,
                "RECEIVER_FULLNAME" => $order->is_shipdiff ? $order->shipping_name : $order->customer_name,
                "RECEIVER_ADDRESS" => $receiver_address,
                "RECEIVER_PHONE" => $order->is_shipdiff ? $order->shipping_phone : $order->customer_phone,
                "RECEIVER_EMAIL" => $order->is_shipdiff ? $order->shipping_email : $order->customer_email,
                "RECEIVER_WARD" => $to_ward->viettelpost_id,
                "RECEIVER_DISTRICT" => $to_district->viettelpost_id,
                "RECEIVER_PROVINCE" => $to_province->viettelpost_id,
                "PRODUCT_NAME" => $listname,
                "PRODUCT_QUANTITY" => $quantity,
                "PRODUCT_PRICE" => $totalamount,
                "PRODUCT_WEIGHT" => $totalweight,
                "PRODUCT_TYPE" => "HH",
                "ORDER_SERVICE_ADD" => "",
                "ORDER_VOUCHER" => "",
                "ORDER_NOTE" => "",
                "LIST_ITEM" => $listproduct,
            );
            $header = array(
                'Content-Type: application/json',
                'Token: '.$token
            );
            $response = null;
            if($temp_shipping_info != null){
                $requestArr["ORDER_SERVICE"] = $temp_shipping_info['srv'];
                if($order->is_online_payment){
                    $requestArr["ORDER_PAYMENT"] = 1;
                    $requestArr["MONEY_COLLECTION"] = 0;
                    $requestArr["MONEY_TOTALFEE"] = 0;
                    $requestArr["MONEY_TOTAL"] = 0;
                }
                else{
                    if($totalamount > 5000){
                        $requestArr["ORDER_PAYMENT"] = 2;
                        $requestArr["MONEY_COLLECTION"] =  $totalamount;
                        $requestArr["MONEY_TOTAL"] = $totalamount + $temp_shipping_info['shipping_cost'];
                    }
                    else{
                        $requestArr["ORDER_PAYMENT"] = 4;
                        $requestArr["MONEY_COLLECTION"] =  0;
                        $requestArr["MONEY_TOTAL"] = $temp_shipping_info['shipping_cost'];
                    }
                    $requestArr["MONEY_TOTALFEE"] = $temp_shipping_info['shipping_cost'];
                }
                $response = HTTPRequester::HTTPPostBodyHeader($url, $requestArr, $header);
                $rqs [] = $requestArr;
            }
            else
            {
                $service = $this->get_online_service($pick_city->viettelpost_id, $pick_district->viettelpost_id,$to_province->viettelpost_id,$to_district->viettelpost_id,
                    $weight, $totalamount, $order->is_online_payment ? 0 : $totalamount, $token);
                if($service == null){
                    return 0;
                }
                //$order_services = $this->getservices($order->is_online_payment, $to_province->id == $pick_city->id);

                $requestArr["ORDER_SERVICE"] = $service;
                if($order->is_online_payment){
                    $requestArr["ORDER_PAYMENT"] = 1;
                    $requestArr["MONEY_COLLECTION"] = 0;
                    $requestArr["MONEY_TOTALFEE"] = 0;
                    $requestArr["MONEY_TOTAL"] = 0;
                }
                else{
                    if($totalamount > 5000){
                        $requestArr["ORDER_PAYMENT"] = 2;
                        $requestArr["MONEY_COLLECTION"] =  $totalamount;
                        $requestArr["MONEY_TOTAL"] = $totalamount + $temp_shipping_info['shipping_cost'];
                    }
                    else{
                        $requestArr["ORDER_PAYMENT"] = 4;
                        $requestArr["MONEY_COLLECTION"] =  0;
                        $requestArr["MONEY_TOTAL"] = $temp_shipping_info['shipping_cost'];
                    }
                    $requestArr["MONEY_TOTALFEE"] = 0;
                }
                $response = HTTPRequester::HTTPPostBodyHeader($url, $requestArr, $header);
                $rqs [] = $requestArr;
            }

            // $rps [] = $response;
            // $rrs [] = $temp_shipping_info;
            // $rrs [] = $rqs;
            // $rrs [] = $rps;
            // dd($rrs);
            if(!$response){
                return array(false, $order, "Response failed!");
            }
            else{
                if(!$response['error']){
                    $fee = $response["data"]["MONEY_TOTAL"];
                    $partner_order_number = $response["data"]["ORDER_NUMBER"];
                    $order_shipping_cost += $fee;
                    DB::table('order_consumer_shipping_costs')
                        ->updateOrInsert(
                            ['order_id' => $order->id, 'shop_id' => $userid],
                            ['from_city_id' => $pick_city->id,
                            'from_district_id' => $pick_district->id,
                            'consumer_id' => $order->user_id,
                            'to_city_id' => $to_province->id,
                            'to_district_id' => $to_district->id,
                            'shipping_partner' => 'Viettel Post',
                            'shipping_partner_code' => $partner_order_number,
                            'shipping_cost' => $fee,
                            'remarks' => $response["message"],
                            'MONEY_COLLECTION' => $response["data"]["MONEY_COLLECTION"],
                            'EXCHANGE_WEIGHT' => $response["data"]["EXCHANGE_WEIGHT"],
                            'MONEY_TOTAL_FEE' => $response["data"]["MONEY_TOTAL_FEE"],
                            'MONEY_FEE' => $response["data"]["MONEY_FEE"],
                            'MONEY_COLLECTION_FEE' => $response["data"]["MONEY_COLLECTION_FEE"],
                            'MONEY_OTHER_FEE' => $response["data"]["MONEY_OTHER_FEE"],
                            'MONEY_VAS' => $response["data"]["MONEY_VAS"],
                            'MONEY_VAT' => $response["data"]["MONEY_VAT"],
                            'KPI_HT' => $response["data"]["KPI_HT"],
                            'to_ward_id' => $order->customer_ward_id,
                            'created_at' => date("Y-m-d H:m:s"),
                            'updated_at' => date("Y-m-d H:m:s"),
                            'weight' => $totalweight,
                            'products_amount' => $products_amount,
                            'tax_amount' => $tax_amount,
                            'discount_amount' => $discount_amount,
                            'total_qty' => $total_qty,
                            'total_item' => $total_item,
                            'delivery_service' => $requestArr["ORDER_SERVICE"],
                            ]
                        );
                        //dd([$rqs, $response]);
                }
                else{
                    $message = $response["message"];
                    DB::table('order_consumer_shipping_costs')
                    ->updateOrInsert(
                        ['order_id' => $order->id, 'shop_id' => $userid],
                        ['from_city_id' => $pick_city->id,
                        'from_district_id' => $pick_district->id,
                        'consumer_id' => $order->user_id,
                        'to_city_id' => $to_province->id,
                        'to_district_id' => $to_district->id,
                        'shipping_partner' => 'Viettel Post',
                        'shipping_partner_code' => 'error: '.$response['error'],
                        'remarks' => $message,
                        'to_ward_id' => $order->customer_ward_id,
                        'created_at' => date("Y-m-d H:m:s"),
                        'updated_at' => date("Y-m-d H:m:s"),
                        'weight' => $totalweight,
                        'products_amount' => $products_amount,
                        'tax_amount' => $tax_amount,
                        'discount_amount' => $discount_amount,
                        'total_qty' => $total_qty,
                        'total_item' => $total_item,
                        'delivery_service' => $requestArr["ORDER_SERVICE"],
                        ]
                    );
                    //dd([$rqs, $response]);
                    return array(false, $order, "Response with error");
                }
            }//end if response
            $auto_num = $auto_num + 1;
        }//end foreach shops

        // dd($rrs);

        $detail = collect();
        $detail->push($rqs);
        $detail->push($rps);
        $data1 = [Session::get('temp_shipping_info'), $detail, $discount_percent, $cart, $order];
        //dd($data1);
        $order->is_send_fee = true;
        $order->shipping_cost = $order_shipping_cost;
        $order->save();
        $result[0] = true;
        $result[1] = $order;
        return $result;
    }

    public function updateorderstatus($partner_order_number, $status, $note){
        $requestArr = array(
            "TYPE" => $status,
            "ORDER_NUMBER" => $partner_order_number,
            "NOTE" => $note
        );
        $url = config('app.viettel_post.update_url');
        $token = $this->gettoken();
        if (!$token) return false;
        $header = array(
            'Content-Type: application/json',
            'Token: '.$token
        );
        $response = HTTPRequester::HTTPPostBodyHeader($url, $requestArr, $header);
        if(!$response){
            return false;
        }
        else{
            if(!$response['error']){
                return true;
            }
            else{
                return false;
            }
        }//end else response
    }
}


