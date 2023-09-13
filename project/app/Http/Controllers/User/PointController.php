<?php

namespace App\Http\Controllers\User;

use App\Models\UserPointLog;
use Auth;
use Illuminate\Support\Facades\DB;
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

class PointController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(){
        $user = \Illuminate\Support\Facades\Auth::user();
        $transactions = $user->transactions()->get();
        if($user->can_transfer_point){
            return view('user.transfer-point',compact('user', 'transactions'));
        }
        abort(404);
    }

    public function transfer(Request $request){
        $rules = [
            'to_customer' => 'required|email',
            'amount' => 'required|numeric'
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            dd($validator);
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $amount = $request->get("amount");

        $toCustomer = $request->get("to_customer");

        $fromCustomerEmail = \Illuminate\Support\Facades\Auth::user()->email;

        $fromCustomer = User::where("email", "=", $fromCustomerEmail)->first();

        $transfered_shopping_point = $fromCustomer->transfered_shopping_point + $amount;

        if($amount > $fromCustomer->max_transfer_point - $fromCustomer->transfered_shopping_point){
            return redirect()->back()->with('success','Số lượng SP muốn chuyển quá lớn!');
        }

        if($amount > $fromCustomer->shopping_point){
            return redirect()->back()->with('success','Số lượng SP của bạn không đủ!');
        }


        if($fromCustomer->can_transfer_point){

           DB::beginTransaction();

           try {

               $toCustomer = User::where("email", "=", $toCustomer)->first();

//               $fromCustomer->shopping_point = $fromCustomer->shopping_point - $amount;

//               $toCustomer->shopping_point = $toCustomer->shopping_point + $amount;

               $point_exchange = $amount;

               $point_log = new UserPointLog();
               $point_log->user_id = $fromCustomer->id;

               if($fromCustomer->email != 'demo@demo.com'){
                   $point_log->log_type = 'User Transfer Point';
                   $point_log->shopping_point_balance = isset($fromCustomer->shopping_point) ? $fromCustomer->shopping_point : 0;
                   $point_log->exchange_rate = 1;
                   $point_log->note = "Hệ thống chuyển {$point_exchange} SP của bạn sang cho ".$toCustomer->email;
                   $point_log->descriptions = "Hệ thống chuyển {$point_exchange} SP của bạn sang cho ".$toCustomer->email;
                   $point_log->shopping_point = -$point_exchange;

                   $fromCustomer->transfered_shopping_point = $transfered_shopping_point;
                   $fromCustomer->shopping_point = $fromCustomer->shopping_point - $point_exchange;
               }
               $fromCustomer->save();

               $point_log->save();

               $point_log = new UserPointLog();
               $point_log->user_id = $toCustomer->id;

               $point_log->log_type = 'User Transfer Point';
               $point_log->shopping_point_balance = isset($toCustomer->shopping_point) ? $toCustomer->shopping_point : 0;
               $point_log->exchange_rate = 1;
               $point_log->note = ($fromCustomer->email == 'demo@demo.com'? 'Ohlaalaa': $fromCustomer->email ). " chuyển {$point_exchange} SP cho bạn";
               $point_log->descriptions = ($fromCustomer->email == 'demo@demo.com'? 'Ohlaalaa': $fromCustomer->email ). " chuyển {$point_exchange} SP cho bạn";
               $point_log->shopping_point = $point_exchange;
               $toCustomer->shopping_point = $toCustomer->shopping_point + $point_exchange;
               $toCustomer->save();
               $point_log->save();



               DB::commit();

               user_log(\App\Models\UserTransaction::USER_TRANSFER_POINT, "Chuyển ". $amount. " SP sang ".$toCustomer->email);
               // all good
           } catch (\Exception $e) {
                dd($e->getMessage());
               DB::rollback();
               return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
           }
           $msg = 'Data Updated Successfully.';

           return redirect()->back()->with('success','Yêu cầu chuyển điểm đã được gửi thành công!');
       }else{
           return redirect()->back()->with('error','Có lỗi xảy ra!');
       }

    }
}