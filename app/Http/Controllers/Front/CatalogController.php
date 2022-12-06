<?php

namespace App\Http\Controllers\Front;

use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Reply;
use App\Models\Rating;
use App\Models\Report;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Subcategory;
use App\Models\ProductClick;
use Illuminate\Http\Request;
use App\Classes\CometChatHTD;
use App\Models\Childcategory;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;


class CatalogController extends Controller
{

    // CATEGORIES SECTOPN
    const STATUS    = 1;
    const PAGINATE  = 12;

    public function categories()
    {
        return view('front.categories');
    }

    // -------------------------------- CATEGORY SECTION ----------------------------------------
    //
    // public function filteredProducts(Request $request, $slug=null, $slug1=null, $slug2=null)
    // {
    //
    //
    //   return $products;
    // }

    // -------------------------------- CATEGORY SECTION ----------------------------------------

    public function category(Request $request, $slug=null, $slug1=null, $slug2=null)
    {
        //  dd($request->all(),$slug,$slug1);
        if (Session::has('currency'))
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        $cat = null;
        $subcat = null;
        $childcat = null;
        $minprice = $request->min;
        $maxprice = $request->max;
        $sort = $request->sort;
        $search = $request->search;
        $minprice = round(($minprice / $curr->value),2);
        $maxprice = round(($maxprice / $curr->value),2);
        $location = $request->search_location;



        if (!empty($slug)) {
            $cat = Category::where('slug', $slug)->first();
            if(isset($cat)){
                $data['cat'] = $cat;
            }
        }

        if(isset($request->search) && $request->search !='') {
            $arr =  explode(':', $request->search);
            $arr = array_map('trim', $arr);
            if (count($arr) < 2) {
                return redirect()->route('front.index');
            } else  {
                $search = $arr[1];
                if(strlen(trim($search)) == 0 && $cat == null ){
                    return redirect()->route('front.index');
                }
            }
        }



        if (!empty($slug1)) {
            $subcat = Subcategory::where('slug', $slug1)->firstOrFail();
            $data['subcat'] = $subcat;
        }
        if (!empty($slug2)) {
            $childcat = Childcategory::where('slug', $slug2)->firstOrFail();
            $data['childcat'] = $childcat;
        }

        $prods = Product::
            when($cat, function ($query, $cat) {
                if($cat == null)
                    return $query;
                return $query->where('category_id', $cat->id);
            })
            ->when($subcat, function ($query, $subcat) {
                if($subcat == null)
                    return $query;
                return $query->where('subcategory_id', $subcat->id);
            })
            ->when($childcat, function ($query, $childcat) {
                if($childcat == null)
                    return $query;
                return $query->where('childcategory_id', $childcat->id);
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
                // return $query->join('users as shop', 'products.user_id', '=', 'shop.id')->where('CityID', '=', $location);
                return $query->whereRaw('products.user_id IN  (SELECT id FROM users where `CityID` =  '.$location.')');
            })
            //task-1, thannd, fixed data search
            ->when($search, function ($query, $search) {
                return $query->where(  DB::raw('LOWER(products.name)'), 'like', DB::raw("CONCAT('%', CONVERT(LOWER('".$search."'), BINARY), '%')"));
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
        //$prods = (new Collection($prods))->paginate(12);
        $prods = (new Collection(Product::filterProducts($prods)))->paginate(static::PAGINATE);

        $data['prods'] = $prods;
        // $data['total_page'] = (int) ceil($prods->total() / $prods->perPage());
        $data['total_page']   =  $prods->lastPage();
        $data['action'] = url("/").'/category?search=' . $search;

        $returnHTML = '';
        if($request->ajax()) {
            $data['ajax_check'] = 1;
            if (count($prods)>0) {
                $returnHTML = view('includes.product.filtered-products', $data)->render();
            }
            return  $returnHTML;
            // return response()->json(array('success' => true, 'html'=>$returnHTML));
            // return view('includes.product.filtered-products', $data);
        }
        return view('front.category', $data);
    }

    public function getsubs(Request $request) {
      $category = Category::where('slug', $request->category)->firstOrFail();
      $subcategories = Subcategory::where('category_id', $category->id)->get();
      return $subcategories;
    }


    // -------------------------------- PRODUCT DETAILS SECTION ----------------------------------------

    public function report(Request $request)
    {

        //--- Validation Section
            $rules = [
                   'note' => 'max:400',
                    ];
            $customs = [
                   'note.max' => 'Note Must Be Less Than 400 Characters.',
                       ];
            $validator = Validator::make($request->all(), $rules, $customs);
            if ($validator->fails()) {
              return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Report;
        $input = $request->all();
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }

    public function product($slug)
    {
        $this->code_image();
        $productt = Product::where('slug','=',$slug)->firstOrFail();
        if($productt->status == 0){
          return response()->view('errors.404')->setStatusCode(404);
        }
        $productt->views+=1;
        $productt->update();
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

        if($productt->user_id != 0)
        {
            if(Auth::guard('web')->check())
            {
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
        return view('front.product',compact('productt','curr','vendors'));
    }

    public function search_data()
    {
        $p = Product::where('status','>',0)->get();

        $prods = (new Collection(Product::filterProducts($p)));
        $products = $prods->pluck('name')->unique();

        //dd([count($products), count(array_unique($products))]);
        // $products = Product::where('status','>',0)->select('name')->distinct()->pluck('name');

        $vendors = User::where('is_vendor','=',2)->select('name', 'email'
            , DB::raw("REPLACE(shop_name,' ','-') as shop_name"))->get();
        $data[0] = $vendors;
        $data[1] = $products;

        return response()->json($data);
    }

    public function search_data_by_key($keyword = null)
    {
        if($keyword == null){
            return response()->json(['error' => 'null key word']);
        }

        // $p = Product::where('status','>',0)
        //     ->where('name', 'like', '%'.$keyword.'%')
        //     ->limit(20)
        //     ->get();

        $p  = DB::select(DB::raw("SELECT * FROM products WHERE status > 0
            AND LOWER(name) LIKE CONCAT('%', CONVERT(LOWER(:keyword), BINARY), '%')
            ORDER BY updated_at desc
            LIMIT 20"), array(
            'keyword' => $keyword
        ));

        // $prods = (new Collection(Product::filterProducts($p)));
        $prods = (new Collection($p));
        $products = $prods->pluck('name','slug')->unique();
        $vendors = User::where('is_vendor','=',2)
            ->where(function ($q) use ($keyword) {
                $q->where('shop_name' , 'like', '%'.$keyword.'%')
                    ->orWhere('email' , 'like', '%'.$keyword.'%');
            })
            ->limit(3)
            ->select('shop_name as name', 'email'
            , DB::raw("REPLACE(shop_name,' ','-') as shop_name"))
            ->get();
        $data[0] = $vendors;
        $data[1] = $products;

        return response()->json($data);
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

    public function quick($id)
    {
        $product = Product::findOrFail($id);
        if (Session::has('currency'))
        {
            $curr = Currency::find(Session::get('currency'));
        }
        else
        {
            $curr = Currency::where('is_default','=',1)->first();
        }
        return view('load.quick',compact('product','curr'));

    }

    public function affProductRedirect($slug)
    {
        $product = Product::where('slug','=',$slug)->first();
//        $product->views+=1;
//        $product->update();


        return redirect($product->affiliate_link);

    }
    // -------------------------------- PRODUCT DETAILS SECTION ENDS----------------------------------------



    // -------------------------------- PRODUCT COMMENT SECTION ----------------------------------------

        public function comment(Request $request)
        {
            $comment = new Comment;
            $input = $request->all();
            $comment->fill($input)->save();
            $comments = Comment::where('product_id','=',$request->product_id)->get()->count();
            $data[0] = $comment->user->photo ? url('assets/images/users/'.$comment->user->photo):url('assets/images/noimage.png');
            $data[1] = $comment->user->name;
            $data[2] = $comment->created_at->diffForHumans();
            $data[3] = $comment->text;
            $data[4] = $comments;
            $data[5] = route('product.comment.delete',$comment->id);
            $data[6] = route('product.comment.edit',$comment->id);
            $data[7] = route('product.reply',$comment->id);
            $data[8] = $comment->user->id;
            return response()->json($data);
        }

        public function commentedit(Request $request,$id)
        {
            $comment =Comment::findOrFail($id);
            $comment->text = $request->text;
            $comment->update();
            return response()->json($comment->text);
        }

        public function commentdelete($id)
        {
            $comment =Comment::findOrFail($id);
            if($comment->replies->count() > 0)
            {
                foreach ($comment->replies as $reply) {
                    $reply->delete();
                }
            }
            $comment->delete();
        }

    // -------------------------------- PRODUCT COMMENT SECTION ENDS ----------------------------------------

    // -------------------------------- PRODUCT REPLY SECTION ----------------------------------------

        public function reply(Request $request,$id)
        {
            $reply = new Reply;
            $input = $request->all();
            $input['comment_id'] = $id;
            $reply->fill($input)->save();
            $data[0] = $reply->user->photo ? url('assets/images/users/'.$reply->user->photo):url('assets/images/noimage.png');
            $data[1] = $reply->user->name;
            $data[2] = $reply->created_at->diffForHumans();
            $data[3] = $reply->text;
            $data[4] = route('product.reply.delete',$reply->id);
            $data[5] = route('product.reply.edit',$reply->id);
            return response()->json($data);
        }

        public function replyedit(Request $request,$id)
        {
            $reply = Reply::findOrFail($id);
            $reply->text = $request->text;
            $reply->update();
            return response()->json($reply->text);
        }

        public function replydelete($id)
        {
            $reply =Reply::findOrFail($id);
            $reply->delete();
        }

    // -------------------------------- PRODUCT REPLY SECTION ENDS----------------------------------------


    // ------------------ Rating SECTION --------------------

        public function reviewsubmit(Request $request)
        {
            $ck = 0;
            $orders = Order::where('user_id','=',$request->user_id)->where('status','=','completed')->get();

            foreach($orders as $order)
            {
            $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
                foreach($cart->items as $product)
                {
                    if($request->product_id == $product['item']['id'])
                    {
                        $ck = 1;
                        break;
                    }
                }
            }
            if($ck == 1)
            {
                $user = Auth::guard('web')->user();
                $prev = Rating::where('product_id','=',$request->product_id)->where('user_id','=',$user->id)->get();
                if(count($prev) > 0)
                {
                return response()->json(array('errors' => [ 0 => 'You Have Reviewed Already.' ]));
                }
                $Rating = new Rating;
                $Rating->fill($request->all());
                $Rating['review_date'] = date('Y-m-d H:i:s');
                $Rating->save();
                $data[0] = 'Your Rating Submitted Successfully.';
                $data[1] = Rating::rating($request->product_id);
                return response()->json($data);
            }
            else{
                return response()->json(array('errors' => [ 0 => 'Buy This Product First' ]));
            }
        }


        public function reviews($id){
            $productt = Product::find($id);
            return view('load.reviews',compact('productt','id'));

        }

    // ------------------ Rating SECTION ENDS --------------------
}
