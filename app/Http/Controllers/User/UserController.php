<?php

namespace App\Http\Controllers\User;

use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Classes\VNPay;
use App\Models\Alepay;
use App\Models\MemberPackageAlepayTrackLog;
use App\Enums\UserRank;
use App\Models\Currency;
use App\Enums\ModuleCode;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Classes\CometChatHTD;
use App\Classes\GeniusMailer;
use App\Classes\HTDUtils;
use App\Enums\ApprovalStatus;
use App\Models\PackageConfig;
use App\Models\FavoriteSeller;
use App\Models\Generalsetting;
use App\Models\UserSubscription;
use App\Enums\VendorSubscription;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\MemberPackageRegister;
use Illuminate\Support\Facades\Session;
use App\Models\MemberPackageVnpayTrackLog;

class UserController extends Controller
{
    const IS_VENDOR  = '2'; //active
    const PREFERRED  = '1';
    const KOL  = '1';
    const SPECIAL_KOL  = '1';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if(str_contains( request()->headers->get('referer'), 'admin/users')) {
            if (Session::has('kolbonus')) {
                Session::forget('kolbonus');
            }
        }
        $this->checkUser($user->id);
        // $gs = Generalsetting::findOrfail(1);
        // if($user->get_rank() > UserRank::Regular){
        //     $now = Carbon::now();
        //     $expired = HTDUtils::carbonAddDays($now, $gs->subs_notify_remain_days);
        //     $enddate = HTDUtils::carbonGetDate($user->getRankingEndDate());

        //     $register = $user->member_packages()->where('approval_status','=', ApprovalStatus::Pending)->get();


        //     dd([Carbon::now(), $expired, $enddate, HTDUtils::carbonDaysDiff($now, $enddate)]);
        // }
        return view('user.dashboard',compact('user'));
    }

    public function checkToShowMembershipNotification()
    {
        $user = Auth::user();
        $gs = Generalsetting::findOrfail(1);
        if($user->get_rank() > UserRank::Regular){
            $expired = HTDUtils::carbonAddDays(Carbon::now(), $gs->subs_notify_remain_days);
            $enddate = HTDUtils::carbonGetDate($user->getRankingEndDate());
            if($enddate < $expired)
            {
                $register = $user->member_packages()->where('approval_status','=', ApprovalStatus::Pending)->get();
                if($register->count() == 0){
                    return response()->json([1, HTDUtils::carbonDaysDiff(Carbon::now(), $enddate)]);
                }
            }
        }
        return response()->json([0, 0]);
    }

    public function create_comet_user($id)
    {
        $user = User::findOrFail($id);
        $comet_detail = CometChatHTD::create_user($user);
        if($comet_detail['authToken'] != null){
            $user->comet_token = $comet_detail['authToken'];
            $user->save();
        }
        return response()->json($comet_detail['authToken']);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile',compact('user'));
    }

    public function profileupdate(Request $request)
    {
        //--- Validation Section

        $rules =
        [
            'photo' => 'mimes:jpeg,jpg,png,svg',
            'email' => 'unique:users,email,'.Auth::user()->id
        ];


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends
        $input = $request->all();
        $input['CityID'] = $request->province;
        $input['DistrictID'] = $request->district;
        $input['ward_id'] = $request->ward;

        $data = Auth::user();
        if ($file = $request->file('photo'))
        {
            $name = time().str_replace(' ', '', $file->getClientOriginalName());
            $file->move('assets/images/users/',$name);
            if($data->photo != null)
            {
                if (file_exists(public_path().'/assets/images/users/'.$data->photo)) {
                    unlink(public_path().'/assets/images/users/'.$data->photo);
                }
            }
            $input['photo'] = $name;
        }

        if(!empty($data->referral_code))
        {
            $referral_user = $data->referral_user($data->referral_code);
            if(isset($referral_user)){
                $input['referral_user_id'] = $referral_user->id;
                $input['referral_user_ids'] = $data->referral_user_ids($referral_user->referral_code, $referral_user->id);
            }
        }

        $data->update($input);
        $msg = 'Successfully updated your profile';
        return response()->json($msg);
    }

    public function resetform()
    {
        return view('user.reset');
    }

    public function reset(Request $request)
    {
        $user = Auth::user();
        if ($request->cpass){
            if (Hash::check($request->cpass, $user->password)){
                if ($request->newpass == $request->renewpass){
                    $input['password'] = Hash::make($request->newpass);
                }else{
                    return response()->json(array('errors' => [ 0 => 'Confirm password does not match.' ]));
                }
            }else{
                return response()->json(array('errors' => [ 0 => 'Current password Does not match.' ]));
            }
        }
        $user->update($input);
        $msg = 'Successfully change your passwprd';
        return response()->json($msg);
    }


    public function package()
    {
        $sub = null;
        $user = Auth::user();
        $free = $user->subscribes()
            ->where('status', ApprovalStatus::Approved)
            ->where('type', VendorSubscription::Free)
            ->orderBy('id','desc')->first();
        if(isset($free)){
            $sub = Subscription::where('is_default', '=', 1)->where('type', VendorSubscription::Pricing)->orderBy('id','desc')->first();
            if($sub == null){
                $sub = Subscription::where('type', VendorSubscription::Pricing)->orderBy('id','desc')->first();
            }
            if(isset($sub)){
                return $this->vendorrequest($sub->id);
            }
        }
        else{
            $package = $user->subscribes()->where('status', ApprovalStatus::Pending)->orderBy('id','desc')->first();
            if(isset($package)){
                return redirect()->route('user-dashboard')->with('success','Your Vendor Request already submitted! Please wait for approval!');
            }

            $subp = Subscription::where('is_default', '=', 1)->where('type', VendorSubscription::Pricing)->orderBy('id','desc')->limit(1);
            if($subp == null){
                $subp = Subscription::where('type', VendorSubscription::Pricing)->orderBy('id','desc')->limit(1);
            }

            $package = $user->subscribes()->where('status', ApprovalStatus::Approved)->orderBy('id','desc')->first();
            if(isset($package)){
                $subs = $subp->get();
                return view('user.package.index',compact('user','subs','package'));
            }
            $subf = Subscription::where('is_default', '=', 1)->where('type', VendorSubscription::Free)->orderBy('id','desc')->limit(1);
            if($subf == null){
                $subf = Subscription::where('type', VendorSubscription::Free)->orderBy('id','desc')->limit(1);
            }

            $subs = $subf->union($subp)->get();
            $package = $user->subscribes()->where('status',1)->orderBy('id','desc')->first();
            return view('user.package.index',compact('user','subs','package'));
        }
        $subs = Subscription::all();
        $package = $user->subscribes()->where('status',1)->orderBy('id','desc')->first();
        return view('user.package.index',compact('user','subs','package'));
    }

    public function vendorrequest($id)
    {
        $user = Auth::user();
        $package = $user->subscribes()->where('status', ApprovalStatus::Pending)->orderBy('id','desc')->first();
        if(isset($package)){
            return redirect()->route('user-dashboard')->with('success','Yêu cầu của bạn đã được gửi và đang chờ xét duyệt!');
        }
        $subs = Subscription::findOrFail($id);
        $gs = Generalsetting::findOrfail(1);

        $package = $user->subscribes()
            ->where('status',ApprovalStatus::Approved)
            ->where('type',Vendorsubscription::Pricing)
            ->orderBy('id','desc')->first();
        if($gs->reg_vendor != 1)
        {
            return redirect()->back()->with('unsuccess','$gs->reg_vendor != 1');;
        }
        return view('user.package.details',compact('user','subs','package'));
    }

    public function banks()
    {
        return view('load.banks');
    }

    public function member_package()
    {
        $user = Auth::user();
        $register = $user->member_packages()->where('approval_status','=', ApprovalStatus::Pending)->get();
        if($register->count() > 0){
            return redirect()->route('user-dashboard')->with('success','Yêu cầu của bạn đã được gửi và đang chờ xét duyệt!');
        }
        $curr = Currency::where('is_default','=',1)->first();
        $package = PackageConfig::where('name', $user->rank_name())->first();
        $data = PackageConfig::where('id', '<>', $package->id)
            ->get();
        $items = array();

        //add sub higher
        foreach($data as $dt){
            $sub_rank = UserRank::getValue($dt->name);
            if($sub_rank > $user->get_rank()){
                array_push($items, $dt);
                break;
            }
        }

        //add sub extend
        $gs = Generalsetting::findOrfail(1);
        if($user->get_rank() > UserRank::Regular && $user->ranking_ad_applied == UserRank::Auto){
            $expired = HTDUtils::carbonAddDays(Carbon::now(), $gs->subs_notify_remain_days);
            $enddate = HTDUtils::carbonGetDate($user->getRankingEndDate());
            if($enddate < $expired)
            {
                $register = $user->member_packages()->where('approval_status','=', ApprovalStatus::Pending)->get();
                if($register->count() == 0){
                    $extend_package = null;
                    if($user->ranking_purchased == UserRank::Auto){
                        $extend_package = PackageConfig::where('user_rank_id', '=', UserRank::Premium)->first();
                    }
                    else{
                        $extend_package = PackageConfig::where('user_rank_id', '=', $user->get_rank())->first();
                    }
                    array_push($items, $extend_package);
                }
            }
        }

        $subs = collect($items);
        return view('user.ranking.package',compact('user','subs','package','curr'));
    }

    public function member_package_tnc($id)
    {
        $user = Auth::user();
        $register = $user->member_packages()->where('approval_status','=', ApprovalStatus::Pending)->get();
        if($register->count() > 0){
            return redirect()->route('user-dashboard')->with('success','Yêu cầu của bạn đã được gửi và đang chờ xét duyệt!');
        }
        $package = PackageConfig::findOrFail($id);
        return view('user.ranking.tnc',compact('user','package'));
    }

    public function ranking_register(Request $request, $id)
    {
        $user = Auth::user();
        $register = $user->member_packages()->where('approval_status','=', ApprovalStatus::Pending)->get();
        if($register->count() > 0){
            return redirect()->route('user-dashboard')->with('success','Yêu cầu của bạn đã được gửi và đang chờ xét duyệt!');
        }
        $id = intval($id);

        if($request['checked_tnc'] == 0)
            return redirect()->back()->with('unsuccess','Xin hãy chọn vào mục "Tôi đã đọc và đồng ý với các điều khoản"');
        $package = PackageConfig::findOrFail($id);

        if($package != null){
            $register = new MemberPackageRegister;
            $register->user_id = $user->id;
            $register->package_config_id = $package->id;
            $register->package_price = $package->price;
            $register->package_old_end_at = $user->ranking_end_date;
            $register->checked_tnc = $request['checked_tnc'];
            $register->payment_number = ModuleCode::getKey(ModuleCode::AFF).date("YmdHms").strtoupper(Str::random(2));
            $register->payment_status = 'Pending';
            // $register->payment_bank = $request['payment_bank']; // Alepay update after success payment

            $register->save();
            $settings = Generalsetting::findOrFail(1);
            if($settings->is_smtp == 1)
            {
                $data = [
                    'to' => $user->email,
                    'type' => "user_membership_application",
                    'cname' => $user->name,
                    'oamount' => "",
                    'aname' => "",
                    'aemail' => "",
                    'onumber' => "",
                ];
                $mailer = new GeniusMailer();
                $mailer->sendAutoMail($data);
            }
            else
            {
                $headers = "From: ".$settings->from_name."<".$settings->from_email.">";
                mail($user->email,'Request for membership application','You are submitted a package membership. Please wait for us to review your request.',$headers);
            }
            // $vnpay_data = VNPay::createPaymentMembership($register);
            // if($vnpay_data['code'] == '00'){
            //     $ref_url = $vnpay_data['data'];
            //     $vnpay_log = new MemberPackageVnpayTrackLog();
            //     $vnpay_log->save_url($register->id, $ref_url);
            //     return redirect($ref_url);
            // }

            $alepay = new Alepay;
            $result  = $alepay->createPaymentMembership($register);
            if($result->code == '000') {
                $ref_url = $result->checkoutUrl;
                $alepay_log = new MemberPackageAlepayTrackLog();
                session()->put('orderID',$register->id);
                $alepay_log->save_url($register->id, $ref_url);
                return redirect($ref_url);
            }
            Session::flash('unsuccess', 'Hệ thống thanh toán online đang gặp sự cố!');
            return redirect()->back()->withInput();
        }
        return redirect()->back()->with('unsuccess','Oops! Something wrong!');
    }

    public function vendorrequestsub(Request $request)
    {
        $this->validate($request, [
            // 'shop_name'   => 'unique:users',
            'shop_name'   => 'unique:users,shop_name,'.Auth::user()->id,
           ],[
               'shop_name.unique' => 'Tên shop này đã được sử dụng! Vui lòng chọn tên shop khác.'
            ]);
        $user = Auth::user();

        $subs = Subscription::findOrFail($request->subs_id);
        $settings = Generalsetting::findOrFail(1);

        $input = $request->all();
        if($user->is_vendor == 0){
            if(isset($request->province)){
                $input['CityID'] =  $request->province;
                $input['DistrictID'] = $request->district;
                $input['ward_id'] = $request->ward;
            }
        }
        $user->mail_sent = 1;
        $user->update($input);

        $sub = new UserSubscription;
        $sub->user_id = $user->id;
        $sub->subscription_id = $subs->id;
        $sub->title = $subs->title;
        $sub->currency = $subs->currency;
        $sub->currency_code = $subs->currency_code;
        $sub->price = $subs->price;
        $sub->days = $subs->days;
        $sub->allowed_products = $subs->allowed_products;
        $sub->old_end_at = $user->date;
        $sub->details = $subs->details;
        $sub->type = $subs->type;
        $sub->method = 'Free';
        $sub->status = ApprovalStatus::Pending;
        $sub->save();

        if($settings->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "vendor_request",
                'cname' => $user->name,
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'onumber' => "",
            ];
            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($data);
        }
        else
        {
            $headers = "From: ".$settings->from_name."<".$settings->from_email.">";
            mail($user->email,'Request for Vendor Account','Your Request for a Vendor Account sent. Please wait for your request approval.',$headers);
        }

        return redirect()->route('user-dashboard')->with('success','Yêu cầu mở shop đã được gửi thành công!');
    }

    public function vendorrequestsub1(Request $request)
    {
        $this->validate($request, [
            'shop_name'   => 'unique:users',
           ],[
               'shop_name.unique' => 'Tên shop này đã được sử dụng! Vui lòng chọn tên shop khác.'
            ]);

        $user = Auth::user();
        $package = $user->subscribes()->where('status', ApprovalStatus::Pending)->orderBy('id','desc')->first();
        if(isset($package)){
            return redirect()->route('user-dashboard')->with('success','Yêu cầu mở shop đã được gửi thành công!');
        }
        $subs = Subscription::findOrFail($request->subs_id);
        $settings = Generalsetting::findOrFail(1);

        $input = $request->all();

        if($user->is_vendor == 0){
            $input['CityID'] = $request->province;
            $input['DistrictID'] = $request->district;
            $input['ward_id'] = $request->ward;
        }
        $today = Carbon::now()->format('Y-m-d');
        $user->is_vendor = 2;
        $user->date = date('Y-m-d', strtotime($today.' + '.$subs->days.' days'));
        $user->mail_sent = 1;
        $user->update($input);

        $sub = new UserSubscription;
        $sub->user_id = $user->id;
        $sub->subscription_id = $subs->id;
        $sub->title = $subs->title;
        $sub->currency = $subs->currency;
        $sub->currency_code = $subs->currency_code;
        $sub->price = $subs->price;
        $sub->days = $subs->days;
        $sub->allowed_products = $subs->allowed_products;
        $sub->details = $subs->details;
        $sub->method = 'Free';
        $sub->status = 0;
        $sub->save();

        if($settings->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "vendor_accept",
                'cname' => $user->name,
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'onumber' => "",
            ];
            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($data);
        }
        else
        {
            $headers = "From: ".$settings->from_name."<".$settings->from_email.">";
            mail($user->email,'Your Vendor Account Request','Your Vendor Account Requested Successfully. Please wait for your approval.',$headers);
        }
        return redirect()->route('user-dashboard')->with('success','Yêu cầu mở shop đã được gửi thành công!');
    }

    public function favorite($id1=null,$id2=null)
    {
        $fav = new FavoriteSeller();
        $fav->user_id = $id1;
        $fav->vendor_id = $id2;
        $fav->save();
    }

    public function favorites()
    {
        $user = Auth::guard('web')->user();
        $favorites = FavoriteSeller::where('user_id','=',$user->id)->get();
        return view('user.favorite',compact('user','favorites'));
    }

    public function favdelete($id)
    {
        $wish = FavoriteSeller::findOrFail($id);
        $wish->delete();
        return redirect()->route('user-favorites')->with('success','Successfully Removed The Seller.');
    }

    public function checkUser($id)
    {
        $user =  User::select('id')
        ->where('kol', '=', static::KOL)
        ->orWhere('special_kol', '=', static::SPECIAL_KOL)
        ->orWhere('preferred', '=', static::PREFERRED)
        ->pluck('id')->toArray();

        if (in_array($id, $user)) {
            Session::put('kolbonus', $id);
        }
        return;
    }

}
