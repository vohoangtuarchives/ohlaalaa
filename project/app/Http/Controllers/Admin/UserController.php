<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminTransaction;
use Auth;
use Validator;
use Datatables;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Enums\UserRank;
use App\Models\Currency;
use App\Models\Withdraw;
use App\Classes\HTDUtils;
use App\Enums\RankingReason;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Enums\ApprovalStatus;
use App\Enums\RankingLogType;
use App\Models\PackageConfig;
use App\Models\Generalsetting;
use App\Models\UserRankingLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MemberPackageRegister;
use App\Exports\Users\Reports\SaleReport;
use App\Exports\Users\Reports\KOLAffiliateBonus;
use App\Exports\Users\Reports\KOLAffiliateBonusPaid;
use App\Exports\Users\MemberPackageRegisters\MemberPackageRegisterExcel;
use App\Exports\Users\Reports\SaleReportSummariesTop10SPEarningByAffiliate;
use App\Exports\Users\Reports\SaleReportSummariesTop10RPEarningByMerchantBonus;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables($status, $rank, $from = null, $to = null, $key = null)
    {
        $datas = $this->query($status, $rank, $from, $to, $key)
            ->orderByDesc( 'ranking', 'id');
        if(!isset($from)){
            $datas = $datas->limit(200);
        }
        $datas = $datas->get();

        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('rank_name', function($data){
                return $data->rank_name();
            })
            ->addColumn('end_at', function($data){
                $rs = isset($data['ranking_end_date']) ? Carbon::parse($data['ranking_end_date'])->format('d-m-Y H:m') : '';
                return $rs;
            })
            ->addColumn('rank_ad_name', function($data) {
                $class = $data->ranking_ad_applied == UserRank::Auto ? 'drop-auto' : ($data->ranking_ad_applied == UserRank::Regular ? 'drop-regular' : ($data->ranking_ad_applied == UserRank::Premium ? 'drop-premium' : ($data->ranking_ad_applied == UserRank::Gold ? 'drop-gold' : ('drop-platinum'))));
                $ranks = '<select data-val="" class="process select rank-droplinks '.$class.'">'.
                    '<option value="'. route('admin-user-rank-update',['id1' => $data->id, 'id2' => UserRank::Auto]).'" data-class="drop-regular" data-val="'.UserRank::getKey(UserRank::Auto).'" '.($data->ranking_ad_applied == UserRank::Auto ? 'selected' : '').'>'.UserRank::getKey(UserRank::Auto).'</option>'.
                    '<option value="'. route('admin-user-rank-update',['id1' => $data->id, 'id2' => UserRank::Regular]).'" data-class="drop-regular" data-val="'.UserRank::getKey(UserRank::Regular).'" '.($data->ranking_ad_applied == UserRank::Regular ? 'selected' : '').'>'.UserRank::getKey(UserRank::Regular).'</option>'.
                    '<option value="'. route('admin-user-rank-update',['id1' => $data->id, 'id2' => UserRank::Premium]).'" data-class="drop-premium" data-val="'.UserRank::getKey(UserRank::Premium).'" '.($data->ranking_ad_applied == UserRank::Premium ? 'selected' : '').'>'.UserRank::getKey(UserRank::Premium).'</option>'.
                    '<option value="'. route('admin-user-rank-update',['id1' => $data->id, 'id2' => UserRank::Gold]).'" data-class="drop-gold" data-val="'.UserRank::getKey(UserRank::Gold).'" '.($data->ranking_ad_applied == UserRank::Gold ? 'selected' : '').'>'.UserRank::getKey(UserRank::Gold).'</option>'.
                    '<option value="'. route('admin-user-rank-update',['id1' => $data->id, 'id2' => UserRank::Platinum]).'" data-class="drop-platinum" data-val="'.UserRank::getKey(UserRank::Platinum).'" '.($data->ranking_ad_applied == UserRank::Platinum ? 'selected' : '').'>'.UserRank::getKey(UserRank::Platinum).'</option>'.
                    '</select>';
                return '<div class="action-list">'.$ranks.'</div>';
            })
            ->addColumn('action', function($data) {
                $verify = $data->email_verified == 'No' ? '<a href="javascript:;" data-href="' . route('admin-user-verify',$data->id) . '" data-toggle="modal" data-target="#confirm-verify" class="delete"> <i class="fas fa-user-check"></i> Verify</a>' : '';
                $class = $data->ban == 0 ? 'drop-success' : 'drop-danger';
                $s = $data->ban == 1 ? 'selected' : '';
                $ns = $data->ban == 0 ? 'selected' : '';
                $refresh_rank = '<a data-href="' . route('admin-user-rank-refresh',$data->id) . '" class="refresh-rank" > <i class="fas fa-sync"></i> Refresh Rank</a>';
                $ban = '<select class="process select droplinks '.$class.'">'.
                    '<option data-val="0" value="'. route('admin-user-ban',['id1' => $data->id, 'id2' => 1]).'" '.$s.'>Block</option>'.
                    '<option data-val="1" value="'. route('admin-user-ban',['id1' => $data->id, 'id2' => 0]).'" '.$ns.'>UnBlock</option></select>';

                $kol_checked = ( $data->kol==1 ? 'checked' : '' );
                $kol_special_checked = ( $data->special_kol==1 ? 'checked' : '' );
                $kol = $special_kol = '';
                $transfer_points_checked = ( $data->can_transfer_point == 1 ? 'checked' : '' );
                if ($data->ranking > 1 || $data->ranking_purchased > 1) {
                    $kol = '<div class="form-check form-check-inline">
                    <input type="checkbox" id="'. $data->id.'" value="'.$data->id.'"'.$kol_checked.' >&nbsp
                    <label class="form-check-label" for="kol">KOL</label>
                    </div>';
                    $special_kol = '<div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="'. $data->id.'" value="'.$data->id.'" '.$kol_special_checked.' >
                    <label class="form-check-label" for="special kol">Special KOL</label>
                    </div>';

                }

                $transfer_points_html = '<div class="form-check form-check-inline">
                    <input type="checkbox" id="'. $data->id.'" value="'.$data->id.'"'.$transfer_points_checked.' >&nbsp
                    <label class="form-check-label" for="user_transfer_point">Transfer Point</label>
                    </div>';

                return '<div class="action-list">
                    <a href="' . route('admin-user-secret',$data->id) . '" > <i class="fas fa-user"></i> Secret Login</a>
                    <a href="' . route('admin-user-show',$data->id) . '" > <i class="fas fa-eye"></i> Details</a>
                    <a data-href="' . route('admin-user-edit',$data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a>
                    <a href="javascript:;" class="send" data-email="'. $data->email .'" data-toggle="modal" data-target="#vendorform"><i class="fas fa-envelope"></i> Send</a>'.$ban.$refresh_rank.'
                    <a style="background: #a90000;" href="javascript:;" data-href="' . route('admin-user-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a>'
                    .$verify.
                    '<br><a style="background: #007bff;"  href="javascript:;" data-href="' . route('admin-user-confirm-kol',$data->id) . '" data-toggle="modal" data-target="#confirm-kol" class="delete">
                    '.$kol.'</a>
                    <a style="background: #42a1cf;"  href="javascript:;" data-href="' . route('admin-user-confirm-special-kol',$data->id) . '" data-toggle="modal" data-target="#confirm-special-kol" class="delete">'.$special_kol.'</a>
                    <a style="background: #0724ac;"  href="javascript:;" data-href="' . route('admin-user-confirmTransferPoint',$data->id) . '" data-toggle="modal" data-target="#confirm-transfer-point" class="delete">'.$transfer_points_html.'</a>
                    </div>
                    ';

            })
            ->rawColumns(['action', 'rank_ad_name'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function query($status, $rank, $from = null, $to = null, $key = null)
    {
        $statuss = array($status);
        if($status == -1){
            $statuss = array(0, 1);
        }
        $ranks = array($rank);
        if($rank == -1){
            $ranks = array(UserRank::Regular, UserRank::Premium, UserRank::Gold, UserRank::Platinum);
        }
        $query = User::whereIn('status',$statuss)->whereIn('ranking',$ranks);
        if($key != null){
            $query = $query->where(function ($q) use ($key) {
                $q->where('name' , 'like', '%'.$key.'%')
                    ->orWhere('email' , 'like', '%'.$key.'%');
            });
        }
        if($from != null){
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $query = $query->whereBetween('created_at',[$from, $to]);
        }
        return $query;
    }

    //*** GET Request
    public function index()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.user.index', compact('now'));
    }

    public function memberPackageData($from, $to, $status = -1)
    {
        $to = date("Y-m-d",strtotime($to . '+1 days'));
        $query = DB::table('member_package_registers as m')
            ->join('users as u', 'm.user_id', '=', 'u.id')
            ->join('package_configs as p', 'm.package_config_id', '=', 'p.id')
            ->leftJoin('admins as ada', 'm.approved_by', '=', 'ada.id')
            ->leftJoin('admins as adr', 'm.rejected_by', '=', 'adr.id')
            ->select('u.id as user_id'
                , 'u.name as user_name'
                , 'u.email'
                , 'u.phone'
                , 'u.affilate_code'
                , 'm.id'
                , 'm.package_config_id'
                , 'p.name as package_name'
                , DB::raw('m.created_at')
                , DB::raw('m.package_old_end_at')
                , DB::raw('m.package_new_end_at')
                , 'm.approval_status'
                , 'm.checked_tnc'
                , DB::raw('m.approval_at')
                , DB::raw('m.rejected_at')
                , 'm.payment_status'
                , 'adr.name as rejected_by'
                , 'ada.name as approved_by'
                , 'm.payment_number'
                )
            ->orderByDesc('id');
        $query = $query->where(function ($q)  use ($from, $to){
            $q = $q->whereBetween('m.created_at',[$from, $to])
                ->orWhereBetween('m.approval_at',[$from, $to])
                ->orWhereBetween('m.rejected_at',[$from, $to])
            ;
        });
        if($status != -1){
            $query = $query->where('approval_status', '=', $status);
        }
        $data = $query->get();
        return $data;
    }

    public function member_package_datatables($from, $to, $status = -1)
    {
        $datas = $this->memberPackageData($from, $to, $status);

        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->editColumn('user_name', function($data) {
                return '<span>'.$data->user_name.'</span><br>';
            })
            ->editColumn('checked_tnc', function($data) {
                return $data->checked_tnc == 1 ? 'Yes' : 'No';
            })
            ->editColumn('payment_status', function($data) {
                return isset($data->payment_status)
                    ? ($data->payment_status == 'Pending'
                        ? "<span id='payment-status' class='badge badge-danger'>Unpaid</span>"
                        : "<span id='payment-status' class='badge badge-success'>Paid</span>")
                    : '';
            })
            ->addColumn('is_renew', function($data) {
                $count = MemberPackageRegister::whereNotNull('approval_at')
                    ->whereDate('approval_at', '<', $data->created_at)
                    ->where('user_id', '=', $data->user_id)
                    ->where('id', '<>', $data->id)
                    ->count();
                return $count > 0 ? 'Yes' : 'No';
            })
            ->addColumn('status_caption', function($data) {
                return ApprovalStatus::getKey($data->approval_status);
            })
            ->editColumn('approval_at', function($data) {
                return isset($data->approval_at) ? Carbon::parse($data->approval_at)->format('d-m-Y') : '';
            })
            ->editColumn('package_old_end_at', function($data) {
                return isset($data->package_old_end_at) ? Carbon::parse($data->package_old_end_at)->format('d-m-Y') : '';
            })
            ->editColumn('package_new_end_at', function($data) {
                return isset($data->package_new_end_at) ? Carbon::parse($data->package_new_end_at)->format('d-m-Y') : '';
            })
            ->editColumn('rejected_at', function($data) {
                return isset($data->rejected_at) ? Carbon::parse($data->rejected_at)->format('d-m-Y') : '';
            })
            ->editColumn('created_at', function($data) {
                return Carbon::parse($data->created_at)->format('d-m-Y');
            })
            ->addColumn('action_by', function($data) {
                return isset($data->approved_by)
                    ? $data->approved_by
                    : $data->rejected_by
                    ;
            })
            ->addColumn('action', function($data) {
                $approve = '<a href="javascript:;" data-href="'. route('admin-user-member-package-approve',$data->id) . '" data-toggle="modal" data-target="#confirm-approve" class="delete"> <i class="fas fa-edit"></i>Approve</a>';
                $reject = '<a href="javascript:;" data-href="'. route('admin-user-member-package-reject',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i>Reject</a>';
                return '<div class="action-list">'.($data->approval_status == 1 ? $approve.$reject : '').'</div>';
            })
            ->rawColumns(['action', 'payment_status', 'user_name'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function member_package()
    {
        return view('admin.user.member-package');
    }

    public function exportMemberPackage($from, $to, $status = -1)
    {
        $datas = $this->memberPackageData($from, $to, $status);
        $file_name = 'member_package_register_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new MemberPackageRegisterExcel($datas), $file_name, null, []);
    }

    public function member_package_approve($id)
    {
        $register = MemberPackageRegister::find($id);
        $package = PackageConfig::findOrFail($register->package_config_id);
        $account = User::findOrFail('71229');

        $rank = UserRank::getValue($package->name);
        $user = $register->user()->first();
        $issuer = Auth::guard('admin')->user();
        $dateString = date("Y-m-d H:m:s");
        $register->approval_at = $dateString;
        $register->approved_by = $issuer->id;
        $register->package_new_end_at = $user->cal_ranking_end_date('+1 year');
        $register->approval_status = ApprovalStatus::Approved;
        $register->save();

        $rs = false;
        if($rank == UserRank::Premium){
            $rs = $user->upgrade_premium($issuer->id, null, $register->id);
        }
        else if($rank == UserRank::Gold){
            $rs = $user->upgrade_gold($issuer->id, null, $register->id);
            $register->package_new_end_at = $user->ranking_end_date;
            $register->save();
        }
        else if($rank == UserRank::Platinum){
            $rs = $user->upgrade_platinum($issuer->id, null, $register->id);
            $register->package_new_end_at = $user->ranking_end_date;
            $register->save();
        }

        if($rs){
            $gs = Generalsetting::findOrFail(1);
            if($package->bonus_rp > 0){
                $point_log = new UserPointLog;
                $point_log->user_id = $user->id;
                $point_log->log_type = 'Buying Package Bonus';
                $point_log->order_ref_id = 0;
                $point_log->reward_point_balance = $user->reward_point;
                $point_log->shopping_point_balance = isset($user->shopping_point) ? $user->shopping_point : 0;
                $point_log->exchange_rate = 0;
                $point_log->note = 'Bonus Reward Point when user buy member package';
                $point_log->descriptions = 'Hệ thống cộng điểm thưởng reward point khi đăng ký gói thành viên';
                $point_log->reward_point = $package->bonus_rp;
                $point_log->shopping_point = 0;
                $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                $user->reward_point = $user->reward_point + $package->bonus_rp;
                $user->save();
                $point_log->save();
            }
            if($package->bonus_sp > 0){
                $point_log = new UserPointLog;
                $point_log->user_id = $user->id;
                $point_log->log_type = 'Buying Package Bonus';
                $point_log->order_ref_id = 0;
                $point_log->reward_point_balance = $user->reward_point;
                $point_log->shopping_point_balance = isset($user->shopping_point) ? $user->shopping_point : 0;
                $point_log->exchange_rate = 0;
                $point_log->note = 'Bonus Shopping Point when user buy member package';
                $point_log->descriptions = 'Hệ thống cộng điểm thưởng shopping point khi đăng ký gói thành viên';
                $point_log->reward_point = 0;
                $point_log->shopping_point = $package->bonus_sp;
                $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                if ($account->shopping_point > $package->bonus_sp) {
                    $user->shopping_point = $user->shopping_point + $package->bonus_sp;
                    $account->shopping_point = $account->shopping_point - $package->bonus_sp;
                    $account->save();
                } else {
                    $user->shopping_point = $user->shopping_point;
                }
               
                $user->save();
                $point_log->save();
            }
        }

        $settings = Generalsetting::findOrFail(1);
        if($settings->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "user_membership_approve",
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
            mail($user->email,'Your request for membership successfully','Your request for membership successfully. Please Login to your account to check your title.',$headers);
        }

        $data = array('Approve Package Success');
        return response()->json($data);
    }

    public function member_package_reject($id)
    {
        $register = MemberPackageRegister::find($id);

        $issuer = Auth::guard('admin')->user();
        $dateString = date("Y-m-d H:m:s");
        $register->rejected_at = $dateString;
        $register->rejected_by = $issuer->id;
        $register->approval_status = ApprovalStatus::Rejected;
        $register->save();
        $user = $register->user()->first();
        $settings = Generalsetting::findOrFail(1);
        if($settings->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "user_membership_reject",
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
            mail($user->email,'Your request for membership have been rejected','Your request for membership have been rejected. Please contact us for more details.',$headers);
        }
        $data = array('Reject Package Success');
        return response()->json($data);
    }

    //*** GET Request
    public function image()
    {
        return view('admin.generalsetting.user_image');
    }

    //*** GET Request
    public function show($id)
    {
        if(!User::where('id',$id)->exists())
        {
            return redirect()->route('admin.dashboard')->with('unsuccess',__('Sorry the page does not exist.'));
        }
        $data = User::findOrFail($id);
        return view('admin.user.show',compact('data'));
    }

    //*** GET Request
    public function ban($id1,$id2)
    {
        $user = User::findOrFail($id1);
        $user->ban = $id2;
        $user->update();
    }

    public function verify($id)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user = User::findOrFail($id);
        $user->verified_date = Carbon::now()->format('Y-m-d H:m:s');
        $user->email_verified = 'Yes';
        $user->update();
        $msg = 'User Verified Successfully.';
        return response()->json($msg);
    }

    //*** GET Request
    public function rank_update($id1,$id2)
    {
        $id2 = intval($id2);
        $data = array('Rank Update Success');
        $user = User::findOrFail($id1);
        if($user->ranking_ad_applied != $id2){
            $new_rank = $id2;
            if($id2 == UserRank::Auto){
                $max_ranking_end_date = DB::table('member_package_registers')
                    ->where('user_id', '=', $user->id)
                    ->where('approval_status', '=', ApprovalStatus::Approved)
                    ->max('package_new_end_at');
                $user->ranking_end_date = $max_ranking_end_date;
                $new_rank = $user->cal_rank();
                //$user->save();
            }
            $isUpRank = $user->is_higher_rank($new_rank);
            $issuer = Auth::guard('admin')->user();
            $log = new UserRankingLog;
            $log->user_id = $user->id;
            $log->admin_issuer = $issuer->id;
            $log->reason = RankingReason::AdminUpdate;
            $log->from_rank = UserRank::getKey($user->ranking);
            $log->to_rank = UserRank::getKey($new_rank);
            $log->rank_ad_applied = $id2;
            $log->rank_current_date = $user->ranking_end_date;
            $log->rank_end_date = $user->ranking_end_date;
            $user->ranking = $new_rank;
            $user->ranking_ad_applied = $id2;
            $referral = $user->referral_user($user->referral_code);
            $user->save();
            if($isUpRank){
                $log->ranking_log_type = RankingLogType::Upgrade;
                if($user->checkExpiredMembership()){
                    $user->ranking_end_date = $user->cal_ranking_end_date(' +1 year');
                    $log->rank_end_date = $user->ranking_end_date;
                    $user->save();
                }
                $log->save();
                if(isset($referral)){
                    $referral->checkUpRank();
                }
            }
            else{
                $log->ranking_log_type = RankingLogType::Downgrade;
                $log->save();
                if(isset($referral)){
                    $referral->checkDownRank();
                }
            }
            array_push($data, 1);
        }
        else{
            array_push($data, 0);
        }
        return response()->json($data);
    }

    //*** GET Request
    public function edit($id)
    {
        $data = User::findOrFail($id);
        return view('admin.user.edit',compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
                'photo' => 'mimes:jpeg,jpg,png,svg',
                'email' => 'unique:users,email,'.$id
                ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        $user = User::findOrFail($id);

        $data = $request->all();
        $data['CityID'] = $request->province;
        $data['DistrictID'] = $request->district;
        $data['ward_id'] = $request->ward;
        if ($file = $request->file('photo'))
        {
            $name = time().str_replace(' ', '', $file->getClientOriginalName());
            $file->move('assets/images/users',$name);
            if($user->photo != null)
            {
                if (file_exists(public_path().'/assets/images/users/'.$user->photo)) {
                    unlink(public_path().'/assets/images/users/'.$user->photo);
                }
            }
            $data['photo'] = $name;
        }

        if(!empty($data['referral_code']))
        {
            $referral_user = $user->referral_user($data['referral_code']);
            if(isset($referral_user)){
                $data['referral_user_id'] = $referral_user->id;
                $data['referral_user_ids'] = $user->referral_user_ids($referral_user->referral_code, $referral_user->id);
            }
        }

        if(!empty($data['new_password']))
        {
            $data['password'] = bcrypt($request['new_password']);
        }

        $user->update($data);
        $msg = 'Customer Information Updated Successfully.';
        return response()->json($msg);
    }

    //*** GET Request Delete
    public function destroy($id)
    {
        $user = User::findOrFail($id);


        if($user->reports->count() > 0)
        {
            foreach ($user->reports as $gal) {
                $gal->delete();
            }
        }


        if($user->shippings->count() > 0)
        {
            foreach ($user->shippings as $gal) {
                $gal->delete();
            }
        }


        if($user->packages->count() > 0)
        {
            foreach ($user->packages as $gal) {
                $gal->delete();
            }
        }


        if($user->ratings->count() > 0)
        {
            foreach ($user->ratings as $gal) {
                $gal->delete();
            }
        }

        if($user->notifications->count() > 0)
        {
            foreach ($user->notifications as $gal) {
                $gal->delete();
            }
        }

        if($user->wishlists->count() > 0)
        {
            foreach ($user->wishlists as $gal) {
                $gal->delete();
            }
        }

        if($user->withdraws->count() > 0)
        {
            foreach ($user->withdraws as $gal) {
                $gal->delete();
            }
        }

        if($user->socialProviders->count() > 0)
        {
            foreach ($user->socialProviders as $gal) {
                $gal->delete();
            }
        }

        if($user->conversations->count() > 0)
        {
            foreach ($user->conversations as $gal) {
            if($gal->messages->count() > 0)
            {
                foreach ($gal->messages as $key) {
                    $key->delete();
                }
            }
                $gal->delete();
            }
        }
        if($user->comments->count() > 0)
        {
            foreach ($user->comments as $gal) {
            if($gal->replies->count() > 0)
            {
                foreach ($gal->replies as $key) {
                    $key->delete();
                }
            }
                $gal->delete();
            }
        }

            if($user->replies->count() > 0)
            {
                foreach ($user->replies as $gal) {
                    if($gal->subreplies->count() > 0)
                    {
                        foreach ($gal->subreplies as $key) {
                            $key->delete();
                        }
                    }
                    $gal->delete();
                }
            }


        if($user->favorites->count() > 0)
        {
            foreach ($user->favorites as $gal) {
                $gal->delete();
            }
        }


        if($user->subscribes->count() > 0)
        {
            foreach ($user->subscribes as $gal) {
                $gal->delete();
            }
        }

        if($user->services->count() > 0)
        {
            foreach ($user->services as $gal) {
                if (file_exists(public_path().'/assets/images/services/'.$gal->photo)) {
                    unlink(public_path().'/assets/images/services/'.$gal->photo);
                }
                $gal->delete();
            }
        }


        if($user->withdraws->count() > 0)
        {
            foreach ($user->withdraws as $gal) {
                $gal->delete();
            }
        }


        if($user->products->count() > 0)
        {

// PRODUCT

            foreach ($user->products as $prod) {
                if($prod->galleries->count() > 0)
                {
                    foreach ($prod->galleries as $gal) {
                            if (file_exists(public_path().'/assets/images/galleries/'.$gal->photo)) {
                                unlink(public_path().'/assets/images/galleries/'.$gal->photo);
                            }
                        $gal->delete();
                    }

                }
                if($prod->ratings->count() > 0)
                {
                    foreach ($prod->ratings as $gal) {
                        $gal->delete();
                    }
                }
                if($prod->wishlists->count() > 0)
                {
                    foreach ($prod->wishlists as $gal) {
                        $gal->delete();
                    }
                }

                if($prod->clicks->count() > 0)
                {
                    foreach ($prod->clicks as $gal) {
                        $gal->delete();
                    }
                }

                if($prod->comments->count() > 0)
                {
                    foreach ($prod->comments as $gal) {
                    if($gal->replies->count() > 0)
                    {
                        foreach ($gal->replies as $key) {
                            $key->delete();
                        }
                    }
                        $gal->delete();
                    }
                }

                if (file_exists(public_path().'/assets/images/products/'.$prod->photo)) {
                    unlink(public_path().'/assets/images/products/'.$prod->photo);
                }

                $prod->delete();
            }


// PRODUCT ENDS

        }
// OTHER SECTION



        if($user->senders->count() > 0)
        {
            foreach ($user->senders as $gal) {
            if($gal->messages->count() > 0)
            {
                foreach ($gal->messages as $key) {
                    $key->delete();
                }
            }
                $gal->delete();
            }
        }


        if($user->recievers->count() > 0)
        {
            foreach ($user->recievers as $gal) {
            if($gal->messages->count() > 0)
            {
                foreach ($gal->messages as $key) {
                    $key->delete();
                }
            }
                $gal->delete();
            }
        }


        if($user->conversations->count() > 0)
        {
            foreach ($user->conversations as $gal) {
            if($gal->messages->count() > 0)
            {
                foreach ($gal->messages as $key) {
                    $key->delete();
                }
            }
                $gal->delete();
            }
        }


        if($user->vendororders->count() > 0)
        {
            foreach ($user->vendororders as $gal) {
                $gal->delete();
            }
        }

        if($user->notivications->count() > 0)
        {
            foreach ($user->notivications as $gal) {
                $gal->delete();
            }
        }


// OTHER SECTION ENDS


        //If Photo Doesn't Exist
        if($user->photo == null){
            $user->delete();
            //--- Redirect Section
            $msg = 'Data Deleted Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends
        }
        //If Photo Exist
        if (file_exists(public_path().'/assets/images/users/'.$user->photo)) {
                unlink(public_path().'/assets/images/users/'.$user->photo);
                }
        $user->delete();
        //--- Redirect Section
        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** JSON Request
    public function withdrawdatatables()
    {
            $datas = Withdraw::where('type','=','user')->orderBy('id','desc')->get();
            //--- Integrating This Collection Into Datatables
            return Datatables::of($datas)
                            ->addColumn('email', function(Withdraw $data) {
                                $email = $data->user->email;
                                return $email;
                            })
                            ->addColumn('phone', function(Withdraw $data) {
                                $phone = $data->user->phone;
                                return $phone;
                            })
                            ->editColumn('status', function(Withdraw $data) {
                                $status = ucfirst($data->status);
                                return $status;
                            })
                            ->editColumn('amount', function(Withdraw $data) {
                                $sign = Currency::where('is_default','=',1)->first();
                                $amount = $sign->sign.round($data->amount * $sign->value , 2);
                                return $amount;
                            })
                            ->addColumn('action', function(Withdraw $data) {
                                $action = '<div class="action-list"><a data-href="' . route('admin-withdraw-show',$data->id) . '" class="view details-width" data-toggle="modal" data-target="#modal1"> <i class="fas fa-eye"></i> Details</a>';
                                if($data->status == "pending") {
                                $action .= '<a data-href="' . route('admin-withdraw-accept',$data->id) . '" data-toggle="modal" data-target="#confirm-delete"> <i class="fas fa-check"></i> Accept</a><a data-href="' . route('admin-withdraw-reject',$data->id) . '" data-toggle="modal" data-target="#confirm-delete1"> <i class="fas fa-trash-alt"></i> Reject</a>';
                                }
                                $action .= '</div>';
                                return $action;
                            })
                            ->rawColumns(['name','action'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    //*** GET Request
    public function withdraws()
    {
        return view('admin.user.withdraws');
    }

    //*** GET Request
    public function withdrawdetails($id)
    {
        $sign = Currency::where('is_default','=',1)->first();
        $withdraw = Withdraw::findOrFail($id);
        return view('admin.user.withdraw-details',compact('withdraw','sign'));
    }

    //*** GET Request
    public function accept($id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $data['status'] = "completed";
        $withdraw->update($data);
        //--- Redirect Section
        $msg = 'Withdraw Accepted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function reject($id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $account = User::findOrFail($withdraw->user->id);
        $account->affilate_income = $account->affilate_income + $withdraw->amount + $withdraw->fee;
        $account->update();
        $data['status'] = "rejected";
        $withdraw->update($data);
        //--- Redirect Section
        $msg = 'Withdraw Rejected Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function secret($id)
    {
        Auth::guard('web')->logout();
        $data = User::findOrFail($id);
        Auth::guard('web')->login($data);
        return redirect()->route('user-dashboard');
    }

    //*** FILE3 - Weekly Sales Report - Customer

    public function saleReport()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.user.reports.sale-report', compact('now'));
    }

    public function datatablesSaleReport($from, $to, $isTop = 0, $isOrder = 0)
    {
         $datas = $this->dataSaleReport($from, $to, $isTop, $isOrder)->get();
         return Datatables::of($datas)
            ->editColumn('created_at', function($data) {
                $rs = Carbon::parse($data->created_at)->format('d-m-Y H:m');
                return $rs;
            })
            ->editColumn('number_of_sales', function($data) {
                return number_format($data->number_of_sales);
            })
            ->editColumn('total_qty', function($data) {
                return number_format($data->total_qty);
            })
            ->editColumn('total_amount', function($data) {
            return number_format($data->total_amount);
            })
            ->editColumn('reward_point', function($data) {
                return number_format($data->reward_point);
            })
            ->editColumn('shopping_point', function($data) {
                return number_format($data->shopping_point);
            })
             ->toJson(); //--- Returning Json Data To Client Side
    }

    public function dataSaleReport($from, $to, $isTop = 0, $isOrder = 0, $top = 10)
    {
        $query = $this->querySaleReport($from, $to);
        $query = $query->select(
                'u.name as customer_name'
                , 'u.email as customer_email'
                , DB::raw('pc.name as ranking')
                , 'u.created_at'
                , DB::raw('ifnull(l1.l1_count, 0) l1_count')
                , DB::raw('COUNT(DISTINCT o.id) as number_of_sales')
                , DB::raw('SUM(vo.qty) as total_qty')
                , DB::raw('SUM(vo.price + vo.price_shopping_point_amount) as total_amount')
                , 'u.reward_point'
                , 'u.shopping_point'
                )
            ->groupBy('u.name', 'u.email', 'u.created_at', 'u.reward_point', 'u.shopping_point', 'pc.name', 'l1.l1_count')
            ;

        if($isOrder){
            $query = $query->orderByDesc('number_of_sales');
        }
        else{
            $query = $query->orderByDesc('total_amount');
        }
        if($isTop){
            $query = $query->limit($top);
        }
        return $query;
    }

    public function querySaleReportL1($from, $to)
    {
        return DB::table('users as u')
            ->join('users as u1', 'u1.referral_user_id', '=', 'u.id')
            // ->whereExists(function ($q) use ($from, $to) {
            //     $q->select(DB::raw(1))
            //         ->from('orders')
            //         ->whereColumn('orders.user_id', 'u.id')
            //         ->whereBetween('orders.completed_at',[$from, $to])
            //         ;
            // })
            ->select(
                'u.id'
                , DB::raw('COUNT(u1.id) as l1_count')
                )
            ->groupBy('u.id')
        ;
    }

    public function querySaleReport($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $sub_query = $this->querySaleReportL1($from, $to);
        $query = DB::table('users as u')
            ->join('orders as o', 'o.user_id', '=', 'u.id')
            ->join('vendor_orders as vo', 'vo.order_id', '=', 'o.id')
            ->join('package_configs as pc', 'pc.user_rank_id', '='
                , DB::raw('case when u.ranking > u.ranking_purchased then u.ranking else u.ranking_purchased end'))
            ->leftJoinSub($sub_query, 'l1', function ($join) {
                    $join->on('l1.id', '=', 'u.id');
                })
            ->whereBetween('o.completed_at',[$from, $to])
            ->where('o.status', '=', 'completed')
            ;
        return $query;
    }

    public function exportSaleReport($from, $to, $isTop = 0, $isOrder = 0, $top = 10)
    {
        $datas = $this->dataSaleReport($from, $to, $isTop, $isOrder, $top)->get();
        $file_name = 'sale_report_customer_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new SaleReport($datas), $file_name, null, []);
    }


    //FILE3 -  Summaries  - Top Spending Customer by Total Purchase Amount
    public function saleReportSummariesTop10SpendingAmount()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.user.reports.sale-report-summaries-top10spendingamount', compact('now'));
    }

    //FILE3 -  Summaries  - Top Spending Customer by Total Order
    public function saleReportSummariesTop10SpendingOrder()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.user.reports.sale-report-summaries-top10spendingorder', compact('now'));
    }

    //FILE3 -  Summaries  - SP earning member by Affiliate Bonus
    public function querySaleReportSummariesTop10SPEarningByAffiliate($from, $to)
    {
    $to = HTDUtils::addDays($to, 1);
    $sub_query = $this->querySaleReportL1($from, $to);
    $query = DB::table('user_point_logs as pl')
        ->join('orders as o', 'o.id', '=', 'pl.order_ref_id')
        ->join('users as u', 'u.id', '=', 'pl.user_id')
        ->join('package_configs as pc', 'pc.user_rank_id', '='
            , DB::raw('case when u.ranking > u.ranking_purchased then u.ranking else u.ranking_purchased end'))
        ->leftJoinSub($sub_query, 'l1', function ($join) {
            $join->on('l1.id', '=', 'u.id');
        })
        ->where('pl.log_type', '=', 'Affiliate Bonus')
        ->whereBetween('o.completed_at',[$from, $to])
        ->where('o.status', '=', 'completed')
        ;
        return $query;
    }

    public function dataSaleReportSummariesTop10SPEarningByAffiliate($from, $to)
    {
        $query = $this->querySaleReportSummariesTop10SPEarningByAffiliate($from, $to);
        $query = $query->select(
                'u.name as customer_name'
                // , DB::raw('u.id as ranking1')
                , DB::raw('pc.name as ranking')
                , 'u.created_at'
                , DB::raw('ifnull(l1.l1_count, 0) l1_count')
                , DB::raw('SUM(pl.shopping_point) as total_shopping_point')
                , 'u.shopping_point'
                )
            ->groupBy('u.name', 'u.created_at', 'u.shopping_point', 'pc.name', 'l1.l1_count')
            ->orderByDesc('total_shopping_point')
            ->limit(10)
            ;
        return $query;
    }

    public function datatablesSaleReportSummariesTop10SPEarningByAffiliate($from, $to)
    {
        $datas = $this->dataSaleReportSummariesTop10SPEarningByAffiliate($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('created_at', function($data) {
                $rs = Carbon::parse($data->created_at)->format('d-m-Y H:m');
                return $rs;
            })
            ->editColumn('shopping_point', function($data) {
                return number_format($data->shopping_point);
            })
            ->editColumn('total_shopping_point', function($data) {
                return number_format($data->total_shopping_point);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function saleReportSummariesTop10SPEarningByAffiliate()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.user.reports.sale-report-summaries-top10spearningbyaffiliate', compact('now'));
    }

    public function exportSaleReportSummariesTop10SPEarningByAffiliate($from, $to)
    {
        $datas = $this->dataSaleReportSummariesTop10SPEarningByAffiliate($from, $to)->get();
        $file_name = 'sale_report_summaries_top10spearningbyaffiliate_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new SaleReportSummariesTop10SPEarningByAffiliate($datas), $file_name, null, []);
    }

    //FILE3 -  Summaries  - RP earning member by Merchant Bonus (2)
    public function querySaleReportSummariesTop10RPEarningByMerchantBonus($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $sub_query = $this->querySaleReportL1($from, $to);
        $query = DB::table('user_point_logs as pl')
            ->join('users as u', 'u.id', '=', 'pl.user_id')
            ->join('package_configs as pc', 'pc.user_rank_id', '='
                , DB::raw('case when u.ranking > u.ranking_purchased then u.ranking else u.ranking_purchased end'))
            ->leftJoinSub($sub_query, 'l1', function ($join) {
                $join->on('l1.id', '=', 'u.id');
            })
            ->where('pl.log_type', '=', 'Merchant Sales Bonus')
            ->whereBetween('pl.created_at',[$from, $to])
            ;
        return $query;
    }

    public function dataSaleReportSummariesTop10RPEarningByMerchantBonus($from, $to)
    {
        $query = $this->querySaleReportSummariesTop10RPEarningByMerchantBonus($from, $to);
        $query = $query->select(
                'u.name as customer_name'
                , DB::raw('pc.name as ranking')
                , 'u.created_at'
                , DB::raw('ifnull(l1.l1_count, 0) l1_count')
                , DB::raw('SUM(pl.reward_point) as total_reward_point')
                , 'u.reward_point'
                )
            ->groupBy('u.name', 'u.created_at', 'u.reward_point', 'pc.name', 'l1.l1_count')
            ->orderByDesc('total_reward_point')
            ->limit(10)
            ;
        return $query;
    }

    public function datatablesSaleReportSummariesTop10RPEarningByMerchantBonus($from, $to)
    {
        $datas = $this->dataSaleReportSummariesTop10RPEarningByMerchantBonus($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('created_at', function($data) {
                $rs = Carbon::parse($data->created_at)->format('d-m-Y H:m');
                return $rs;
            })
            ->editColumn('total_reward_point', function($data) {
                return number_format($data->total_reward_point);
            })
            ->editColumn('reward_point', function($data) {
                return number_format($data->reward_point);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function saleReportSummariesTop10RPEarningByMerchantBonus()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.user.reports.sale-report-summaries-top10rpearningbymerchantbonus', compact('now'));
    }

    public function exportSaleReportSummariesTop10RPEarningByMerchantBonus($from, $to)
    {
        $datas = $this->dataSaleReportSummariesTop10RPEarningByMerchantBonus($from, $to)->get();
        $file_name = 'sale_report_summaries_top10rpearningbyMerchantBonus_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new SaleReportSummariesTop10RPEarningByMerchantBonus($datas), $file_name, null, []);
    }

    public function refreshRank($id)
    {
        $user = User::findOrFail($id);
        $current = $user->get_rank();
        $calculated = $user->cal_rank();
        if($calculated > $current){
            $issuer = Auth::guard('admin')->user();
            try{
                $user->upgradeMember($calculated, RankingReason::RefreshRank, $issuer->id);
            }
            catch (\Exception $e){
                return response()->json(3);
            }
            return response()->json(2);
        }
        return response()->json(1);
    }

    public function findRootAffiliate($id)
    {
        $max_level = 300;
        $level = 0;
        $user = User::findOrFail($id);
        $u = $user;
        $result[] = array('id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'affilate_code' => $u->affilate_code, 'referral_code' => $u->referral_code, 'referral_user_id' => $u->referral_user_id, 'level' => $level);
        while(true){
            $level++;
            $ref = $u->referral_user($u->referral_code);
            if(isset($ref)){
                $u = $ref;
                $result[] = array('id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'affilate_code' => $u->affilate_code, 'referral_code' => $u->referral_code, 'referral_user_id' => $u->referral_user_id, 'level' => $level);
            }
            else{
                break;
            }
            if($level == $max_level){
                break;
            }
        }
        return dd($result);
    }

    //KOL Affiliate Bonus
    public function datatablesKOLAffiliateBonus($from, $is_include_renew = 0)
    {
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolAffiliateBonusData($from, $is_include_renew);
        return Datatables::of($datas)
            ->editColumn('package_price', function($data) {
                return number_format($data->package_price);
            })
            ->editColumn('bonus', function($data) {
                return number_format($data->bonus);
            })
            ->editColumn('kolaff_calculated', function($data) {
                return $data->kolaff_calculated == 1 ? 'Yes' : 'No';
            })
            ->addColumn('consumer_info', function($data) {
                $info = '<span>'. $data->consumer_id .'</span><br>'.'<span>'. $data->consumer_name .'</span><br>'.'<span>'. $data->consumer_email .'</span><br>';
                return $info;
            })
            ->addColumn('l1_info', function($data) {
                $info = '<span>'. $data->l1_id .'</span><br>'
                    .'<span>'. $data->l1_name .'</span><br>'
                    .'<span>'. $data->l1_email .'</span><br>'
                    .'<span>'. $data->l1_ranking .'</span>';
                return $info;
            })
            ->addColumn('l1_bank_info', function($data) {
                $info = '<span>'. $data->l1_bankname .'</span><br>'.'<span>'. $data->l1_bankaccount .'</span><br>'.'<span>'. $data->l1_bankbumber .'</span><br>'.'<span>'. $data->l1_bankaddress .'</span>';
                return $info;
            })
            ->rawColumns(['consumer_info', 'l1_info', 'l1_bank_info'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function kolAffiliateBonus()
    {
        return view('admin.user.reports.kol-affiliate-bonus');
    }

    public function exportKOLAffiliateBonus($from, $is_include_renew = 0)
    {
        $datas = app('App\Http\Controllers\Admin\ApiUserController')->kolAffiliateBonusData($from, $is_include_renew);
        $file_name = 'user_kol_affiliate_bonus_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new KOLAffiliateBonus($datas), $file_name, null, []);
    }

    public function processPayKOLAffiliateBonus($from, $is_include_renew = 0)
    {
        $msg = app('App\Http\Controllers\Admin\ApiUserController')->kolAffCalculate($from, $is_include_renew);
        return $msg;
    }

    //KOL Affiliate Bonus ENDS

    //KOL Affiliate Bonus Paid
    public function dataKOLAffiliateBonusPaid($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $query = DB::table('member_package_registers as r')
            ->join('user_point_logs as lo', 'r.id', '=', 'lo.mpr_id')
            ->join('users as consumer', 'r.user_id', '=', 'consumer.id')
            ->join('users as referral', 'consumer.referral_user_id', '=', 'referral.id')
            ->join('package_configs as pc', 'pc.user_rank_id', '='
                , DB::raw('case when referral.ranking > referral.ranking_purchased then referral.ranking else referral.ranking_purchased end'))
            ->whereBetween('lo.created_at',[$from, $to])
            ->select('r.id as mpr_id', 'r.payment_number'
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
                , 'lo.amount as package_price'
                , DB::raw('lo.amount_bonus as bonus')
                , 'r.approval_at as approved_at'
                , 'lo.created_at as paid_at'
                );
        $datas = $query->get();
        return $datas;
    }

    public function datatablesKOLAffiliateBonusPaid($from, $to)
    {
        $datas = $this->dataKOLAffiliateBonusPaid($from, $to);
        return Datatables::of($datas)
            ->editColumn('package_price', function($data) {
                return number_format($data->package_price);
            })
            ->editColumn('bonus', function($data) {
                return number_format($data->bonus);
            })
            ->addColumn('consumer_info', function($data) {
                $info = '<span>'. $data->consumer_id .'</span><br>'.'<span>'. $data->consumer_name .'</span><br>'.'<span>'. $data->consumer_email .'</span><br>';
                return $info;
            })
            ->addColumn('l1_info', function($data) {
                $info = '<span>'. $data->l1_id .'</span><br>'
                    .'<span>'. $data->l1_name .'</span><br>'
                    .'<span>'. $data->l1_email .'</span><br>'
                    .'<span>'. $data->l1_ranking .'</span>';
                return $info;
            })
            ->addColumn('l1_bank_info', function($data) {
                $info = '<span>'. $data->l1_bankname .'</span><br>'.'<span>'. $data->l1_bankaccount .'</span><br>'.'<span>'. $data->l1_bankbumber .'</span><br>'.'<span>'. $data->l1_bankaddress .'</span>';
                return $info;
            })
            ->rawColumns(['consumer_info', 'l1_info', 'l1_bank_info'])
            ->toJson();
    }

    public function kolAffiliateBonusPaid()
    {
        return view('admin.user.reports.kol-affiliate-bonus-paid');
    }

    public function exportKOLAffiliateBonusPaid($from, $to)
    {
        $datas = $this->dataKOLAffiliateBonusPaid($from, $to);
        $file_name = 'user_kol_affiliate_bonus_paid_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new KOLAffiliateBonusPaid($datas), $file_name, null, []);
    }
    //KOL Affiliate Bonus ENDS

    public function confirmKol($id) {
        $user = User::findOrFail($id);
        if ( $user->kol == 1) {
            $user->kol = '0';
        } else {
            $user->kol = '1';
        }

        $user->update();
        $msg = 'Successfully.';
        return response()->json($msg);
    }
    public function confirmTransferPoint($id){
        $user = User::findOrFail($id);
        if ( $user->can_transfer_point == 1) {
            $user->can_transfer_point = '0';
            $user->max_transfer_point = 0;
            disable_transfer_point_log($user);
        } else {
            $user->can_transfer_point = '1';
            $user->max_transfer_point = $user->shopping_point * config("tuezy.monthly_transfer_percents", 0.2);
            enable_transfer_point_log($user);
        }
        $user->update();
        $msg = 'Successfully.';
        return response()->json($msg);
    }
    public function confirmSpecialKol($id) {
        $user = User::findOrFail($id);
        if ( $user->special_kol == 1) {
            $user->special_kol = '0';
        } else {
            $user->special_kol = '1';
        }

        $user->update();
        $msg = 'Successfully.';
        return response()->json($msg);
    }

    public function showTransferPoint(){
        $customers = User::all(["id","email"]);
        $adminTransactions = AdminTransaction::where("name", '=', AdminTransaction::ADMIN_TRANSFER_POINT)->limit(60)->get();
        return view('admin.user.transfer-point', [
            "customers" => $customers,
            'transactions' => $adminTransactions
        ]);
    }

    public function adminSubmitTransferPoint(Request $request){

        $rules = [
            'from_customer' => 'required|email',
            'to_customer' => 'required|email',
            'amount' => 'required|numeric'
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }



        DB::beginTransaction();

        try {
            $fromCustomer = User::where("email", "=", $request->input("from_customer"))->first();

            $toCustomer = User::where("email", "=", $request->input("to_customer"))->first();

            $point_exchange = $request->input("amount");

            $point_log = new UserPointLog();
            $point_log->user_id = $fromCustomer->id;
            if($fromCustomer->email != 'demo@demo.com'){
                $point_log->log_type = 'Admin Transfer Point';
                $point_log->shopping_point_balance = isset($fromCustomer->shopping_point) ? $fromCustomer->shopping_point : 0;
                $point_log->exchange_rate = 1;
                $point_log->note = "Hệ thống chuyển {$point_exchange} SP của bạn sang cho ".$toCustomer->email;
                $point_log->descriptions = "Hệ thống chuyển {$point_exchange} SP của bạn sang cho ".$toCustomer->email;
                $point_log->shopping_point = -$point_exchange;
                $fromCustomer->shopping_point = $fromCustomer->shopping_point - $point_exchange;

            }
            $fromCustomer->save();
            $point_log->save();

            $point_log = new UserPointLog();
            $point_log->user_id = $toCustomer->id;

            $point_log->log_type = 'Admin Transfer Point';
            $point_log->shopping_point_balance = isset($toCustomer->shopping_point) ? $toCustomer->shopping_point : 0;
            $point_log->exchange_rate = 1;
            $point_log->note = ($fromCustomer->email == 'demo@demo.com'? 'Ohlaalaa': $fromCustomer->email ). " chuyển {$point_exchange} SP cho bạn";
            $point_log->descriptions = ($fromCustomer->email == 'demo@demo.com'? 'Ohlaalaa': $fromCustomer->email ). " chuyển {$point_exchange} SP cho bạn";
            $point_log->shopping_point = $point_exchange;
            $toCustomer->shopping_point = $toCustomer->shopping_point + $point_exchange;
            $toCustomer->save();
            $point_log->save();

            admin_log(\App\Models\AdminTransaction::ADMIN_TRANSFER_POINT, "Chuyển ". $request->input("amount"). " SP từ ". $request->input("from_customer") . " sang ".$request->input("to_customer"));

            DB::commit();
            // all good
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);

    }
}
