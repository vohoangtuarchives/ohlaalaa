<?php

namespace App\Http\Controllers\Api;

use Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Blog;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Counter;
use App\Models\Slider;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Province;
use Markury\MarkuryPost;
use App\Models\Subscriber;
use App\Enums\PreferredType;
use App\Models\BlogCategory;
use App\Models\CouponVendor;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use InvalidArgumentException;
use App\Models\Generalsetting;
use App\Models\ProductClick;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Collection;
use function GuzzleHttp\json_encode;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Category;
use App\Models\Ward;
use App\Classes\CometChatHTD;
use Validator;
use App\Models\Cart;


class ApiCartController extends Controller
{
    private  $ps, $lang;
    const STATUS    = 1;
    const PAGINATE  = 20;
    const FEATURE  = 18;
    const TOP  = 20;
    const BEST  = 18;
    const DISCOUNT  = 18;
    const BIG  = 6;
    const IS_VENDOR  = '2'; //active
    const PREFERRED  = '1';
    const KOL  = '1';
    const SPECIAL_KOL  = '1';
    private $selectable = ['id','user_id','name','slug','features','colors','thumbnail','price','previous_price','attributes','size','size_price','discount_date','weight',
    'price_shopping_point',
    'percent_price',
    'percent_shopping_point'];

    public function __construct() {
        $this->ps = DB::table('pagesettings')->find(1);

        if (Session::has('language'))
        {
            $data = \DB::table('languages')->find(Session::get('language'));
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $this->lang = json_decode($data_results);

        }
        else
        {
            $data = \DB::table('languages')->where('is_default','=',1)->first();
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $this->lang = json_decode($data_results);

        }

    }

// -------------------------------- HOME PAGE SECTION ----------------------------------------

	public function index() {
        // $this->code_image();
        //  if(!empty($request->reff))
        //  {
        //     $affilate_user = User::where('affilate_code','=',$request->reff)->first();
        //     if(!empty($affilate_user))
        //     {
        //         $gs = Generalsetting::findOrFail(1);
        //         if($gs->is_affilate == 1)
        //         {
        //             Session::put('affilate', $affilate_user->id);
        //             return redirect()->route('front.index');
        //         }
        //     }
        //  }
        $data = [];
        if($this->ps->slider == 1) {
            $sliders = Slider::get();
            // $data['sliders']['title'] = 'Slider Banners';
            // if (count($sliders)>0) {
            //     $data[] = $sliders;
            // }
            $tmp['title'] = 'Slider Banners';
            $tmp['type'] = 'slider-banners';
            $tmp['item'] = $sliders;
            $data['slider-banners'] = $tmp;
        }

        //san pham noi bat
        if($this->ps->featured == 1) {
            $feature_products =  Product::with('user')
            ->where('featured','=',1)
            ->where('status','=',1)
            ->select($this->selectable)
            ->inRandomOrder()
            ->limit(static::FEATURE)->get()
            ->reject(function($item){
                if ($item->user_id != 0) {
                    if ($item->user->is_vendor != 2) {
                        return true;
                    }
                }
                return false;
            });

            $feature_products  = $feature_products->values();
            $tmp['title'] = 'Sản phẩm nổi bật';
            $tmp['type'] = 'feature-products';
            $tmp['item'] =  $feature_products;
            $data['feature-products'] =  $tmp;
        }

        if ($this->ps->small_banner == 1) {
            $top_small_banners = Banner::where('type','=','TopSmall')->get();
            // $data ['top_small_banners']['title'] = 'Top small banners';
            // $top_small_banners['title']= 'Top small banners';
            $tmp['title'] = 'Top small banners';
            $tmp['type'] = 'top-small-banners';
            $tmp['item'] = $top_small_banners;
            $data['top-small-banners'] =  $tmp;
        }
        // Sản phẩm bán chạy
        $best_products = Product::with('user')
        ->where('best','=',1)
        ->where('status','=',1)
        ->select($this->selectable)
        ->inRandomOrder()
        ->limit(static::BEST)
        ->get()
        ->reject(function($item){
            if($item->user_id != 0){
                if($item->user->is_vendor != 2){
                    return true;
                }
            }
            return false;
        });
        $best_products  = $best_products->values();
        $tmp['title'] = 'Sản phẩm bán chạy nhất';
        $tmp['type'] = 'best-products';
        $tmp['item'] = $best_products;
        $data['best-products'] =  $tmp;

        // Deal Chớp Nhoáng
        if ($this->ps->flash_deal == 1) {
            $discount_products =  Product::with('user')
            ->where('is_discount','=',1)
            ->where('status','=',1)
            ->where('discount_date', '>=', date('Y-m-d'))
            ->inRandomOrder()
            ->limit(static::DISCOUNT)
            ->get()
            ->reject(function($item){
                if($item->user_id != 0){
                    if($item->user->is_vendor != 2){
                        return true;
                    }
                }
                return false;
            });
            $discount_products  = $discount_products->values();
            $tmp['title'] = 'Deal chớp nhoáng';
            $tmp['type'] = 'discount-products';
            $tmp['item'] = $discount_products;
            $data['discount-products'] =  $tmp;
        }


         //  {{-- Xếp hạng cao nhất --}}
        $top_products = Product::with('user')
         ->where('top','=',1)
         ->where('status','=',1)
         ->select($this->selectable)
         ->inRandomOrder()
         ->limit(static::TOP)
         ->get()->reject(function($item){
            if($item->user_id != 0){
                if($item->user->is_vendor != 2){
                    return true;
                }
            }
            return false;
        });
        $top_products  = $top_products->values();
        $tmp['title'] = 'Xếp hạng cao nhất';
        $tmp['type'] = 'top-products';
        $tmp['item'] = $top_products;
        $data['top-products'] =  $tmp;

        $big_products = Product::with('user')
        ->where('big','=',1)
        ->where('status','=',1)
        ->select($this->selectable)
        ->inRandomOrder()
        ->limit(static::BIG)
        ->get()
        ->reject(function($item){
            if($item->user_id != 0){
                if($item->user->is_vendor != 2){
                    return true;
                }
            }
            return false;
        });

        $big_products  = $big_products->values();
        $tmp['title'] = 'Big Save';
        $tmp['type'] = 'big-products';
        $tmp['item'] = $big_products;
        $data['big-products'] =  $tmp;


        //return (json_encode($data));
        // echo "<pre>"; print_r(json_encode($data));echo "</pre>";
        // $top_small_banners = DB::table('banners')->where('type','=','TopSmall')->get();
        // $ps = DB::table('pagesettings')->find(1);

        if($data) {
            return response()->success('Your custom success message', 200,$data);
        } else {
            return response()->error('Your custom error message', 'Validation errors or else');
        }
	}

    public function addtocart($id = null,Request $request)
    {
        if ($id == null) {
            return 'khong co san pham';
        }
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

    public function addCart(Request $request)
    {
        $data = [];
        $id  = null;
        $curr = Currency::where('is_default','=',1)->first();
        if ($request->all()) {
            $id = $request->id;

            if ($id == null) {
                return response()->success('Không có sản phẩm', 404, []);
            }

            $qty = $request->qty;
            $size = str_replace(' ','-', $request->size);
            $color = $request->color;
            $size_qty = $request->size_qty;
            $size_price = (double)($request->size_price);
            $size_key = $request->size_key;
            $keys = $request->keys;
            $values = $request->values;
            $prices = $request->prices;
            $keys = $keys == "" ? '' : implode(',',$keys);
            $values = $values == "" ? '' : implode(',',$values);

            $size_price = ($size_price / $curr->value);
        } else {
            return response()->success('Không có sản phẩm', 404, []);
        }

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


        if ($prod->user_id != 0){
            $gs = Generalsetting::findOrFail(1);
            $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
            $prod->price = round($prc,2);
        }
        if (!empty($prices)) {
            foreach($prices as $data){
                $prod->price += ($data / $curr->value);
            }
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
        // dd($prod);
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


        //dd($prod->price, $size,  $color);
        // $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart([]);

        $cart->add($prod, $prod->id,$size,$color,$keys,$values);

        if (count($cart->items) >0) {
            foreach ($cart->items as $k => $v) {
                $data['cartItemId']   =  $k;
                $data['product'] = $v;
            }
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['dp'] == 1)
        {   //Hệ thống không thanh toán sản phẩm digital
            return response()->success('Hệ thống không thanh toán sản phẩm digital', 404, $data);

        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['stock'] < 0)
        {
            //Hết Hàng
            return response()->success($this->lang->out_stock, 404, $data);
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
        {
            if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['qty'] > $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
            {
               //Hết Hàng
                return response()->success($this->lang->out_stock, 404, $data);
            }
        }


        return response()->success('Success message', 200, $data);


        dd($cart->items);
        $data1[0] = count($cart->items);
        return response()->json($data1);
    }


    public function checkCart(Request $request)
    {
        $data = [];
        $id  = null;
        $curr = Currency::where('is_default','=',1)->first();
        if ($request->all()) {
            $id = $request->id;

            if ($id == null) {
                return response()->success('Không có sản phẩm', 404, []);
            }

            $qty = $request->qty;
            $size = str_replace(' ','-', $request->size);
            $color = $request->color;
            $size_qty = $request->size_qty;
            $size_price = (double)($request->size_price);
            $size_key = $request->size_key;
            $keys = $request->keys;
            $values = $request->values;
            $prices = $request->prices;
            $keys = $keys == "" ? '' : implode(',',$keys);
            $values = $values == "" ? '' : implode(',',$values);

            $size_price = ($size_price / $curr->value);
        } else {
            return response()->success('Không có sản phẩm', 404, []);
        }

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


        if ($prod->user_id != 0){
            $gs = Generalsetting::findOrFail(1);
            $prc = $prod->price + $gs->fixed_commission + ($prod->price/100) * $gs->percentage_commission ;
            $prod->price = round($prc,2);
        }
        if (!empty($prices)) {
            foreach($prices as $data){
                $prod->price += ($data / $curr->value);
            }
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
        // dd($prod);
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


        //dd($prod->price, $size,  $color);
        // $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart([]);

        $cart->add($prod, $prod->id,$size,$color,$keys,$values);

        if (count($cart->items) >0) {
            foreach ($cart->items as $k => $v) {
                $data['cartItemId']   =  $k;
                $data['product'] = $v;
            }
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['dp'] == 1)
        {   //Hệ thống không thanh toán sản phẩm digital
            return response()->success('Hệ thống không thanh toán sản phẩm digital', 404, $data);

        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['stock'] < 0)
        {
            //Hết Hàng
            return response()->success($this->lang->out_stock, 404, $data);
        }

        if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
        {
            if($cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['qty'] > $cart->items[$id.$size.$color.str_replace(str_split(' ,'),'',$values)]['size_qty'])
            {
               //Hết Hàng
                return response()->success($this->lang->out_stock, 404, $data);
            }
        }


        return response()->success('Success message', 200, $data);


        dd($cart->items);
        $data1[0] = count($cart->items);
        return response()->json($data1);
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

    public function updateSP($id, $point = 0)
    {
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
}
