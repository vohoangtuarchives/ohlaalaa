<?php

namespace App\Http\Controllers\Front;

use App\Classes\GeniusMailer;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Category;
use App\Models\Language;
use App\Models\Rating;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
class VendorController extends Controller
{
    const STATUS = 1;
    const IS_VENDOR = 2;
    const PAGINATE  = 12;

    public function index(Request $request, $category, $slug1=-1, $slug2=-1, $slug3=-1)
    {
        // $this->code_image();
        // $sort = "";
        $minprice = $request->min;
        $maxprice = $request->max;
        $sort = $request->sort;

        $vendor = User::where('shop_url','=',strtolower($category))->first();

        if(!isset($vendor->id)){
            $string = str_replace('-',' ', $category);
            $vendor = User::where('shop_name','=',$string)->firstOrFail();
        }

        $data['vendor'] = $vendor;
        $data['services'] = DB::table('services')->where('user_id','=',$vendor->id)->get();
        $data['shop_name'] = $category;

        if (!empty($slug1)) {
            $cat = Category::where('id', $slug1)->first();
            if(isset($cat)){
                $data['cat'] = $cat;
            }
        }
        $prods = Product::when($minprice, function($query, $minprice) {
            return $query->where('price', '>=', $minprice);
        })
        ->when($maxprice, function($query, $maxprice) {
            return $query->where('price', '<=', $maxprice);
        })

        ->when($cat, function ($query, $cat) {
            if($cat == null)
                return $query;
            return $query->where('category_id', $cat->id);
        })
        ->when($slug2, function($query, $slug2) {
        if($slug2 == -1){
            return $query;
        }
        else{
            return $query->where('subcategory_id', '=', $slug2);
        }
        })
        ->when($slug3, function($query, $slug3) {
        if($slug3 == -1){
            return $query;
        }
        else{
            return $query->where('childcategory_id', '=', $slug3);
        }
        })
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
        })
        ->when(empty($sort), function ($query, $sort) {
            return $query->orderBy('products.id', 'DESC');
        })->where('products.status',  static::STATUS)
        ->where('user_id', $vendor->id)->get();

        $prods = (new Collection(Product::filterProducts($prods)))->paginate(static::PAGINATE);

        $data['prods']  = $prods;
        //$data['total_page'] = (int) ceil($prods->total() / $prods->perPage());
        $data['total_page']   =  $prods->lastPage();
        return view('front.vendor', $data);
    }

    public function getProducts (Request $request, $name, $slug1=-1, $slug2=-1, $slug3=-1)
    {


        $minprice = $request->min;
        $maxprice = $request->max;
        $sort = $request->sort;
        $data = Language::where('is_default','=',1)->first();
        $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
        $lang = json_decode($data_results);
        $location = $request->search_location;
        $search = $request->search;
        $string = str_replace('-',' ', $name);
        $vendor = User::where('shop_name','=',$string)->firstOrFail();
        $data['vendor'] = $vendor;
        $data['name'] = $name;
        $data['services'] = DB::table('services')->where('user_id','=',$vendor->id)->get();


        if (!empty($slug1)) {
            $cat = Category::where('id', $slug1)->first();
            if(isset($cat)){
                $data['cat'] = $cat;
            }
        }

        // echo "<pre>"; print_r($category);echo "</pre>";
        // $results = Product::orderBy('id')->paginate(9);
        $prods = Product::when($minprice, function($query, $minprice) {
            return $query->where('price', '>=', $minprice);
        })
        ->when($maxprice, function($query, $maxprice) {
            return $query->where('price', '<=', $maxprice);
        })
        ->when($search, function ($query, $search) {
            return $query->where(  DB::raw('LOWER(products.name)'), 'like', DB::raw("CONCAT('%', CONVERT(LOWER('".$search."'), BINARY), '%')"));
        })
        ->when($cat, function ($query, $cat) {
            if($cat == null)
                return $query;
            return $query->where('category_id', $cat->id);
        })
        ->when($slug2, function($query, $slug2) {
            if($slug2 == -1){
                return $query;
            }
            else{
                return $query->where('subcategory_id', '=', $slug2);
            }
            })
        ->when($slug3, function($query, $slug3) {
        if($slug3 == -1){
            return $query;
        }
        else{
            return $query->where('childcategory_id', '=', $slug3);
        }
        })
        ->when($location, function ($query, $location) {
            if($location == 0)
                return $query;
            // return $query->join('users as shop', 'products.user_id', '=', 'shop.id')->where('CityID', '=', $location);
            return $query->whereRaw('products.user_id IN  (SELECT id FROM users where `CityID` =  '.$location.')');
        })
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
        })
        ->when(empty($sort), function ($query, $sort) {
            return $query->orderBy('products.id', 'DESC');
        })->where('products.status',  static::STATUS)
        ->where('user_id', $vendor->id)->get();

        $prods = (new Collection(Product::filterProducts($prods)))->paginate(static::PAGINATE);

        $html = '';
        $data['prods']  = $prods;
        //$data['total_page'] = (int) ceil($prods->total() / $prods->perPage());
        $data['total_page']   =  $prods->lastPage();

        if ($request->ajax()) {
            if (count($prods) > 0) {
                // foreach ($results as $prod) {
                //     $html .= '<div class="col-lg-4 col-md-4 col-6 remove-padding">';
                //         $html .= '<a href="'. route('front.product', $prod->slug) .'" class="item">';
                //             $this->productImg($prod, $lang, $html);
                //             $this->productInfo($prod, $lang, $html);
                //         $html .= '</a>';
                //     $html .= '</div>';
                // }
                $html = view('includes.product.filtered-products', $data)->render();
            }
            return $html;
        }
        return view('front.vendor', $data);
    }

    //Send email to user
    public function vendorcontact(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $vendor = User::findOrFail($request->vendor_id);
        $gs = Generalsetting::findOrFail(1);
            $subject = $request->subject;
            $to = $vendor->email;
            $name = $request->name;
            $from = $request->email;
            $msg = "Name: ".$name."\nEmail: ".$from."\nMessage: ".$request->message;
        if($gs->is_smtp)
        {
            $data = [
                'to' => $to,
                'subject' => $subject,
                'body' => $msg,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
        }
        else{
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            mail($to,$subject,$msg,$headers);
        }


    $conv = Conversation::where('sent_user','=',$user->id)->where('subject','=',$subject)->first();
        if(isset($conv)){
            $msg = new Message();
            $msg->conversation_id = $conv->id;
            $msg->message = $request->message;
            $msg->sent_user = $user->id;
            $msg->save();
        }
        else{
            $message = new Conversation();
            $message->subject = $subject;
            $message->sent_user= $request->user_id;
            $message->recieved_user = $request->vendor_id;
            $message->message = $request->message;
            $message->save();
            $msg = new Message();
            $msg->conversation_id = $message->id;
            $msg->message = $request->message;
            $msg->sent_user = $request->user_id;;
            $msg->save();

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

    public function  shopList(Request $request, $search)
    {
         // dd($request->all(),$search);

        $sort = $request->sort;
        // $arr =  explode(':', $request->search);
        // $arr = array_map('trim', $arr);
        $location = $request->search_location;

        // if (count($arr) < 2) {
        //     return redirect()->route('front.index');
        // } else  {
        //     $search = $arr[1];
        if(strlen(trim($search)) == 0 ){
            return redirect()->route('front.index');
        }
        // }

        if (!empty($slug1)) {
            $cat = Category::where('id', $slug1)->first();
            if(isset($cat)){
                $data['cat'] = $cat;
            }
        }

        $shops = User::when($search, function ($query, $search) {
            return $query->where(  DB::raw('LOWER(shop_name)'), 'like', DB::raw("CONCAT('%', CONVERT(LOWER('".$search."'), BINARY), '%')"));
        })
        ->when($location, function ($query, $location) {
            if($location == 0)
                return $query;
            // return $query->join('users as shop', 'products.user_id', '=', 'shop.id')->where('CityID', '=', $location);
            return $query->whereRaw('users.id IN  (SELECT id FROM users where `CityID` =  '.$location.')');
        })
        ->when($sort, function ($query, $sort) {
            if ($sort=='date_desc') {
                return $query->orderBy('users.id', 'DESC');
            }
            elseif($sort=='date_asc') {
                return $query->orderBy('users.id', 'ASC');
            }

        })
        ->when(empty($sort), function ($query, $sort) {
            return $query->orderBy('users.id', 'DESC');
        })
        // ->where('products.status',  static::STATUS)
        ->where('is_vendor', static::IS_VENDOR)->get();

        $shops = (new Collection($shops))->paginate(static::PAGINATE);
        $data['shops']  = $shops;

        $data['total_page']   =  $shops->lastPage();
        $data['action'] = url("/").'/shops/' . $search;
        $html = '';
        if ($request->ajax()) {
            if (count($shops) > 0) {
                $html = view('includes.shop.filtered-shop', $data)->render();
            }
            return $html;
        }
        return view('front.shop', $data);
    }


}
