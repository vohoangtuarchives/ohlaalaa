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
use App\Models\District;
use App\Classes\CometChatHTD;
use Validator;

class ApiMasterController extends Controller
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

    public function __construct() {
    }

    function getOS() {
    }

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

    public function ward($id = null)
    {
        $data = [];
        if ($id != null && $id != '') {
            $data = Ward::findOrFail($id);
        } else {
            $data = Ward::all();
        }

        if ($data) {
            return response()->success('Success message', 200, $data);
        } else {
            return response()->error('Error message', 'not have parameter');
        }
    }

    public function district($id = null)
    {
        $data = [];
        if ($id != null && $id != '') {
            $data = Ward::Where('district_id', '=', $id)->get();
        } else {
            $data = District::all();
        }

        if ($data) {
            return response()->success('Success message', 200, $data);
        } else {
            return response()->error('Error message', 'not have parameter');
        }
    }


    public function province($id = null)
    {
        $data = [];
        if ($id != null && $id != '') {
            $data = District::Where('province_id', '=', $id)->get();
        } else {
            $data = Province::all();
        }

        if ($data) {
            return response()->success('Success message', 200, $data);
        } else {
            return response()->error('Error message', 'not have parameter');
        }
    }

    public function country()
    {
        $data =  DB::table('countries')->get();

        if ($data) {
            return response()->success('Success message', 200, $data);
        } else {
            return response()->error('Error message', 'not have parameter');
        }
    }

}
