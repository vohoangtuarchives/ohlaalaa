<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Models\User;
use App\Models\Order;
use App\Enums\UserRank;
use App\Models\UserPointLog;
use App\Models\PackageConfig;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\KolConfig;
use App\Models\KolOrderConsumerLog;
use App\Models\MemberPackageRegister;
use App\Models\UserConvertPointNote;
use App\Models\UserMerchantSaleBonusNote;
use Session;

class ApiUserController extends Controller
{
    const IS_VENDOR  = '2'; //active
    const PREFERRED  = '1';
    const KOL  = '1';
    const RANKING  = '1';
    const EMAIL_VERIFIED = 'Yes';
    const SPECIAL_KOL  = '1';
    const ORDER_COMPLETE = 'completed';
    const ORDER_DECLINED = 'declined';
    const ORDER_PENDING = 'pending';
    const ORDER_DELIVERY = 'on delivery';
    const ORDER_NUMBER = 'O%';

    public function __construct()
    {
        $this->middleware('auth:admin-api');
    }

    //*** POST CONVERT RW TO SP
    public function convertshoppingpoint(Request $request)
    {
        $accounts = User::where('reward_point','>',0)->get();
        $gs = Generalsetting::findOrFail(1);
        $note = new UserConvertPointNote();
        $note->issuer_id = $request->user()->id;
        $note->exchange_rate = $gs->daily_sp_exchange_rate;
        $note->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
        $note->note = 'from rw -> sp';
        $note->save();
        foreach($accounts as $acc){
            $point = $acc->reward_point * $gs->daily_sp_exchange_rate / 100.0;
            $point_exchange = $point;
            // $point_exchange = $point / $gs->sp_vnd_exchange_rate;
            $point_log = new UserPointLog;
            $point_log->user_id = $acc->id;
            $point_log->cpnote_id = $note->id;
            $point_log->log_type = 'Daily Convert';
            $point_log->order_ref_id = 0;
            $point_log->reward_point_balance = $acc->reward_point;
            $point_log->shopping_point_balance = isset($acc->shopping_point) ? $acc->shopping_point : 0;
            $point_log->exchange_rate = $gs->daily_sp_exchange_rate;
            $point_log->note = 'Convert reward point to shopping point';
            $point_log->descriptions = 'Hệ thống tự động chuyển reward point sang shopping point';
            $point_log->reward_point = -$point;
            $point_log->shopping_point = $point_exchange;
            $point_log->daily_sp_exchange_rate = $gs->daily_sp_exchange_rate;
            $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
            $point_log->created_at = '2022-12-15 04:31:18';
            $point_log->updated_at = '2022-12-15 04:31:18';
            $acc->reward_point = $acc->reward_point - $point;
            $acc->shopping_point = $acc->shopping_point + $point_exchange;
            $acc->save();
            $point_log->save();
        }
        $msg = 'Convert shopping point Successfully!';

        $msg =  $msg.' '.$this->send_subs_expire_notification($request);
        $msg =  $msg.' '.$this->send_membership_expire_notification($request);
        return response()->json($msg);
    }
    //*** POST CONVERT RW TO SP ENDS

    //*** POST MERCHANT SALE BONUS CALCULATION
    public function merchantsalebonus(Request $request)
    {
        try {
            $gs = Generalsetting::findOrFail(1);
            $orders = DB::table('orders as o')
                ->where('o.status','=','completed')
                ->where('o.msb_calculated','=','0')
                ->select(
                    'o.id', DB::raw('IFNULL(o.coupon_discount, 0) as coupon_discount'),
                    DB::raw('CASE WHEN o.total_product_final_amount > 0 THEN IFNULL(o.coupon_discount, 0) / o.total_product_final_amount ELSE 0 END as percent_discount'))
                ;

            $shop_revenue = DB::table('vendor_orders')
                ->joinSub($orders, 'o1', function ($join) {
                    $join->on('vendor_orders.order_id', '=', 'o1.id');
                })
                ->join('users as shop', 'vendor_orders.user_id', '=', 'shop.id')
                ->where('shop.referral_user_id','>','0')
                //->join('users as referral', 'shop.referral_user_id', '=', 'referral.id')
                ->select('o1.id as order_id', DB::raw('vendor_orders.qty * vendor_orders.unit_price + vendor_orders.qty * IFNULL(vendor_orders.item_price_shopping_point, 0) as product_amount'),
                    DB::raw('vendor_orders.qty * vendor_orders.unit_price + vendor_orders.qty * IFNULL(vendor_orders.item_price_shopping_point, 0) - IFNULL(vendor_orders.shop_coupon_amount, 0) - IFNULL(vendor_orders.product_final_amount, 0) * IFNULL(o1.percent_discount, 0) as amount'),
                    'shop.referral_user_id','shop.id as shop_id', 'shop.name as shop_name')
                ;

            $shop_total_revenue = DB::query()->fromSub($shop_revenue, 'subquery')
                ->select('order_id', 'shop_id', 'shop_name', 'referral_user_id', DB::raw('SUM(amount) as total_sales'))
                ->groupBy('order_id', 'shop_id', 'shop_name', 'referral_user_id')
                ->get();



            if($shop_total_revenue->count() > 0)
            {
                $note = new UserMerchantSaleBonusNote();
                $note->issuer_id = $request->user()->id;
                $note->merchant_sale_bonus = $gs->merchant_sale_bonus;
                $note->merchant_sale_bonus_in = $gs->merchant_sale_bonus_in;
                $note->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                $note->note = '';
                $note->save();

                foreach($shop_total_revenue as $revenue){
                    $user = User::find($revenue->referral_user_id);
                    if($user->get_rank() != UserRank::Regular){
                        $point_rw = 0;
                        $point_sp = 0;
                        $remark = '';
                        switch($gs->merchant_sale_bonus_in){
                            case 0:
                                $point_rw = $revenue->total_sales * $gs->merchant_sale_bonus / 100.0;
                                $remark = ' reward point';
                                break;
                            case 1:
                                $point_sp = $revenue->total_sales * $gs->merchant_sale_bonus / 100.0;
                                $remark = ' shopping point';
                                // $point_sp = $revenue->total_sales * $gs->merchant_sale_bonus / 100.0 / $gs->sp_vnd_exchange_rate;
                                break;
                        }

                        $point_log = new UserPointLog;
                        $point_log->user_id = $revenue->referral_user_id;
                        $point_log->msbnote_id = $note->id;
                        $point_log->log_type = 'Merchant Sales Bonus';
                        $point_log->order_ref_id = $revenue->order_id;
                        $point_log->reward_point_balance = isset($user->reward_point) ? $user->reward_point : 0;
                        $point_log->shopping_point_balance = isset($user->shopping_point) ? $user->shopping_point : 0;
                        $point_log->exchange_rate = $gs->merchant_sale_bonus;
                        $point_log->note = 'Paid from order ['.$revenue->order_id.']';
                        $point_log->descriptions = 'Bạn được hưởng điểm thưởng'.$remark.' từ doanh số ['.number_format($revenue->total_sales).'] của shop ['.$revenue->shop_name.']';
                        $point_log->reward_point = $point_rw;
                        $point_log->shopping_point = $point_sp;
                        $point_log->shop_id = $revenue->shop_id;
                        $point_log->amount = $revenue->total_sales;
                        $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                        $point_log->merchant_sale_bonus = $gs->merchant_sale_bonus;
                        $point_log->merchant_sale_bonus_in = $gs->merchant_sale_bonus_in;

                        $user->reward_point = $user->reward_point + $point_rw;
                        $user->shopping_point = $user->shopping_point + $point_sp;
                        //return response()->json($user);
                        $order = Order::find($revenue->order_id);
                        $order->msb_calculated = 1;

                        $order->save();
                        $user->save();
                        $point_log->save();
                    }
                }

                $msg = 'MERCHANT SALE BONUS CALCULATE Successfully!';
                return response()->json($msg);
            }

            $msg = 'All sales bonus has calculated!';
            return response()->json($msg);
        }
        catch (\Exception $e){
            return response()->json($e->getMessage());
            // die($e->getMessage());
        }
    }
    //*** POST MERCHANT SALE BONUS CALCULATION ENDS

    public function getuser(Request $request)
    {
        return $request->user();
    }

    public function check_all_memberships(Request $request)
    {
        $members = User::whereIn('ranking',[UserRank::Premium, UserRank::Gold, UserRank::Platinum])->get();
        foreach($members as $m){
            $m->checkDownRank();
        }
        $msg = 'All members rank checked!';
        return response()->json($msg);
    }

    public function send_subs_expire_notification(Request $request)
    {
        $gs = Generalsetting::findOrFail(1);
        $dateString = date("Y-m-d");
        $end = date("Y-m-d", strtotime($dateString . ' + '.$gs->subs_notify_remain_days.' days'));
        $members = User::where('is_vendor',2)
            ->where('date', '=', $end)
            ->get()
        ;

        foreach($members as $m){

            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $m->email,
                    'type' => "vendor_remain_notify",
                    'cname' => $m->name,
                    'oamount' => "",
                    'aname' => "",
                    'aemail' => "",
                    'onumber' => "",
                ];
                $mailer = new GeniusMailer();
                if($mailer->sendAutoMail($data))
                {
                    $stt = "sent";
                }
                else{
                    $stt = "failed";
                }
            }
            else
            {
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($m->email, 'Your Vendor Account Is About To Expire','Your Vendor Account Will Be Expired After '.$gs->subs_notify_remain_days.' day(s). Please Login to your account to make your subscriptions renewal.',$headers);
            }
        }
        $msg = 'All members rank checked!';
        return response()->json($msg);
    }

    public function send_membership_expire_notification(Request $request)
    {
        $gs = Generalsetting::findOrFail(1);
        $dateString = date("Y-m-d");
        $end = date("Y-m-d", strtotime($dateString . ' + '.$gs->subs_notify_remain_days.' days'));
        $members = User::where('ranking', '>', UserRank::Regular)
            ->where('ranking_end_date', '=', $end)
            ->get()
        ;

        foreach($members as $m){

            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $m->email,
                    'type' => "membership_remain_notify",
                    'cname' => $m->name,
                    'oamount' => "",
                    'aname' => "",
                    'aemail' => "",
                    'onumber' => "",
                ];
                $mailer = new GeniusMailer();
                if($mailer->sendAutoMail($data))
                {
                    $stt = "sent";
                }
                else{
                    $stt = "failed";
                }
            }
            else
            {
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($m->email, 'Your Membership Account Is About To Expire','Your Membership Account Will Be Expired After '.$gs->subs_notify_remain_days.' day(s). Please Login to your account to make your member package renewal.',$headers);
            }
        }
        $msg = 'All user membership packages checked!';
        return response()->json($msg);
    }

    //*** KOL CONSUMER BONUS CALCULATION
    public function kolConsumerBonusData($fromDate)
    {
        try {
            $orders = DB::table('orders as o')
                ->where('o.status','=','completed')
                ->where('o.completed_at','>=', $fromDate)
                ->where('o.kolc_calculated','=','0')
                ->select(
                    'o.id', 'o.order_number', 'o.user_id', DB::raw('IFNULL(o.coupon_discount, 0) as coupon_discount'),
                    DB::raw('CASE WHEN o.total_product_final_amount > 0 THEN IFNULL(o.coupon_discount, 0) / o.total_product_final_amount ELSE 0 END as percent_discount'))
                ;

            $revenue = DB::table('vendor_orders')
                ->joinSub($orders, 'o1', function ($join) {
                    $join->on('vendor_orders.order_id', '=', 'o1.id');
                })
                ->join('users as consumer', 'o1.user_id', '=', 'consumer.id')
                ->join('users as referral', 'consumer.referral_user_id', '=', 'referral.id')
                ->join('package_configs as pc', 'pc.user_rank_id', '='
                    , DB::raw('case when referral.ranking > referral.ranking_purchased then referral.ranking else referral.ranking_purchased end'))
                ->crossJoin('generalsettings as gs')
                ->where('consumer.referral_user_id','>','0')
                ->where(function ($q) {
                    $q->where('referral.ranking' , '>', '1')
                        ->orWhere('referral.ranking_purchased' , '>', '1');
                })
                ->select('o1.id as order_id', 'o1.order_number as order_number',
                    DB::raw('vendor_orders.qty * vendor_orders.unit_price + vendor_orders.qty * IFNULL(vendor_orders.item_price_shopping_point, 0) as product_amount'),
                    DB::raw('vendor_orders.qty * vendor_orders.unit_price + vendor_orders.qty * IFNULL(vendor_orders.item_price_shopping_point, 0) - IFNULL(vendor_orders.shop_coupon_amount, 0) - IFNULL(vendor_orders.product_final_amount, 0) * IFNULL(o1.percent_discount, 0) as amount'),
                    'consumer.referral_user_id as l1_id','consumer.id as consumer_id', 'consumer.name as consumer_name', 'consumer.email as consumer_email'
                    , 'referral.name as l1_name'
                    , 'referral.email as l1_email'
                    , 'pc.name as l1_ranking'
                    , 'referral.BankName as l1_bankname'
                    , 'referral.BankAccountName as l1_bankaccount'
                    , 'referral.BankAccountNumber as l1_bankbumber'
                    , 'referral.BankAddress as l1_bankaddress'
                    , 'gs.kol_con_bonus'
                );
            $total_revenue = DB::query()->fromSub($revenue, 'subquery')
                ->select('order_id', 'order_number', 'consumer_id', 'consumer_name', 'consumer_email'
                    , 'l1_id', 'l1_name', 'l1_email', 'l1_ranking'
                    , 'l1_bankname', 'l1_bankaccount', 'l1_bankbumber', 'l1_bankaddress'
                    , DB::raw('SUM(amount) as total_sales')
                    , DB::raw('SUM(amount) * kol_con_bonus / 100.0 as bonus'))
                ->groupBy('order_id', 'order_number', 'consumer_id', 'consumer_name', 'consumer_email'
                    , 'l1_id', 'l1_name', 'l1_email', 'l1_ranking'
                    , 'l1_bankname', 'l1_bankaccount', 'l1_bankbumber', 'l1_bankaddress', 'kol_con_bonus')
                ->get();

            return $total_revenue;
        }
        catch (\Exception $e){
            return response()->json($e->getMessage());
            // die($e->getMessage());
        }
    }

    public function kolcCalculate($fromDate)
    {
        $data = $this->kolConsumerBonusData($fromDate);
        if($data->count() > 0)
        {
            $gs = Generalsetting::findOrFail(1);
            $bonus_rate = $gs->kol_con_bonus / 100.0;
            foreach($data as $revenue){
                $user = User::find($revenue->l1_id);
                if($user->get_rank() != UserRank::Regular){
                    $bonus = $revenue->total_sales * $bonus_rate / 100.0;
                    $point_log = new UserPointLog;
                    $point_log->user_id = $revenue->l1_id;
                    $point_log->log_type = 'KOL Consumer Bonus';
                    $point_log->order_ref_id = $revenue->order_id;
                    $point_log->reward_point_balance = isset($user->reward_point) ? $user->reward_point : 0;
                    $point_log->shopping_point_balance = isset($user->shopping_point) ? $user->shopping_point : 0;
                    $point_log->exchange_rate = $bonus_rate;
                    $point_log->note = 'Paid from order ['.$revenue->order_id.']';
                    $point_log->descriptions = 'Bạn được thưởng KOL từ doanh số ['.number_format($revenue->total_sales).'] của Người mua hàng ['.$revenue->consumer_name.'] - Mã DH ['.$revenue->order_number.']';
                    $point_log->reward_point = 0;
                    $point_log->shopping_point = 0;
                    $point_log->consumer_id = $revenue->consumer_id;
                    $point_log->amount = $revenue->total_sales;
                    $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                    $point_log->amount_bonus = $bonus;
                    $order = Order::find($revenue->order_id);
                    $order->kolc_calculated = 1;
                    $order->save();
                    $point_log->save();
                }
            }

            $msg = 'KOL CONSUMER BONUS CALCULATED Successfully!';
            return response()->json($msg);
        }

        $msg = 'All KOL CONSUMER BONUS has calculated!';
        return response()->json($msg);
    }
    //*** KOL CONSUMER BONUS CALCULATION ENDS

    //*** KOL AFFILIATE BONUS CALCULATION
    public function kolAffiliateBonusData($fromDate, $is_include_renew = 0)
    {
        try {
            $query = DB::table('member_package_registers as r')
                ->join('users as consumer', 'r.user_id', '=', 'consumer.id')
                ->join('users as referral', 'consumer.referral_user_id', '=', 'referral.id')
                ->join('package_configs as pc', 'pc.user_rank_id', '='
                    , DB::raw('case when referral.ranking > referral.ranking_purchased then referral.ranking else referral.ranking_purchased end'))
                ->crossJoin('generalsettings as gs')
                ->where('r.payment_status','=','Completed')
                ->where('r.approval_at','>=', $fromDate)
                ->where('consumer.referral_user_id','>','0')
                ->where('r.kolaff_calculated','=','0')
                ->where(function ($q) {
                    $q->where('referral.ranking' , '>', '1')
                        ->orWhere('referral.ranking_purchased' , '>', '1');
                });

            if(!$is_include_renew){
                $query = $query->where('consumer.kolaff_calculated','=','0');
            }

            $data = $query->select('r.id as mpr_id', 'r.payment_number'
                ,'consumer.id as consumer_id'
                , 'consumer.name as consumer_name'
                , 'consumer.email as consumer_email'
                , 'consumer.referral_user_id as l1_id'
                , 'referral.name as l1_name'
                , 'referral.email as l1_email'
                , 'pc.name as l1_ranking'
                , 'referral.BankName as l1_bankname'
                , 'referral.BankAccountName as l1_bankaccount'
                , 'referral.BankAccountNumber as l1_bankbumber'
                , 'referral.BankAddress as l1_bankaddress'
                , DB::raw('r.package_price')
                , DB::raw('r.package_price * gs.kol_aff_bonus / 100.0 bonus ')
                , 'consumer.kolaff_calculated'
                )
                ->get();
            return $data;
        }
        catch (\Exception $e){
            return response()->json($e->getMessage());
            // die($e->getMessage());
        }
    }

    public function kolAffCalculate($fromDate, $is_include_renew = 0)
    {
        $data = $this->kolAffiliateBonusData($fromDate, $is_include_renew);
        if($data->count() > 0)
        {
            $gs = Generalsetting::findOrFail(1);
            $bonus_rate = $gs->kol_aff_bonus / 100.0;
            foreach($data as $revenue){
                $user = User::find($revenue->l1_id);
                if($user->get_rank() != UserRank::Regular){
                    $bonus = $revenue->package_price * $bonus_rate / 100.0;
                    $point_log = new UserPointLog;
                    $point_log->user_id = $revenue->l1_id;
                    $point_log->log_type = 'KOL Affiliate Bonus';
                    $point_log->order_ref_id = 0;
                    $point_log->mpr_id = $revenue->mpr_id;
                    $point_log->reward_point_balance = isset($user->reward_point) ? $user->reward_point : 0;
                    $point_log->shopping_point_balance = isset($user->shopping_point) ? $user->shopping_point : 0;
                    $point_log->exchange_rate = $bonus_rate;
                    $point_log->note = 'Paid from mpr ['.$revenue->mpr_id.']';
                    $point_log->descriptions = 'Bạn được thưởng KOL từ việc nâng hạng của thành viên ['.$revenue->consumer_name.'] - Mã TT ['.$revenue->payment_number.']';
                    $point_log->reward_point = 0;
                    $point_log->shopping_point = 0;
                    $point_log->consumer_id = $revenue->consumer_id;
                    $point_log->amount = $revenue->package_price;
                    $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                    $point_log->amount_bonus = $bonus;
                    $consumer = User::find($revenue->consumer_id);
                    $consumer->kolaff_calculated = 1;
                    $consumer->save();
                    $order = MemberPackageRegister::find($revenue->mpr_id);
                    $order->kolaff_calculated = 1;
                    $order->save();
                    $point_log->save();
                }
            }

            $msg = 'KOL AFFILIATE BONUS CALCULATED Successfully!';
            return response()->json($msg);
        }

        $msg = 'All KOL AFFILIATE BONUS has calculated!';
        return response()->json($msg);
    }
    //*** KOL AFFILIATE BONUS CALCULATION ENDS

       //*** KOL CONSUMER BONUS CALCULATION
    public function kolConsumerBonusData2Weeks($fromDate)
    {
        ini_set('max_execution_time', '300');
        $gs = Generalsetting::findOrFail(1);
        $config = KolConfig::where('kol_date', '=', $fromDate)->first();
        $package_configs = PackageConfig::findOrFail(2);
        $bonus_affiliate = ($package_configs->price * $gs->kol_aff_bonus) / 100;
        $revenue_l2 =   $config->revenue_l2;
        $arr = explode('-',$fromDate);
        $d = cal_days_in_month(CAL_GREGORIAN, $arr[0], $arr[1]);
        $fromDate = $arr[1].'-'.$arr[0].'-'.'01';
        $toDate   = $arr[1].'-'.$arr[0].'-'.$d;

        try {

            $orders = DB::table('orders as o')
            ->where('o.status','=','completed')
            //->where('o.msb_calculated','=','0')
            ->whereYear('o.completed_at', '=', $arr[1])
            ->whereMonth('o.completed_at', '=', $arr[0])
            ->select(
                'o.id'
                , DB::raw('IFNULL(o.coupon_discount, 0) as coupon_discount')
                , DB::raw('CASE WHEN o.total_product_final_amount > 0 THEN IFNULL(o.coupon_discount, 0) / o.total_product_final_amount ELSE 0 END as percent_discount'));

            $shop_revenue = DB::table('vendor_orders')
            ->joinSub($orders, 'o1', function ($join) {
                $join->on('vendor_orders.order_id', '=', 'o1.id');
            })
            ->join('users as shop', 'vendor_orders.user_id', '=', 'shop.id')
            ->where('shop.referral_user_id','>','0')
            ->where('shop.is_vendor', '=', static::IS_VENDOR)
            ->where('shop.preferred', '=', static::PREFERRED)
            ->having('amount','>=',  $config->avg_amount_order_l1)
            ->select('o1.id as order_id'
                , DB::raw('vendor_orders.qty * vendor_orders.unit_price + vendor_orders.qty * IFNULL(vendor_orders.item_price_shopping_point, 0) - IFNULL(vendor_orders.shop_coupon_amount, 0) - IFNULL(vendor_orders.product_final_amount, 0) * IFNULL(o1.percent_discount, 0) as amount')
                , 'shop.referral_user_id'
                , 'shop.id as shop_id'
                , 'shop.name as shop_name'
                , 'shop.email as rev_email'
                , 'shop.BankName as rev_bankname'
                , 'shop.BankAccountName as rev_bankaccount'
                , 'shop.BankAccountNumber as rev_bankbumber'
                , 'shop.BankAddress as rev_bankaddress'
            );

            $shop_total_revenue = DB::query()->fromSub($shop_revenue, 'subquery')
            ->select('shop_id'
                , 'shop_name'
                , 'rev_email'
                , 'rev_bankname'
                , 'rev_bankaccount'
                , 'rev_bankbumber'
                , 'rev_bankaddress'
                , DB::raw('SUM(amount) as revenue_total_sales')
            )
            ->having('revenue_total_sales', '>', $revenue_l2)
            ->groupBy( 'shop_id');

            $collection = collect($shop_total_revenue->get());
            $plucked = $collection->pluck('shop_id');
            //  dd( $plucked);

           ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $order = DB::table('orders as o')
            ->join('vendor_orders as v', 'o.id', '=', 'v.order_id')
            ->where('o.status', '=', 'completed')
            ->whereYear('o.completed_at', '=', $arr[1])
            ->whereMonth('o.completed_at', '=', $arr[0])
            ->having('amount','>=',  $config->avg_amount_order_l1)
            ->select(
                  'o.id'
                , 'o.user_id'
                // , DB::raw('IFNULL(o.coupon_discount, 0) as coupon_discount')
                // , DB::raw('CASE WHEN o.total_product_final_amount > 0 THEN IFNULL(o.coupon_discount, 0) / o.total_product_final_amount ELSE 0 END as percent_discount')
                , DB::raw("v.qty * v.unit_price + v.qty * IFNULL(v.item_price_shopping_point, 0) - IFNULL(v.shop_coupon_amount, 0) - IFNULL(v.product_final_amount, 0) * IFNULL(o.percent_discount, 0) as amount")
                // DB::raw('CASE WHEN (v.qty * v.unit_price + v.qty * IFNULL(v.item_price_shopping_point, 0) - IFNULL(v.shop_coupon_amount, 0) - IFNULL(v.product_final_amount, 0) * IFNULL(o.percent_discount, 0)) > '.$config->avg_amount_order.' THEN 0 ELSE 1 END as avg_amount_order'),
            );

            ///////////////////////////////////////////////////////////
            $users =DB::table('users as u')
            ->leftjoinSub($order, 'o',  function($join)
            {
               $join->on('u.id', '=', 'o.user_id');

            })
            ->join('users as r', 'r.id', '=', 'u.referral_user_id')
            // ->whereIn('r.id', function($query) {
            //     $query->select('id')
            //     ->from(with(new User)->getTable())
            //     ->where('ranking', '>', static::RANKING)
            //     ->where('kol','=', '1');
            // })
            // ->where('r.kol','=', static::KOL)
            ->select(
                'u.id as user_id',
                'u.referral_user_id as referral_id',
                DB::raw('SUM(o.amount) as total_amount_f_user'),
                DB::raw('COUNT(CASE WHEN o.id IS NOT NULL THEN 1 ELSE NULL END) as total_order_f_user'),
                DB::raw('COUNT(CASE WHEN o.id IS NOT NULL AND u.created_at >= "'.$fromDate.'" AND u.created_at <= "'.$toDate.'" THEN 1 ELSE null END) as total_order_user_new'),
                DB::raw('COUNT(CASE WHEN o.id IS NOT NULL AND  u.created_at < "'.$fromDate.'"  THEN 1 ELSE null END) as total_order_user_exits'),
                DB::raw('COUNT(CASE WHEN  u.email_verified = "yes" AND  u.created_at >= "'.$fromDate.'" AND u.created_at <= "'.$toDate.'" THEN 1 ELSE null END) as user_new'),
                DB::raw('SUM(CASE WHEN o.amount IS NOT NULL AND u.created_at BETWEEN "'.$fromDate.'" AND "'.$toDate.'" THEN o.amount ELSE 0 END) as amount_user_new'),
                DB::raw('SUM(CASE WHEN o.amount IS NOT NULL AND u.created_at < "'.$fromDate.'" THEN o.amount ELSE 0 END) as amount_user_exits'),
            )
            ->groupBy('u.id');


            $shop = DB::table('user_subscriptions as us')
            ->select(
            'user_id','approved_at','subscription_id',
                DB::raw('CASE WHEN title IS NOT NULL  THEN 1 ELSE null END as subscription')
            )
            ->whereYear('us.approved_at', '=', $arr[1])
            ->whereMonth('us.approved_at', '=', $arr[0])
            ->where('us.subscription_id', '=', 11);

            $mp = DB::table('member_package_registers as m')
            ->select(
            'user_id'
            ,'user_id as user_id_package'
            )
            ->whereYear('m.approval_at', '=', $arr[1])
            ->whereMonth('m.approval_at', '=', $arr[0])
            ->where('m.package_config_id', '>=', '2')
            ->where('m.payment_status', '=', 'Completed')
            ->groupBy('user_id');

            $user_plus = DB::query()->fromSub($users, 'u')
                ->leftjoinSub($shop, 's',  function($join)
                {
                $join->on('u.user_id', '=', 's.user_id');

                })
                ->leftjoinSub($mp, 'm',  function($join)
                {
                $join->on('u.user_id', '=', 'm.user_id');

                })
                ->select (
                    'u.user_id'
                    ,'u.referral_id'
                    ,'u.total_amount_f_user'
                    ,'u.user_new'
                    ,'u.amount_user_new'
                    ,'u.amount_user_exits'
                    ,'m.user_id_package'
                    ,'s.subscription_id as shop'
                    ,'u.total_order_f_user'
                    ,'u.total_order_user_new'
                    ,'u.total_order_user_exits'
                );

            $referra = DB::query()->fromSub($user_plus, 'u')
                ->join('users as r', 'u.referral_id', '=', 'r.id')
                ->leftjoinSub($shop_total_revenue, 'revenue',  function($join)
                {
                    $join->on('u.referral_id', '=', 'revenue.shop_id');

                })
                ->where('ranking', '>', static::RANKING)
                ->whereIn('r.id', function($query) use($plucked) {
                    $query->select('id')
                    ->from(with(new User)->getTable())
                    ->whereIn('r.id', $plucked)
                    ->orWhere('kol','=', static::KOL);
                })
                ->select(
                    DB::Raw('IFNULL( `u`.`referral_id` , `shop_id` ) as referral_id')
                    , DB::Raw('IFNULL( `r`.`name` , `revenue`.`shop_name` ) as name')
                    , DB::Raw('IFNULL( `r`.`email` , `revenue`.`rev_email` ) as kol_email')
                    , DB::Raw('IFNULL( `r`.`BankName` , `revenue`.`rev_bankname` ) as kol_bankname')
                    , DB::Raw('IFNULL( `r`.`BankAccountName` , `revenue`.`rev_bankaccount` ) as kol_bankaccount')
                    , DB::Raw('IFNULL( `r`.`BankAccountNumber` , `revenue`.`rev_bankbumber` ) as kol_bankbumber')
                    , DB::Raw('IFNULL( `r`.`BankAddress` , `revenue`.`rev_bankaddress` ) as kol_bankaddress')

                    //  'u.referral_id'
                    //   , 'r.name as name1'
                    // , 'r.email as kol_email'
                    // , 'r.BankName as kol_bankname'
                    // , 'r.BankAccountName as kol_bankaccount'
                    // , 'r.BankAccountNumber as kol_bankbumber'
                    // , 'r.BankAddress as kol_bankaddress'
                    , 'r.special_kol'
                    , DB::raw('SUM(u.user_new) as total_user')
                    , DB::raw('SUM(total_order_f_user) as total_order')
                    , DB::raw('SUM(total_order_user_new) as total_order_user_new')
                    , DB::raw('SUM(amount_user_new) as total_amount_user_new')
                    , DB::raw('SUM(total_order_user_exits ) as total_order_user_exits')
                    , DB::raw('SUM(amount_user_exits) as total_amount_user_exits')
                    , DB::raw('SUM(u.total_amount_f_user) as total_amount')
                    , DB::raw('NULL as bonus')
                    , 'revenue.revenue_total_sales'
                    , DB::raw('NULL as con_bonus')
                    , DB::raw('COUNT(u.shop) as total_new_shop')
                    , DB::raw('COUNT(u.user_id_package) as total_affiliate_member')
                    , DB::raw('COUNT(u.user_id_package)  * '.$bonus_affiliate.' as total_affiliate_bonus')
                    , DB::raw('NULL as vat')
                    , DB::raw('NULL as total_bonus')

                )
                ->orderBy('r.special_kol','desc')
                ->orderBy('revenue.revenue_total_sales','desc')
                ->groupBy('u.referral_id')
                ->get();

            $referra = collect($referra->keyBy('referral_id')->pluck('referral_id'))->combine($referra);

            return $referra;
        }
        catch (\Exception $e){
            die($e->getMessage());
            return response()->json($e->getMessage());

        }
    }


    public function operation($fromDate)
    {
        ini_set('max_execution_time', '300');
        $gs = Generalsetting::findOrFail(1);
        // $config = KolConfig::where('kol_date', '=', $fromDate)->first();
        // $package_configs = PackageConfig::findOrFail(2);
        // $arr = explode('-',$fromDate);
        // $d = cal_days_in_month(CAL_GREGORIAN, $arr[0], $arr[1]);
        // $fromDate = $arr[1].'-'.$arr[0].'-'.'01';
        // $toDate   = $arr[1].'-'.$arr[0].'-'.$d;

        try {
            $user = DB::table('users as u')
            ->where('email_verified', '=', static::EMAIL_VERIFIED)
            ->whereYear('created_at', '>=', $fromDate)
            ->select(DB::raw('COUNT(id) as user_total'))->first();
            $user = json_decode(json_encode($user), true);

            $shop = DB::table('users as u')
            ->where('email_verified', '=', static::EMAIL_VERIFIED)
            ->where('is_vendor', '=', static::IS_VENDOR)
            ->select(
                DB::raw('COUNT(id) as shop_total')
                ,DB::raw('COUNT(CASE WHEN  year(vendor_from) >= "'. $fromDate .'" THEN 1 ELSE NULL END) as shop_date')
            )
            ->first();

            $shop = json_decode(json_encode($shop), true);

            // echo "<pre>";print_r($array);echo "</pre>";
            $order = DB::table('orders as o')
            ->where(function ($query) use ($fromDate) {
                $query->whereYear('completed_at', '>=', $fromDate)
                    ->orwhere(DB::raw("year(payment_to_company_date)"), '>=', $fromDate);
            })
            ->where('order_number', 'like', static::ORDER_NUMBER)
            ->select( DB::raw('COUNT(id) as order_total')
                ,DB::raw('COUNT(CASE WHEN  o.status = "'. static::ORDER_COMPLETE .'" THEN 1 ELSE NULL END) as order_complete')
                ,DB::raw('COUNT(CASE WHEN  o.status = "'. static::ORDER_DECLINED .'" THEN 1 ELSE NULL END) as order_declined')
                ,DB::raw('COUNT(CASE WHEN  o.status = "'. static::ORDER_DELIVERY .'" THEN 1 ELSE NULL END) as order_delivery')
                ,DB::raw('COUNT(CASE WHEN  o.status = "'. static::ORDER_PENDING .'" THEN 1 ELSE NULL END) as order_pending')
            )->first();
            $order = json_decode(json_encode($order), true);

            $order_amount = DB::table('orders as o')
            ->join('vendor_orders as v', 'o.id', '=', 'v.order_id')
            ->where('o.status', '=', static::ORDER_COMPLETE)
            ->where(function ($query) use ($fromDate) {
                $query->whereYear('o.completed_at', '>=', $fromDate)
                    ->orwhere(DB::raw("year(o.payment_to_company_date)"), '>=', $fromDate);
            })
            ->where('o.order_number', 'like', static::ORDER_NUMBER)
            ->select(
                DB::raw("v.qty * v.unit_price + v.qty * IFNULL(v.item_price_shopping_point, 0) - IFNULL(v.shop_coupon_amount, 0) - IFNULL(v.product_final_amount, 0) * IFNULL(o.percent_discount, 0) as amount")
            );

            $order_plus = DB::query()->fromSub($order_amount, 'u')->select( DB::raw("SUM(amount) as total_amount"))->first();
            $order_plus = json_decode(json_encode($order_plus), true);
            $collection = collect([$user, $shop, $order, $order_plus]);
            $collapsed = $collection->collapse();
            $a = $collapsed->all();
            return   collect([$a]);
        }
        catch (\Exception $e){
            die($e->getMessage());
            return response()->json($e->getMessage());

        }
    }

    public function kolConsumerBonusDataForUser($fromDate)
    {
        ini_set('max_execution_time', '300');
        $gs = Generalsetting::findOrFail(1);
        $config = KolConfig::where('kol_date', '=', $fromDate)->first();
        $package_configs = PackageConfig::findOrFail(2);
        $bonus_affiliate = ($package_configs->price * $gs->kol_aff_bonus) / 100;
        $revenue_l2 =   $config->revenue_l2;
        $arr = explode('-',$fromDate);
        $d = cal_days_in_month(CAL_GREGORIAN, $arr[0], $arr[1]);
        $fromDate = $arr[1].'-'.$arr[0].'-'.'01';
        $toDate   = $arr[1].'-'.$arr[0].'-'.$d;
        $user = Auth::user();
        // dd($user->id);
        try {
            $orders = DB::table('orders as o')
                ->where('o.status','=','completed')
                //->where('o.msb_calculated','=','0')
                ->whereYear('o.completed_at', '=', $arr[1])
                ->whereMonth('o.completed_at', '=', $arr[0])
                ->select(
                    'o.id'
                    , DB::raw('IFNULL(o.coupon_discount, 0) as coupon_discount')
                    , DB::raw('CASE WHEN o.total_product_final_amount > 0 THEN IFNULL(o.coupon_discount, 0) / o.total_product_final_amount ELSE 0 END as percent_discount'));

            $shop_revenue = DB::table('vendor_orders')
                ->joinSub($orders, 'o1', function ($join) {
                    $join->on('vendor_orders.order_id', '=', 'o1.id');
                })
                ->join('users as shop', 'vendor_orders.user_id', '=', 'shop.id')
                ->where('shop.referral_user_id','>','0')
                ->where('shop.is_vendor', '=', static::IS_VENDOR)
                ->where('shop.preferred', '=', static::PREFERRED)
                ->having('amount','>=',  $config->avg_amount_order_l1)
                ->select('o1.id as order_id'
                    , DB::raw('vendor_orders.qty * vendor_orders.unit_price + vendor_orders.qty * IFNULL(vendor_orders.item_price_shopping_point, 0) - IFNULL(vendor_orders.shop_coupon_amount, 0) - IFNULL(vendor_orders.product_final_amount, 0) * IFNULL(o1.percent_discount, 0) as amount')
                    , 'shop.referral_user_id'
                    , 'shop.id as shop_id'
                    , 'shop.kol as kol'
                    , 'shop.name as shop_name'
                    , 'shop.email as rev_email'
                    , 'shop.BankName as rev_bankname'
                    , 'shop.BankAccountName as rev_bankaccount'
                    , 'shop.BankAccountNumber as rev_bankbumber'
                    , 'shop.BankAddress as rev_bankaddress'
                );

            $shop_total_revenue = DB::query()->fromSub($shop_revenue, 'subquery')
                ->select('shop_id'
                    , 'shop_name'
                    , 'rev_email'
                    , 'rev_bankname'
                    , 'rev_bankaccount'
                    , 'rev_bankbumber'
                    , 'rev_bankaddress'
                    , DB::raw('SUM(amount) as revenue_total_sales')
                )
                // ->where('shop_id', '=', $user->id)73421
                // ->where('shop_id', '=', '73421')
                // ->where('kol','=', static::KOL)
                // ->having('revenue_total_sales', '>', $revenue_l2)
                ->groupBy( 'shop_id');

            //     $collection = collect($shop_total_revenue->get());
            //     $plucked = $collection->pluck('shop_id');


           ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $order = DB::table('orders as o')
            ->join('vendor_orders as v', 'o.id', '=', 'v.order_id')
            ->where('o.status', '=', 'completed')
            ->whereYear('o.completed_at', '=', $arr[1])
            ->whereMonth('o.completed_at', '=', $arr[0])
            ->having('amount','>=',  $config->avg_amount_order_l1)
            ->select(
                  'o.id'
                , 'o.user_id'
                // , DB::raw('IFNULL(o.coupon_discount, 0) as coupon_discount')
                // , DB::raw('CASE WHEN o.total_product_final_amount > 0 THEN IFNULL(o.coupon_discount, 0) / o.total_product_final_amount ELSE 0 END as percent_discount')
                , DB::raw("v.qty * v.unit_price + v.qty * IFNULL(v.item_price_shopping_point, 0) - IFNULL(v.shop_coupon_amount, 0) - IFNULL(v.product_final_amount, 0) * IFNULL(o.percent_discount, 0) as amount")
                // DB::raw('CASE WHEN (v.qty * v.unit_price + v.qty * IFNULL(v.item_price_shopping_point, 0) - IFNULL(v.shop_coupon_amount, 0) - IFNULL(v.product_final_amount, 0) * IFNULL(o.percent_discount, 0)) > '.$config->avg_amount_order.' THEN 0 ELSE 1 END as avg_amount_order'),
            );

            ///////////////////////////////////////////////////////////
            $users =DB::table('users as u')
            ->leftjoinSub($order, 'o',  function($join)
            {
               $join->on('u.id', '=', 'o.user_id');

            })
            ->join('users as r', 'r.id', '=', 'u.referral_user_id')
            // ->whereIn('r.id', function($query) {
            //     $query->select('id')
            //     ->from(with(new User)->getTable())
            //     ->where('ranking', '>', static::RANKING)
            //     ->where('kol','=', '1');
            // })
            // ->where('r.kol','=', static::KOL)
            ->select(
                'u.id as user_id',
                'u.referral_user_id as referral_id',
                DB::raw('SUM(o.amount) as total_amount_f_user'),
                DB::raw('COUNT(CASE WHEN o.id IS NOT NULL THEN 1 ELSE NULL END) as total_order_f_user'),
                DB::raw('COUNT(CASE WHEN o.id IS NOT NULL AND u.created_at >= "'.$fromDate.'" AND u.created_at <= "'.$toDate.'" THEN 1 ELSE null END) as total_order_user_new'),
                DB::raw('COUNT(CASE WHEN o.id IS NOT NULL AND  u.created_at < "'.$fromDate.'"  THEN 1 ELSE null END) as total_order_user_exits'),
                DB::raw('COUNT(CASE WHEN  u.email_verified = "yes" AND  u.created_at >= "'.$fromDate.'" AND u.created_at <= "'.$toDate.'" THEN 1 ELSE null END) as user_new'),
                DB::raw('SUM(CASE WHEN o.amount IS NOT NULL AND u.created_at BETWEEN "'.$fromDate.'" AND "'.$toDate.'" THEN o.amount ELSE 0 END) as amount_user_new'),
                DB::raw('SUM(CASE WHEN o.amount IS NOT NULL AND u.created_at < "'.$fromDate.'" THEN o.amount ELSE 0 END) as amount_user_exits'),
            )
            ->groupBy('u.id');


            $shop = DB::table('user_subscriptions as us')
            ->select(
            'user_id','approved_at','subscription_id',
                DB::raw('CASE WHEN title IS NOT NULL  THEN 1 ELSE null END as subscription')
            )
            ->whereYear('us.approved_at', '=', $arr[1])
            ->whereMonth('us.approved_at', '=', $arr[0])
            ->where('us.subscription_id', '=', 11);

            $mp = DB::table('member_package_registers as m')
            ->select(
            'user_id'
            ,'user_id as user_id_package'
            )
            ->whereYear('m.approval_at', '=', $arr[1])
            ->whereMonth('m.approval_at', '=', $arr[0])
            ->where('m.package_config_id', '>=', '2')
            ->where('m.payment_status', '=', 'Completed')
            ->groupBy('user_id');

            $user_plus = DB::query()->fromSub($users, 'u')
                ->leftjoinSub($shop, 's',  function($join)
                {
                $join->on('u.user_id', '=', 's.user_id');

                })
                ->leftjoinSub($mp, 'm',  function($join)
                {
                $join->on('u.user_id', '=', 'm.user_id');

                })
                ->select (
                    'u.user_id'
                    ,'u.referral_id'
                    ,'u.total_amount_f_user'
                    ,'u.user_new'
                    ,'u.amount_user_new'
                    ,'u.amount_user_exits'
                    ,'m.user_id_package'
                    ,'s.subscription_id as shop'
                    ,'u.total_order_f_user'
                    ,'u.total_order_user_new'
                    ,'u.total_order_user_exits'
                );

            $referra = DB::query()->fromSub($user_plus, 'u')
                ->join('users as r', 'u.referral_id', '=', 'r.id')
                ->leftjoinSub($shop_total_revenue, 'revenue',  function($join)
                {
                    $join->on('u.referral_id', '=', 'revenue.shop_id');

                })
                ->where('ranking', '>', static::RANKING)
                ->where('r.id', '=', $user->id)
                ->where(function($query)  {
                    $query->where('r.kol', '=', static::KOL)
                    ->orWhere('r.special_kol', '=', static::SPECIAL_KOL)
                    ->orWhere('r.preferred', '=', static::PREFERRED);
                })
                // ->where('r.id', function($query) use($plucked) {
                //     $query->select('id')
                //     ->from(with(new User)->getTable())
                //     ->whereIn('r.id', $plucked)
                //     ->orWhere('kol','=', static::KOL);
                // })
                ->select(
                    DB::Raw('IFNULL( `u`.`referral_id` , `shop_id` ) as referral_id')
                    , DB::Raw('IFNULL( `r`.`name` , `revenue`.`shop_name` ) as name')
                    , DB::Raw('IFNULL( `r`.`email` , `revenue`.`rev_email` ) as kol_email')
                    , DB::Raw('IFNULL( `r`.`BankName` , `revenue`.`rev_bankname` ) as kol_bankname')
                    , DB::Raw('IFNULL( `r`.`BankAccountName` , `revenue`.`rev_bankaccount` ) as kol_bankaccount')
                    , DB::Raw('IFNULL( `r`.`BankAccountNumber` , `revenue`.`rev_bankbumber` ) as kol_bankbumber')
                    , DB::Raw('IFNULL( `r`.`BankAddress` , `revenue`.`rev_bankaddress` ) as kol_bankaddress')
                    , 'r.special_kol'
                    , DB::raw('SUM(u.user_new) as total_user')
                    , DB::raw('SUM(total_order_f_user) as total_order')
                    , DB::raw('SUM(total_order_user_new) as total_order_user_new')
                    , DB::raw('SUM(amount_user_new) as total_amount_user_new')
                    , DB::raw('SUM(total_order_user_exits ) as total_order_user_exits')
                    , DB::raw('SUM(amount_user_exits) as total_amount_user_exits')
                    , DB::raw('SUM(u.total_amount_f_user) as total_amount')
                    , DB::raw('NULL as bonus')
                    , 'revenue.revenue_total_sales'
                    , DB::raw('NULL as con_bonus')
                    , DB::raw('COUNT(u.shop) as total_new_shop')
                    , DB::raw('COUNT(u.user_id_package) as total_affiliate_member')
                    , DB::raw('COUNT(u.user_id_package)  * '.$bonus_affiliate.' as total_affiliate_bonus')
                    , DB::raw('NULL as vat')
                    , DB::raw('NULL as total_bonus')

                )
                ->orderBy('r.special_kol','desc')
                ->orderBy('revenue.revenue_total_sales','desc')
                ->groupBy('u.referral_id')
                ->get();

            $referra = collect($referra->keyBy('referral_id')->pluck('referral_id'))->combine($referra);

            return $referra;
        }
        catch (\Exception $e){
            die($e->getMessage());
            return response()->json($e->getMessage());

        }
    }
}
