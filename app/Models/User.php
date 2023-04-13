<?php

namespace App\Models;

use DateTime;
use Carbon\Carbon;
use App\Enums\UserRank;
use Illuminate\Support\Str;
use App\Enums\RankingReason;
use App\Classes\GeniusMailer;
use App\Enums\ApprovalStatus;
use App\Enums\RankingLogType;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    protected $fillable = ['name', 'photo', 'zip', 'residency', 'city', 'country', 'address', 'phone', 'fax', 'email','password'
        ,'affilate_code','verification_link','shop_name','owner_name','shop_number','shop_address','reg_number','shop_message'
        ,'is_vendor','shop_details','shop_image','f_url','g_url','t_url','l_url','f_check','g_check','t_check','l_check'
        ,'shipping_cost','date','mail_sent','CityID','DistrictID','ward_id'
        ,'shopping_point','reward_point','referral_code','referral_user_id','referral_user_ids'
        , 'api_token'
        , 'BankAccountName'
        , 'BankName'
        , 'BankAccountNumber'
        , 'BankAddress'
        , 'ranking'
        , 'ranking_end_date'
        , 'ranking_ad_applied'
        , 'ranking_purchased'
        , 'ranking_purchased_end_date'
        , 'vendor_subscription'
        , 'comet_token'
        , 'comet_note'
        , 'vendor_from'
        , 'kol'
        , 'shop_url'
    ];


    protected $hidden = [
        'password', 'remember_token'
    ];

    public function cal_rank()
    {
        if($this->checkExpiredMembership()){
            return UserRank::Regular;
        }
        else{
            if($this->checkPlatinumMember()){
                return UserRank::Platinum;
            }
            else if($this->checkGoldMember()){
                return UserRank::Gold;
            }
            else{
                return UserRank::Premium;
            }
        }
    }

    public function checkDownRank()
    {
        if($this->ranking != UserRank::Regular){
            if($this->checkExpiredMembership()){
                $this->downgradeMember(UserRank::Regular, RankingReason::Expired);
            }
            else{
                if($this->ranking != UserRank::Premium){
                    if(!$this->checkGoldMember()){
                        if($this->ranking_ad_applied == UserRank::Auto)
                            $this->downgradeMember(UserRank::Premium, RankingReason::NotEnoughPremium);
                    }
                    else{
                        if(!$this->checkPlatinumMember()){
                            if($this->ranking_ad_applied == UserRank::Auto)
                                $this->downgradeMember(UserRank::Gold, RankingReason::NotEnoughGold);
                        }
                    }
                }
            }
        }
    }

    public function checkUpRank()
    {
        if($this->ranking != UserRank::Regular){
            if($this->checkExpiredMembership()){
                $this->downgradeMember(UserRank::Regular, RankingReason::Expired);
            }
            else{
                if($this->checkGoldMember()){
                    if($this->checkPlatinumMember()){
                        if($this->ranking_ad_applied == UserRank::Auto)
                            $this->upgradeMember(UserRank::Platinum, RankingReason::EnoughPremium);
                    }
                    else{
                        if($this->ranking_ad_applied == UserRank::Auto)
                            $this->upgradeMember(UserRank::Gold, RankingReason::EnoughGold);
                    }
                }
            }
        }
    }

    public function checkGoldMember()
    {
        $members_count = User::where('referral_code', '=', $this->affilate_code)
            ->whereIn('ranking', [UserRank::Premium, UserRank::Gold, UserRank::Platinum])
            ->orWhereIn('ranking_purchased', [UserRank::Gold, UserRank::Platinum])
            ->count();
        return $members_count >= 10;
    }

    public function checkPlatinumMember()
    {
        $members_count = User::where('referral_code', '=', $this->affilate_code)
        ->whereIn('ranking', [UserRank::Gold, UserRank::Platinum])
        ->orWhereIn('ranking_purchased', [UserRank::Gold, UserRank::Platinum])
        ->count();
        return $members_count >= 10;
    }

    public function checkExpiredMembership()
    {
        $now = Carbon::now();
        return $now > $this->ranking_end_date && $now > $this->ranking_purchased_end_date;
    }

    public function getRankingStartDate()
    {
        return $this->member_packages->where('approval_status','=', ApprovalStatus::Approved)->max('approval_at');
    }

    public function getRankingEndDate()
    {
        return $this->ranking_purchased_end_date > $this->ranking_end_date ? $this->ranking_purchased_end_date : $this->ranking_end_date;
    }

    public function downgradeMember($rank, $reason)
    {
        $log = new UserRankingLog;
        $log->user_id = $this->id;
        $log->ranking_log_type = RankingLogType::Downgrade;
        $log->reason = $reason;
        $log->from_rank =UserRank::getKey($this->ranking);
        $log->to_rank = UserRank::getKey($rank);
        $log->user_issuer = $this->id;
        $this->ranking = $rank;
        if($reason == RankingReason::Expired){
            $this->ranking_ad_applied = UserRank::Auto;
            $this->ranking_purchased = UserRank::Auto;
        }
        $this->save();
        $log->save();
        $referral = $this->referral_user($this->referral_code);
        if(isset($referral)){
            $referral->checkDownRank();
        }
        return $this;
    }

    public function upgradeMember($rank, $reason, $admin_issuer_id=null)
    {
        $log = new UserRankingLog;
        $log->user_id = $this->id;
        $log->ranking_log_type = RankingLogType::Upgrade;
        $log->reason = $reason;
        $log->from_rank =UserRank::getKey($this->ranking);
        $log->to_rank = UserRank::getKey($rank);
        $log->user_issuer = $this->id;
        $log->admin_issuer = $admin_issuer_id;
        $this->ranking = $rank;
        $this->save();
        $log->save();
        $referral = $this->referral_user($this->referral_code);
        if(isset($referral)){
            $referral->checkUpRank();
        }
        return $this;
    }

    public function upgrade_premium($admin_issuer_id, $user_issuer_id, $mpr_id){
        $log = new UserRankingLog;
        $log->user_id = $this->id;
        $log->admin_issuer = $admin_issuer_id;
        $log->user_issuer = $user_issuer_id;
        $log->member_package_register_id = $mpr_id;
        $log->reason = RankingReason::BuyingPackage;
        $log->rank_current_date = $this->ranking_end_date;
        $this->ranking_end_date = $this->cal_ranking_end_date(' +1 year');
        $log->rank_end_date = $this->ranking_end_date;
        if ($this->ranking == UserRank::Regular) {
            $log->ranking_log_type = RankingLogType::Upgrade;
            $log->from_rank = UserRank::getKey(UserRank::Regular);
            $this->ranking = $this->cal_rank();
            $log->to_rank = UserRank::getKey($this->ranking);
            $this->save();
            $log->save();
            $this->checkUpRank();
        }
        else{
            $log->ranking_log_type = RankingLogType::Extend;
            $log->from_rank = UserRank::getKey($this->ranking);
            $log->to_rank = UserRank::getKey($this->ranking);
            $log->save();
        }
        return true;
    }

    public function upgrade_gold($admin_issuer_id, $user_issuer_id, $mpr_id){
        $log = new UserRankingLog;
        $log->user_id = $this->id;
        $log->admin_issuer = $admin_issuer_id;
        $log->user_issuer = $user_issuer_id;
        $log->member_package_register_id = $mpr_id;
        $log->reason = RankingReason::BuyingPackage;
        $log->rank_current_date = $this->ranking_end_date;
        $this->ranking_end_date = $this->new_ranking_end_date(' +1 year');
        $log->rank_end_date = $this->ranking_end_date;
        $log->ranking_log_type = RankingLogType::Upgrade;
        $log->from_rank = UserRank::getKey($this->get_rank());
        $this->ranking_purchased = UserRank::Gold;
        $this->ranking_purchased_end_date = $this->ranking_end_date;
        $log->to_rank = UserRank::getKey(UserRank::Gold);
        $this->save();
        $log->save();
        if($this->checkPlatinumMember()){
            if($this->ranking_ad_applied == UserRank::Auto)
                $this->upgradeMember(UserRank::Platinum, RankingReason::EnoughPremium);
        }
        else{
            $referral = $this->referral_user($this->referral_code);
            if(isset($referral)){
                $referral->checkUpRank();
            }
        }
        return true;
    }

    public function upgrade_platinum($admin_issuer_id, $user_issuer_id, $mpr_id){
        $log = new UserRankingLog;
        $log->user_id = $this->id;
        $log->admin_issuer = $admin_issuer_id;
        $log->user_issuer = $user_issuer_id;
        $log->member_package_register_id = $mpr_id;
        $log->reason = RankingReason::BuyingPackage;
        $log->rank_current_date = $this->ranking_end_date;
        $this->ranking_end_date = $this->new_ranking_end_date(' +1 year');
        $log->rank_end_date = $this->ranking_end_date;
        $log->ranking_log_type = RankingLogType::Upgrade;
        $log->from_rank = UserRank::getKey($this->get_rank());
        $this->ranking_purchased = UserRank::Platinum;
        $this->ranking_purchased_end_date = $this->ranking_end_date;
        $log->to_rank = UserRank::getKey(UserRank::Platinum);
        $this->save();
        $log->save();
        $referral = $this->referral_user($this->referral_code);
        if(isset($referral)){
            $referral->checkUpRank();
        }
        return true;
    }

    public function get_rank()
    {
        if($this->ranking_ad_applied != UserRank::Auto)
            return $this->ranking_ad_applied;
        if($this->ranking > $this->ranking_purchased)
            return $this->ranking;
        return $this->ranking_purchased;
    }

    public function is_higher_rank($newRank)
    {
        switch($this->ranking){
            case UserRank::Regular: return $newRank != UserRank::Auto && $newRank != UserRank::Regular;
            case UserRank::Premium: return $newRank == UserRank::Gold || $newRank == UserRank::Platinum;
            case UserRank::Gold: return $newRank == UserRank::Platinum;
            default: return false;
        }
    }

    public function new_ranking_end_date($sInterval)
    {
        $dateString = date("Y-m-d H:m:s");
        $end = date("Y-m-d H:m:s", strtotime($dateString . ' '.$sInterval));
        return $end;
    }

    public function cal_ranking_end_date($sInterval)
    {
        $dateString = date("Y-m-d H:m:s");
        if($this->ranking_end_date != null){
            $dt2 = Carbon::parse($this->ranking_end_date);
            if($dt2->gt(Carbon::now())){
                $dateString = date_format(new DateTime($this->ranking_end_date),"Y-m-d H:m:s");
            }
        }
        $end = date("Y-m-d H:m:s", strtotime($dateString . ' '.$sInterval));
        return $end;
    }

    public function check_subs_plan_expired()
    {
        $now = Carbon::now();
        return $now > $this->date;
    }

    public function cal_subscription_end_date($sInterval)
    {
        $dateString = date("Y-m-d");
        if($this->date != null){
            $dt2 = Carbon::createFromFormat('Y-m-d', $this->date);
            if($dt2->gt(Carbon::now())){
                $dateString = date_format(new DateTime($this->date),"Y-m-d");
            }
        }
        $end = date("Y-m-d", strtotime($dateString . ' '.$sInterval));
        return $end;
    }

    public function rank_name()
    {
        return UserRank::getDescription($this->get_rank());
    }

    public function rank_display_date()
    {
        return $this->ranking_end_date != null ? Carbon::parse($this->ranking_end_date)->format('d-m-Y') : '';
    }

    public function IsVendor(){
        if ($this->is_vendor == 2) {
           return true;
        }
        return false;
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    public function ratings()
    {
        return $this->hasMany('App\Models\Rating');
    }

    public function wishlists()
    {
        return $this->hasMany('App\Models\Wishlist');
    }

    public function socialProviders()
    {
        return $this->hasMany('App\Models\SocialProvider');
    }

    public function withdraws()
    {
        return $this->hasMany('App\Models\Withdraw');
    }

    public function conversations()
    {
        return $this->hasMany('App\Models\AdminUserConversation');
    }

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification');
    }

    // Multi Vendor

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function services()
    {
        return $this->hasMany('App\Models\Service');
    }

    public function senders()
    {
        return $this->hasMany('App\Models\Conversation','sent_user');
    }

    public function recievers()
    {
        return $this->hasMany('App\Models\Conversation','recieved_user');
    }

    public function notivications()
    {
        return $this->hasMany('App\Models\UserNotification','user_id');
    }

    public function subscribes()
    {
        return $this->hasMany('App\Models\UserSubscription');
    }

    public function member_packages()
    {
        return $this->hasMany('App\Models\MemberPackageRegister','user_id');
    }

    public function member_package_submitted()
    {
        return $this->member_packages()->where('approval_status','=', ApprovalStatus::Pending)->get();
    }

    public function favorites()
    {
        return $this->hasMany('App\Models\FavoriteSeller');
    }

    public function favorite_members()
    {
        return $this->hasMany('App\Models\FavoriteSeller','vendor_id');
    }

    public function vendororders()
    {
        return $this->hasMany('App\Models\VendorOrder','user_id');
    }

    public function shippings()
    {
        return $this->hasMany('App\Models\Shipping','user_id');
    }

    public function packages()
    {
        return $this->hasMany('App\Models\Package','user_id');
    }

    public function reports()
    {
        return $this->hasMany('App\Models\Report','user_id');
    }

    public function verifies()
    {
        return $this->hasMany('App\Models\Verification','user_id');
    }

    public function province()
    {
        return $this->belongsTo('App\Models\Province','CityID');
    }

    public function district()
    {
        return $this->belongsTo('App\Models\District','DistrictID');
    }

    public function ward()
    {
        return $this->belongsTo('App\Models\Ward','ward_id');
    }

    public function wishlistCount()
    {
        return \App\Models\Wishlist::where('user_id','=',$this->id)->with(['product'])->whereHas('product', function($query) {
                    $query->where('status', '=', 1);
                 })->count();
    }

    public function checkVerification()
    {
        return count($this->verifies) > 0 ?
        (empty($this->verifies()->where('admin_warning','=','0')->orderBy('id','desc')->first()->status) ? false : ($this->verifies()->orderBy('id','desc')->first()->status == 'Pending' ? true : false)) : false;
    }

    public function checkStatus()
    {
        return count($this->verifies) > 0 ? ($this->verifies()->orderBy('id','desc')->first()->status == 'Verified' ? true : false) :false;
    }

    public function checkWarning()
    {
        return count($this->verifies) > 0 ? ( empty( $this->verifies()->where('admin_warning','=','1')->orderBy('id','desc')->first() ) ? false : (empty($this->verifies()->where('admin_warning','=','1')->orderBy('id','desc')->first()->status) ? true : false) ) : false;
    }

    public function displayWarning()
    {
        return $this->verifies()->where('admin_warning','=','1')->orderBy('id','desc')->first()->warning_reason;
    }

    public function affiliate_members()
    {
        return $this->hasMany('App\Models\User','referral_user_id');
    }

    public function referral_user($referral_code)
    {
        return $this->where('affilate_code','=',$referral_code)->first();
    }

    public function referral_user_ids($referral_code, $result)
    {
        if(!isset($referral_code))
            return $result;
        $user = $this->where('affilate_code','=',$referral_code)->first();
        if(!isset($user)){
            return $result;
        }
        if(!Str::contains($result, strval($user->id)))
        {
            $result = $result.', '.$user->id;
            return $this->referral_user_ids($user->referral_code, $result);
        }
        return $result;
    }

    public static function chekValidation(){

        $settings = Generalsetting::findOrFail(1);
        $lastchk = "";
        if (file_exists(base_path().'/schedule.data')){
            $lastchk = file_get_contents(base_path().'/schedule.data');
        }
        $today = Carbon::now()->format('Y-m-d');
        if ($lastchk < $today || $lastchk == ""){
            $newday = strtotime($today);

            foreach (DB::table('users')->where('is_vendor','=',2)->get() as  $user) {
                $lastday = $user->date;
                $secs = strtotime($lastday)-$newday;
                $days = $secs / 86400;
                if($days <= 5)
                {
                  if($user->mail_sent == 1)
                  {
                    if($settings->is_smtp == 1)
                    {
                        $data = [
                            'to' => $user->email,
                            'type' => "subscription_warning",
                            'cname' => $user->name,
                            'oamount' => "",
                            'aname' => "",
                            'aemail' => "",
                            'onumber' => ""
                        ];
                        $mailer = new GeniusMailer();
                        $mailer->sendAutoMail($data);
                    }
                    else
                    {
                    $headers = "From: ".$settings->from_name."<".$settings->from_email.">";
                    mail($user->email,'Your subscription plan duration will end after five days. Please renew your plan otherwise all of your products will be deactivated.Thank You.',$headers);
                    }
                    DB::table('users')->where('id',$user->id)->update(['mail_sent' => 0]);
                  }
                }
                if($today > $lastday)
                {
                    DB::table('users')->where('id',$user->id)->update(['is_vendor' => 1]);
                }
            }

            $handle = fopen(base_path().'/schedule.data','w+');
            fwrite($handle,$today);
            fclose($handle);
        }
    }


    public function shop_image_filename($fileName){
        $fullPath = public_path().$fileName;
        if (file_exists($fullPath)) {
            return asset($fileName);
        } else {
            return asset('assets/images/products/no-photo.jpg');
        }
    }

     public function show_photo()
    {

        if($this->shop_image!=''){
            if (strncmp($this->shop_image, "Data/", 5) === 0){

                return $this->shop_image_filename('assets/images/'.$this->shop_image);
            }
            else{
                return $this->shop_image_filename('assets/images/vendorbanner/'.$this->shop_image);
            }
        }
        return asset('assets/images/products/no-photo.jpg');
    }

    public function show_banner()
    {
        if(isset($this->shop_image)){
            if (strncmp($this->shop_image, "Data/", 5) === 0){
                // dd($this->shop_image);
                return asset('assets/images/'.$this->shop_image);
            }
            else{
                return asset('assets/images/vendorbanner/'.$this->shop_image);
            }
        }
        return '';
    }

    public function rebate_bonus(){
        $package = PackageConfig::where('name', $this->rank_name())->first();
        if(isset($package)){
            return $package->rebate_bonus;
        }
        return 0;
    }

    public function full_address()
    {
        $result = $this->address;
        $p = $this->province;
        $d = $this->district;
        $w = $this->ward;
        if(isset($p))
            $result = $this->address.', '.$p->name;
        if(isset($w))
            $result = $this->address.', '.$d->name.', '.$p->name;
        if(isset($w))
            $result = $this->address.', '.$w->name.', '.$d->name.', '.$p->name;
        return $result;
    }

    public function categories(){
        return Category::join('products as p', 'p.category_id', '=', 'categories.id')
            ->where('p.user_id','=',$this->id)
            ->select('categories.*')
            ->distinct()
            ->get()
        ;
    }

    public function sub_categories(){
        return Subcategory::join('products as p', 'p.subcategory_id', '=', 'subcategories.id')
            ->where('p.user_id','=',$this->id)
            ->select('subcategories.*')
            ->distinct()
            ->get()
        ;
    }

    public function child_categories(){
        return Childcategory::join('products as p', 'p.childcategory_id', '=', 'childcategories.id')
            ->where('p.user_id','=',$this->id)
            ->select('childcategories.*')
            ->distinct()
            ->get()
        ;
    }

    public function showName() {
        $name = mb_strlen($this->shop_name,'utf-8') > 55 ? mb_substr($this->shop_name,0,55,'utf-8').'...' : $this->shop_name;
        return $name;
    }


}
