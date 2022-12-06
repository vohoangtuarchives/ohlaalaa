<?php

namespace App\Http\Controllers\Api;

use Auth;
use Carbon\Carbon;
use App\Models\Blog;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Counter;
use App\Models\Slider;
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
use App\Classes\CometChatHTD;

class ApiController extends Controller
{
    private  $ps, $lang;
    const STATUS    = 1;
    const PAGINATE  = 3;

    public function __construct() {
        if (Session::has('language')){
            $data = cache()->remember('session_language', now()->addDay(), function () {
                return DB::table('languages')->find(Session::get('language'));
            });
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $this->lang = json_decode($data_results);
        } else {
            $data = cache()->remember('default_language', now()->addDay(), function () {
                return DB::table('languages')->where('is_default','=',1)->first();
            });
            $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
            $this->lang = json_decode($data_results);
        }

        $this->ps = DB::table('pagesettings')->find(1);
        //bo het code o constructor thi no chay vao install lai
        //source o install no gọi ở file 500
        //vao constructor nay

        //return redirect()->route('front.index');
        //$this->auth_guests();

        if(isset($_SERVER['HTTP_REFERER'])){
            $referral = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            if ($referral != $_SERVER['SERVER_NAME']){

                $brwsr = Counter::where('type','browser')->where('referral',$this->getOS());
                if($brwsr->count() > 0){
                    $brwsr = $brwsr->first();
                    $tbrwsr['total_count']= $brwsr->total_count + 1;
                    $brwsr->update($tbrwsr);
                }else{
                    $newbrws = new Counter();
                    $newbrws['referral']= $this->getOS();
                    $newbrws['type']= "browser";
                    $newbrws['total_count']= 1;
                    $newbrws->save();
                }

                $count = Counter::where('referral',$referral);
                if($count->count() > 0){
                    $counts = $count->first();
                    $tcount['total_count']= $counts->total_count + 1;
                    $counts->update($tcount);
                }else{
                    $newcount = new Counter();
                    $newcount['referral']= $referral;
                    $newcount['total_count']= 1;
                    $newcount->save();
                }
            }
        }else{
            $brwsr = Counter::where('type','browser')->where('referral',$this->getOS());
            if($brwsr->count() > 0){
                $brwsr = $brwsr->first();
                $tbrwsr['total_count']= $brwsr->total_count + 1;

                $brwsr->update($tbrwsr);
                //dd('handled! else if update');
            }else{
                $newbrws = new Counter();
                $newbrws['referral']= $this->getOS();
                $newbrws['type']= "browser";
                $newbrws['total_count']= 1;
                $newbrws->save();
            }
        }
    }

    function getOS() {

        $user_agent     =   !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Unknown";

        $os_platform    =   "Unknown OS Platform";

        $os_array       =   array(
            '/windows nt 10/i'     =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $user_agent)) {
                $os_platform    =   $value;
            }

        }
        return $os_platform;
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
        // $data['slider-banners'] = 'Slider Banners';
        // return response()->success('Your custom success message', 200,$data);
        $selectable = ['id','user_id','name','slug','features','colors','thumbnail','price','previous_price','attributes','size','size_price','discount_date','weight',
            'price_shopping_point',
            'percent_price',
            'percent_shopping_point'];
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
        // $data['slider-banners'] = 'Slider Banners'
        // print_r( $sliders->toArray()); die;

        //san pham noi bat
        if($this->ps->featured == 1) {
            $feature_products =  Product::with('user')
            ->where('featured','=',1)
            ->where('status','=',1)
            ->select($selectable)
            ->inRandomOrder()
            ->limit(18)->get()
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
            $top_small_banners = DB::table('banners')->where('type','=','TopSmall')->get();
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
        ->select($selectable)
        ->inRandomOrder()
        ->limit(18)
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
        $discount_products =  Product::with('user')
        ->where('is_discount','=',1)
        ->where('status','=',1)
        ->inRandomOrder()
        ->limit(18)
        // ->orderBy('id','desc')
        // ->take(8)
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

        //return (json_encode($data));
         // echo "<pre>"; print_r(json_encode($data));echo "</pre>";
        // ?dd( $sliders);
        // $top_small_banners = DB::table('banners')->where('type','=','TopSmall')->get();
        // $ps = DB::table('pagesettings')->find(1);

        if($data) {
            return response()->success('Your custom success message', 200,$data);
        } else {
            return response()->error('Your custom error message', 'Validation errors or else');
        }
	}

    public function extraIndex() {
        $services = DB::table('services')->where('user_id','=',0)->get();
        $bottom_small_banners = DB::table('banners')->where('type','=','BottomSmall')->get();
        $large_banners = DB::table('banners')->where('type','=','Large')->get();
        $reviews =  DB::table('reviews')->get();
        $ps = DB::table('pagesettings')->find(1);
        $partners = DB::table('partners')->get();
        $selectable = ['id','user_id','name','slug','features','colors','thumbnail','price','previous_price','attributes','size','size_price','discount_date','weight',
        'price_shopping_point',
        'percent_price',
        'percent_shopping_point'];
        $discount_products =  Product::with('user')
            ->where('is_discount','=',1)
            ->where('status','=',1)
            ->inRandomOrder()
            ->limit(18)
            // ->orderBy('id','desc')
            // ->take(8)
            ->get()
            ->reject(function($item){
                if($item->user_id != 0){
                    if($item->user->is_vendor != 2){
                        return true;
                    }
                }
                return false;
            });
        $best_products = Product::with('user')
            ->where('best','=',1)
            ->where('status','=',1)
            ->select($selectable)
            ->inRandomOrder()
            ->limit(18)
            // ->orderBy('id','desc')
            // ->take(6)
            ->get()
            ->reject(function($item){

            if($item->user_id != 0){
                if($item->user->is_vendor != 2){
                    return true;
                }
            }
            return false;

        });

        $top_products = Product::with('user')
            ->where('top','=',1)
            ->where('status','=',1)
            ->select($selectable)
            // ->orderBy('id','desc')
            // ->take(8)
            ->inRandomOrder()
            ->limit(20)
            ->get()->reject(function($item){

            if($item->user_id != 0){
                if($item->user->is_vendor != 2){
                    return true;
                }
            }
            return false;

        });

        $big_products = Product::with('user')
            ->where('big','=',1)
            ->where('status','=',1)
            ->select($selectable)
            // ->orderBy('id','desc')
            // ->take(6)
            ->inRandomOrder()
            ->limit(18)
            ->get()
            ->reject(function($item){

            if($item->user_id != 0){
                if($item->user->is_vendor != 2){
                    return true;
                }
            }
            return false;

        });
        $hot_products =  Product::with('user')
            ->where('hot','=',1)
            ->where('status','=',1)
            ->select($selectable)
            //->orderBy('id','desc')->take(9)
            ->inRandomOrder()
            ->limit(18)
            ->get()->reject(function($item){

            if($item->user_id != 0){
                if($item->user->is_vendor != 2){
                    return true;
                }
            }
            return false;

        });
        $latest_products =  Product::with('user')
            ->where('latest','=',1)
            ->where('status','=',1)
            ->select($selectable)
            ->inRandomOrder()
            ->limit(18)
            //->orderBy('id','desc')->take(9)

            ->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $trending_products =  Product::with('user')
        ->where('trending','=',1)
        ->where('status','=',1)
        ->select($selectable)
        ->inRandomOrder()
        ->limit(18)
        //->orderBy('id','desc')->take(9)
        ->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        $sale_products =  Product::with('user')
        ->where('sale','=',1)
        ->where('status','=',1)
        ->select($selectable)
        ->inRandomOrder()
        ->limit(18)
        //->orderBy('id','desc')->take(9)
        ->get()->reject(function($item){

            if($item->user_id != 0){
              if($item->user->is_vendor != 2){
                return true;
              }
            }
            return false;

          });
        return view('front.extraindex',compact('ps','services','reviews','large_banners','bottom_small_banners','best_products','top_products','hot_products','latest_products','big_products','trending_products','sale_products','discount_products','partners'));
    }

    public function search(Request $request) {
        // set_time_limit(0);
        ini_set('max_execution_time', 600);
        if (Session::has('currency')){
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default','=',1)->first();
        }

        $para = $keyword = $cat = $subcat = $childcat = '';
        $sort = $request->sort;
        $minprice = $request->min;
        $maxprice = $request->max;
        $minprice = round(($minprice / $curr->value),2);
        $maxprice = round(($maxprice / $curr->value),2);
        $location = $request->search_location;

        $vendor = User::where('shop_name','=',$string)->firstOrFail();
        $data['vendor'] = $vendor;
        $data['name'] = $name;
        $data['services'] = DB::table('services')->where('user_id','=',$vendor->id)->get();

        if(isset($request->keyword) && $request->keyword !='') {
            $keyword =  $request->keyword;
            $para  .= "&keyword=". $request->keyword;
        }

        if (isset($request->cat) && !empty($request->cat)) {
            $cat = $request->cat;
            $data['cat'] = $cat;
            $para .= "&cat=". $request->cat;
        }

        if (isset($request->subcat) && !empty($request->subcat)) {
            $subcat = $request->subcat;
            $data['subcat'] = $subcat;
            $para .= "&subcat=". $request->subcat;
        }

        if (isset($request->childcat) && !empty($request->childcat)) {
            $childcat = $request->childcat;
            $data['childcat'] = $childcat;
            $para .= "&childcat=". $request->childcat;
        }
        if ($keyword == '' || ($cat != '' || $subcat != '' || $childcat != '')) {
            return response()->error('Error message', 'Not have paramaters');
        }

        $prods =  Product::Select(DB::raw('products.*'))
            ->join('users as u', 'u.id', '=', 'products.user_id')
            ->where(function ($query) {
                $query->where('u.is_vendor', '=', 2)->whereNotNull('CityID');
            })
        //  $prods =  Product::Select(DB::raw('products.*'))
            ->when($cat, function ($query, $cat) {
                if($cat == null)
                    return $query;
                return $query->where('category_id', $cat);
            })
            ->when($subcat, function ($query, $subcat) {
                if($subcat == null)
                    return $query;
                return $query->where('subcategory_id', $subcat);
            })
            ->when($childcat, function ($query, $childcat) {
                if($childcat == null)
                    return $query;
                return $query->where('childcategory_id', $childcat);
            })
            // ->when($location, function ($query, $location) {
            //     if($location == 0)
            //         return $query;
            //     return $query->join('users as shop', 'user_id', '=', 'shop.id')->where('CityID', $location);
            // })
            // ->when($search, function ($query, $search) {
            //     return $query->where('products.name' , 'like', '%'.$search.'%');
            // })
            ->when($location, function ($query, $location) {
                if($location == 0)
                    return $query;
                return $query->whereRaw('products.user_id IN  (SELECT id FROM users where `CityID` =  '.$location.')');
            })
            //task-1, thannd, fixed data search
            ->when($keyword, function ($query, $keyword) {
                return $query->where(  DB::raw('LOWER(products.name)'), 'like', DB::raw("CONCAT('%', CONVERT(LOWER('".$keyword."'), BINARY), '%')"));
            })
            // ->when($search, function ($query, $search) {
            //       return $query->whereRaw('MATCH (name) AGAINST (? IN BOOLEAN MODE)' , array($search));
            // })
            // ->when($minprice, function($query, $minprice) {
            //     return $query->where('price', '>=', $minprice);
            // })
            // ->when($maxprice, function($query, $maxprice) {
            //     return $query->where('price', '<=', $maxprice);
            // })
            ->when($sort, function ($query, $sort) {
                if ($sort=='date_desc') {
                    return $query->orderBy('products.id', 'DESC');
                }
                elseif($sort=='date_asc') {
                    return $query->orderBy('products.id', 'ASC');
                }
                elseif($sort=='price_desc') {
                    return $query->orderBy('price', 'DESC');
                }
                elseif($sort=='price_asc') {
                    return $query->orderBy('price', 'ASC');
                }
            })->where('products.status', static::STATUS)
            ->when(empty($sort), function ($query, $sort) {
                return $query->orderBy('products.id', 'DESC');
            });
        $prods = $prods->where('products.status', static::STATUS)->get();

        $prods = (new Collection(Product::filterProductsCustom($prods)))->paginate(static::PAGINATE);
        //$prods = (new Collection($prods))->paginate(12);
        // $prods = (new Collection(Product::filterProducts($prods)))->paginate(static::PAGINATE);
        // $prods = $prods->paginate(static::PAGINATE);
        $data['products'] = $prods->values();

        $data['total_page']   =  $prods->lastPage();
        $data['current_page'] =  $prods->currentPage();
        $data['url'] = $prods->url( $prods->currentPage());
        $data['action'] = url("/").'/category?search=' . $keyword;

        $data['nextPageUrl'] = $prods->nextPageUrl();
        if ($para != '') {
            $data['url'] .= $para;
            $data['nextPageUrl'] .= $para;
        }

        if($data) {
            return response()->success('Success message', 200, $data);
        } else {
            return response()->error('Error message', 'not have parameter');
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

    public function product($id = null)
    {
        $data = [];
        if ($id != null && $id != '') {

            $this->code_image();
            $productt = Product::findOrFail($id);
            if ($productt->status == 0) {
                return response()->error('Product was not support', 404);
            }

            $productt->views+=1;
            $productt->update();
            $data['product'] =  $productt;
            if (Session::has('currency'))
            {
                $curr = Currency::find(Session::get('currency'));
            }
            else
            {
                $curr = Currency::where('is_default','=',1)->first();
            }

            $product_click =  new ProductClick;
            $product_click->product_id = $productt->id;
            $product_click->date = Carbon::now()->format('Y-m-d');
            $product_click->save();

            if($productt->user_id != 0) {
                if(Auth::guard('web')->check()) {
                    $user = $productt->user;
                    //create comet_chat user
                    $comet_detail = CometChatHTD::create_user($user);
                    if($comet_detail['authToken'] != null){
                        $user->comet_token = $comet_detail['authToken'];
                        $user->comet_note = "From: IP [".\Request::ip()."]";
                        $from_u = Auth::guard('web')->user();
                        $user->comet_note = $user->comet_note." - Email [".$from_u->email."]";
                        $user->save();
                    }
                }
                $vendors = Product::where('status','=',1)->where('user_id','=',$productt->user_id)->take(8)->get();
            }
            else
            {
                $vendors = Product::where('status','=',1)->where('user_id','=',0)->take(8)->get();
            }
            $data['vendors'] =  $vendors;
            $data['curr'] =  $curr;
            $data['relate_product']['title'] = $this->lang->lang216;
            $data['relate_product']['item'] =   $productt->category->products()->where('status','=',1)->where('id','!=',$productt->id)->take(8)->get();
            // return view('front.product',compact('productt','curr','vendors'));
            if ($data) {
                return response()->success('Success message', 200, $data);
            } else {
                return response()->error('Error message', 'not have parameter');
            }
        }
        return response()->error('Error message', 'not have parameter');
    }

    public function shopSearch (Request $request) {

    }
}
