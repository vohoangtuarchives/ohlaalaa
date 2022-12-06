<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Validator;
use Datatables;
use Carbon\Carbon;
use App\Models\User;
use App\Enums\UserRank;
use App\Models\Currency;
use App\Models\Withdraw;
use App\Classes\HTDUtils;
use App\Enums\PreferredType;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Enums\ApprovalStatus;
use App\Models\Generalsetting;
use App\Models\UserSubscription;
use App\Enums\VendorSubscription;
use Illuminate\Support\Facades\DB;
use App\Exports\Users\VendorExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Users\VendorSubscriptionExport;
use App\Exports\Vendors\Reports\VendorApproval;
use App\Exports\Vendors\Reports\VendorApprovalSummries;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables($status, $plan, $preferred, $from = null, $to = null, $email = null)
    {
        $query = $this->vendor_query($status, $plan, $preferred, $from, $to, $email)
            ->orderBy('u.id','desc');
        if(!isset($from)){
            $query =  $query->limit(200);
        }

        $datas = $query->select('u.*', 'ver.status as ver_status'
            , DB::raw('case when u.ranking_ad_applied <> 0 then u.ranking_ad_applied
                    when u.ranking > u.ranking_purchased then u.ranking
                    else u.ranking_purchased end as ranking')
            )
            ->get();
        return Datatables::of($datas)
            ->addColumn('rank_name', function($data){

                return UserRank::getKey($data->ranking);
            })
            ->editColumn('created_at', function($data) {
                $rs = Carbon::parse($data->created_at)->format('d-m-Y H:m');
                return $rs;
            })
            ->editColumn('date', function($data) {
                $rs = Carbon::parse($data->date)->format('d-m-Y');
                return $rs;
            })
            ->editColumn('ver_status', function($data) {
                return isset($data->ver_status) ? $data->ver_status : 'Non-Send';
            })
            ->editColumn('vendor_subscription', function($data) {
                return isset($data->vendor_subscription) ? VendorSubscription::getKey($data->vendor_subscription) : '';
            })
            ->editColumn('preferred', function($data) {
                return '<span class="'.($data->preferred == PreferredType::Preferred ? 'text-success' : 'text-danger').'">'.PreferredType::getKey($data->preferred).'</span>';
            })
            ->addColumn('status', function($data) {
                $class = $data->is_vendor == 2 ? 'drop-success' : 'drop-danger';
                $s = $data->is_vendor == 2 ? 'selected' : '';
                $ns = $data->is_vendor == 1 ? 'selected' : '';
                return '<div class="action-list"><select class="process select vendor-droplinks '.$class.'">'.
                    '<option value="'. route('admin-vendor-st',['id1' => $data->id, 'id2' => 2]).'" '.$s.'>Activated</option>'.
                    '<option value="'. route('admin-vendor-st',['id1' => $data->id, 'id2' => 1]).'" '.$ns.'>Deactivated</option></select></div>';
            })
            ->addColumn('action', function($data) {
                return '<div class="godropdown"><button class="go-dropdown-toggle"> Actions<i class="fas fa-chevron-down"></i></button><div class="action-list"><a href="' . route('admin-vendor-secret',$data->id) . '" > <i class="fas fa-user"></i> Secret Login</a><a href="javascript:;" data-href="' . route('admin-vendor-verify',$data->id) . '" class="verify" data-toggle="modal" data-target="#verify-modal"> <i class="fas fa-question"></i> Ask For Verification</a><a href="' . route('admin-vendor-show',$data->id) . '" > <i class="fas fa-eye"></i> Details</a><a href="' . route('admin-vendor-edit',$data->id) . '"> <i class="fas fa-edit"></i> Edit</a><a href="javascript:;" class="send" data-email="'. $data->email .'" data-toggle="modal" data-target="#vendorform"><i class="fas fa-envelope"></i> Send Email</a><a href="javascript:;" data-href="' . route('admin-vendor-delete',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i> Delete</a></div></div>';
            })
            ->rawColumns(['status', 'preferred','action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

	//*** GET Request
    public function index()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.vendor.index', compact('now'));
    }

    public function vendor_export($status, $plan, $preferred, $from = null, $to = null, $email = null)
    {
        $datas = $this->vendor_query($status, $plan, $preferred, $from, $to, $email)
            ->select('u.shop_name', 'u.email', 'u.phone', 'u.TaxCode', 'p.name as province_name'
                , DB::raw('case when u.preferred = 1 then "Preferred" else "Non-Preferred" end as preferred')
                , DB::raw('case when u.is_vendor = 2 then "Activated" else "Deactivated" end as status')
                , DB::raw('IFNULL(ver.status, "Non-Send") as ver_status')
                , DB::raw('case when u.vendor_subscription = 1 then "Yearly Plan" else "Shop Experience" end as vendor_subscription')
                , 'u.reward_point'
                , 'u.shopping_point'
                , 'u.affilate_code'
                , DB::raw('case when u.ranking_ad_applied <> 0 then u.ranking_ad_applied
                    when u.ranking > u.ranking_purchased then u.ranking
                    else u.ranking_purchased end as ranking')
                , DB::raw('case when u.ranking_ad_applied <> 0 then DATE_FORMAT(u.ranking_end_date,"%d-%m-%y")
                    when u.ranking > u.ranking_purchased then DATE_FORMAT(u.ranking_end_date,"%d-%m-%y")
                    else DATE_FORMAT(u.ranking_purchased_end_date,"%d-%m-%y") end as ranking_end_date')
                , DB::raw('DATE_FORMAT(u.verified_at,"%d-%m-%y") as verified_at')
                , DB::raw('DATE_FORMAT(u.created_at,"%d-%m-%y %H:%m") as created_at')
                , DB::raw('DATE_FORMAT(u.date,"%d-%m-%y") as date')
                )
            ->orderByDesc('u.id')->get();
        $file_name = 'vendors_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new VendorExport($datas), $file_name, null, []);
    }

    public function vendor_query($status, $plan, $preferred, $from = null, $to = null, $email = null)
    {
        $query = DB::table('users as u')
            ->join('provinces as p', 'p.id', '=', 'u.CityID')
            ->leftJoin('verifications as ver', 'u.id', '=','ver.user_id');
        if($from != null){
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $query = $query->whereBetween('u.created_at',[$from, $to]);
        }
        $statuss = array($status);
        if($status == -1){
            $statuss = array(1, 2);
        }
        $plans = array($plan);
        if($plan == -1){
            $plans = array(VendorSubscription::Free, VendorSubscription::Pricing);
        }
        $prefers = array($preferred);
        if($preferred == -1){
            $prefers = array(PreferredType::Preferred, PreferredType::NonPreferred);
        }
        if($email != null){
            $query = $query->where('u.email', 'like', '%'.$email.'%');
        }
        $query = $query->whereIn('u.is_vendor',$statuss)
            ->whereIn('u.vendor_subscription',$plans)
            ->whereIn('u.preferred',$prefers);
        return $query;
    }

    //*** GET Request
    public function color()
    {
        return view('admin.generalsetting.vendor_color');
    }

    //*** GET Request
    public function subsdatatables($status, $plan, $from = null, $to = null)
    {
        $query = $this->subs_query($status, $plan, $from, $to);
        $datas = $query->select('u.id as user_id', 'u.name', 'us.id', 'us.subscription_id', 'us.title', 'u.email', 'u.phone', 'u.affilate_code'
            , DB::raw('DATE_FORMAT(us.created_at,"%d-%m-%y %H:%m") as created_at')
            , DB::raw('DATE_FORMAT(us.old_end_at,"%d-%m-%y") as old_end_at')
            , DB::raw('DATE_FORMAT(us.new_end_at,"%d-%m-%y") as new_end_at')
            , DB::raw('DATE_FORMAT(us.approved_at,"%d-%m-%y") as approved_at')
            , DB::raw('DATE_FORMAT(us.rejected_at,"%d-%m-%y") as rejected_at')
            , 'us.status')
            ->orderByDesc('id')->get();

         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
            ->addColumn('status_caption', function($data) {
                return ApprovalStatus::getKey($data->status);
            })
            ->addColumn('action', function($data) {
                $approve = '<a href="javascript:;" data-href="'. route('admin-vendor-subs-approve',$data->id) . '" data-toggle="modal" data-target="#confirm-approve" class="delete"> <i class="fas fa-edit"></i>Approve</a>';
                $reject = '<a href="javascript:;" data-href="'. route('admin-vendor-subs-reject',$data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i>Reject</a>';
                return '<div class="action-list"><a data-href="' . route('admin-vendor-sub',$data->id) . '" class="view details-width" data-toggle="modal" data-target="#modal1"> <i class="fas fa-eye"></i>Details</a>'.($data->status == 1 ? $approve.$reject : '').'</div>';
            })
            ->rawColumns(['action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function subs_export($status, $plan, $from = null, $to = null)
    {
        $datas = $this->subs_query($status, $plan, $from, $to)
            ->select('u.name', 'u.email', 'u.phone', 'u.affilate_code', 'us.title'
                , DB::raw('DATE_FORMAT(us.created_at,"%d-%m-%y %H:%m") as created_at')
                , DB::raw('DATE_FORMAT(us.old_end_at,"%d-%m-%y") as old_end_at')
                , DB::raw('DATE_FORMAT(us.new_end_at,"%d-%m-%y") as new_end_at')
                , DB::raw('DATE_FORMAT(us.approved_at,"%d-%m-%y") as approved_at')
                , DB::raw('DATE_FORMAT(us.rejected_at,"%d-%m-%y") as rejected_at')
                , DB::raw('case when us.status = 1 then "Pending" when us.status = 2 then "Approved" else "Rejected" end as status_caption'))
            ->orderByDesc('us.id')->get();
        $file_name = 'vendor-subscriptions_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new VendorSubscriptionExport($datas), $file_name, null, []);
    }

    public function subs_query($status, $plan, $from = null, $to = null)
    {
        $query = DB::table('user_subscriptions as us')
            ->join('users as u', 'us.user_id', '=', 'u.id')
            ->join('subscriptions as s', 'us.subscription_id', '=', 's.id');
        if($from != null){
            $nDays = 1;
            $to = date("Y-m-d",strtotime($to . '+ '.$nDays.'days'));
            $query = $query->whereBetween('us.created_at',[$from, $to]);
        }
        $statuss = array($status);
        if($status == -1){
            $statuss = array(1, 2, 3);
        }
        $plans = array($plan);
        if($plan == -1){
            $plans = array(VendorSubscription::Free, VendorSubscription::Pricing);
        }
        $query = $query->whereIn('us.status',$statuss)->whereIn('us.type',$plans);

        return $query;
    }

	//*** GET Request
    public function subs()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.vendor.subscriptions',compact('now'));
    }

	//*** GET Request
    public function sub($id)
    {
        $subs = UserSubscription::findOrFail($id);
        return view('admin.vendor.subscription-details',compact('subs'));
    }

	//*** GET Request
  	public function status($id1,$id2)
    {
        $user = User::findOrFail($id1);
        $user->is_vendor = $id2;
        $user->update();
        //--- Redirect Section
        $msg[0] = 'Status Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }

    public function preferred($id1,$id2)
    {
        $user = User::findOrFail($id1);
        $user->preferred = $id2;
        $user->preferred_at = date("Y-m-d H:m:s");
        $user->update();
        //--- Redirect Section
        $msg[0] = 'Status Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }

	//*** GET Request
    public function edit($id)
    {
        $data = User::findOrFail($id);
        return view('admin.vendor.edit',compact('data'));
    }

    public function subscription_approve($id)
    {
        try{
            $dateString = date("Y-m-d H:m:s");
            $register = UserSubscription::find($id);
            $package = Subscription::findOrFail($register->subscription_id);
            $user = $register->user()->first();
            $user->is_vendor = 2;
            $user->vendor_subscription = $register->type;
            $user->date = $user->cal_subscription_end_date('+ '.$package->days.' days');
            $user->mail_sent = 1;
            if($register->type == VendorSubscription::Pricing){
                $user->preferred = PreferredType::Preferred;
                $user->preferred_at = date("Y-m-d H:m:s");
            }
            if(!isset($user->vendor_from)){
                $user->vendor_from = $dateString;
            }
            $user->save();
            $issuer = Auth::guard('admin')->user();
            $register->approved_at = $dateString;
            $register->approved_by = $issuer->name;
            $register->new_end_at = $user->date;
            $register->status = ApprovalStatus::Approved;
            $register->save();
            $settings = Generalsetting::findOrFail(1);

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
                mail($user->email,'Your Vendor Account Activated','Your Vendor Account Activated Successfully. Please Login to your account and build your own shop.',$headers);
            }
        }
        catch (\Exception $e){
            return response()->json(array($e->getMessage()));
        }

        $data = array('Approve Package Success');
        return response()->json($data);
    }

    public function subscription_reject($id)
    {
        $register = UserSubscription::find($id);
        $issuer = Auth::guard('admin')->user();
        $dateString = date("Y-m-d H:m:s");
        $register->rejected_at = $dateString;
        $register->rejected_by = $issuer->id;
        $register->status = ApprovalStatus::Rejected;
        $register->save();
        $user = $register->user()->first();
        $settings = Generalsetting::findOrFail(1);
        if($settings->is_smtp == 1)
        {
            $data = [
                'to' => $user->email,
                'type' => "vendor_reject",
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
    public function verify($id)
    {
        $data = User::findOrFail($id);
        return view('admin.vendor.verification',compact('data'));
    }

	//*** POST Request
    public function verifySubmit(Request $request, $id)
    {
        $settings = Generalsetting::find(1);
        $user = User::findOrFail($id);
        $user->verifies()->create(['admin_warning' => 1, 'warning_reason' => $request->details]);

                    if($settings->is_smtp == 1)
                    {
                    $data = [
                        'to' => $user->email,
                        'type' => "vendor_verification",
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
                    mail($user->email,'Request for verification.','You are requested verify your account. Please send us photo of your passport.Thank You.',$headers);
                    }

        $msg = 'Verification Request Sent Successfully.';
        return response()->json($msg);
    }


	//*** POST Request
    public function update(Request $request, $id)
    {
	    //--- Validation Section
	        $rules = [
                'shop_name'   => 'unique:users,shop_name,'.$id,
                 ];
            $customs = [
                'shop_name.unique' => 'Shop Name "'.$request->shop_name.'" has already been taken. Please choose another name.'
            ];

         $validator = Validator::make($request->all(), $rules,$customs);

         if ($validator->fails()) {
           return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
         }
         //--- Validation Section Ends

        $user = User::findOrFail($id);
        $data = $request->all();
        $user->update($data);
        $msg = 'Vendor Information Updated Successfully.'.'<a href="'.route("admin-vendor-index").'">View Vendor Lists</a>';
        return response()->json($msg);
    }

	//*** GET Request
    public function show($id)
    {
        $data = User::findOrFail($id);
        return view('admin.vendor.show',compact('data'));
    }


    //*** GET Request
    public function secret($id)
    {
        Auth::guard('web')->logout();
        $data = User::findOrFail($id);
        Auth::guard('web')->login($data);
        return redirect()->route('vendor-dashboard');
    }


	//*** GET Request
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->is_vendor = 0;
            $user->is_vendor = 0;
            $user->shop_name = null;
            $user->shop_details= null;
            $user->owner_name = null;
            $user->shop_number = null;
            $user->shop_address = null;
            $user->reg_number = null;
            $user->shop_message = null;
        $user->update();
        if($user->notivications->count() > 0)
        {
            foreach ($user->notivications as $gal) {
                $gal->delete();
            }
        }
            //--- Redirect Section
            $msg = 'Vendor Deleted Successfully.';
            return response()->json($msg);
            //--- Redirect Section Ends
    }

    //*** JSON Request
    public function withdrawdatatables()
    {
            $datas = Withdraw::where('type','=','vendor')->orderBy('id','desc')->get();
            //--- Integrating This Collection Into Datatables
            return Datatables::of($datas)
                            ->addColumn('name', function(Withdraw $data) {
                                $name = $data->user->name;
                                return '<a href="' . route('admin-vendor-show',$data->user->id) . '" target="_blank">'. $name .'</a>';
                            })
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
                                $action = '<div class="action-list"><a data-href="' . route('admin-vendor-withdraw-show',$data->id) . '" class="view details-width" data-toggle="modal" data-target="#modal1"> <i class="fas fa-eye"></i> Details</a>';
                                if($data->status == "pending") {
                                $action .= '<a data-href="' . route('admin-vendor-withdraw-accept',$data->id) . '" data-toggle="modal" data-target="#confirm-delete"> <i class="fas fa-check"></i> Accept</a><a data-href="' . route('admin-vendor-withdraw-reject',$data->id) . '" data-toggle="modal" data-target="#confirm-delete1"> <i class="fas fa-trash-alt"></i> Reject</a>';
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
        return view('admin.vendor.withdraws');
    }

    //*** GET Request
    public function withdrawdetails($id)
    {
        $sign = Currency::where('is_default','=',1)->first();
        $withdraw = Withdraw::findOrFail($id);
        return view('admin.vendor.withdraw-details',compact('withdraw','sign'));
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

    //*** FILE1 - Summaries  - Weekly Merchant Approval Report
    public function datatablesMerchantApprovalSummaries($from, $to)
    {
        $datas = $this->dataMerchantApprovalSummaries($from, $to)->get();
        return Datatables::of($datas)
            ->addColumn('total_vendor', function($data){
                return number_format($data->total_vendor);
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function exportMerchantApprovalSummaries($from, $to)
    {
        $datas = $this->dataMerchantApprovalSummaries($from, $to)->get();
        $file_name = 'vendor_approval_summaries_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new VendorApprovalSummries($datas), $file_name, null, []);
    }

    public function dataMerchantApprovalSummaries($from, $to)
    {
        $query = $this->queryMerchantApproval($from, $to);
        $days = HTDUtils::daysDiff($from, $to);
        return $query->select(
            DB::raw('p.name as province')
            , DB::raw('COUNT(u.id) as total_vendor')
            , DB::raw('COUNT(u.id) / '. $days .' as vendor_per_day'))
            ->groupBy('p.name')
            ->orderByDesc('total_vendor');
    }

    public function indexMerchantApprovalSummaries()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.vendor.reports.merchant-approval-summaries', compact('now'));
    }

    //*** FILE1 - Summaries ENDS

    //*** FILE1  - Weekly Merchant Approval Report
    public function queryMerchantApproval($from, $to)
    {
        $to = HTDUtils::addDays($to, 1);
        $query =   DB::table('users as u')
            ->leftJoin('provinces as p', 'p.id', '=', 'u.CityID')
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('u.vendor_from',[$from, $to])
                ->orWhereBetween('u.preferred_at',[$from, $to])
                ;
            });


        return $query;
    }

    public function datatablesMerchantApproval($from, $to)
    {
        $datas = $this->dataMerchantApproval($from, $to)->get();
        return Datatables::of($datas)
            ->editColumn('created_at', function($data) {
                $rs = Carbon::parse($data->created_at)->format('d-m-Y H:m');
                return $rs;
            })
            ->editColumn('approved_at', function($data) {
                $rs = isset($data->approved_at) ? Carbon::parse($data->approved_at)->format('d-m-Y H:m') : '';
                return $rs;
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function exportMerchantApproval($from, $to)
    {
        $datas = $this->dataMerchantApproval($from, $to)->get();
        $file_name = 'vendor_approval_'.Carbon::now()->format('Y-m-d H:m:s').'.xlsx';
        return Excel::download(new VendorApproval($datas), $file_name, null, []);
    }

    public function dataMerchantApproval($from, $to)
    {
        $query = $this->queryMerchantApproval($from, $to);
        $query = $query->join('user_subscriptions as subs', function ($join) {
            $join->on('u.id', '=', 'subs.user_id')
                ->on('u.vendor_from', '=', 'subs.approved_at');
        });
        return $query->select(
                DB::raw('u.shop_name as shop_name')
                , DB::raw('u.name as owner_name')
                , 'u.email'
                , 'u.phone'
                , 'p.name as province'
                , 'u.created_at'
                , 'subs.approved_at'
                , 'subs.approved_by'
                , DB::raw('case when u.preferred > 0 then "Yes" else "" end as preferred')
                )
            ->orderByDesc('u.created_at');
    }

    public function indexMerchantApproval()
    {
        $now = Carbon::now()->format('Y-m-d');
        return view('admin.vendor.reports.merchant-approval', compact('now'));
    }

    //*** FILE1 ENDS

}
