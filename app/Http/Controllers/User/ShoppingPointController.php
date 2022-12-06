<?php

namespace App\Http\Controllers\User;

use Validator;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use App\Classes\HTTPRequester;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;
use Auth;

class ShoppingPointController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function sending_index()
    {
        $user = Auth::guard('web')->user();
        return view('user.send-shopping-point',compact('user'));
    }

    public function sending_check($phonenumber)
    {
        $data = array();
        $url = config('app.global_ecommerce.verify_user_url');
        //return response()->json($url);
        $requestArr = array('mobilenum' => $phonenumber);
        $response = HTTPRequester::HTTPPostBodyAccept($url, $requestArr);
        if($response != null){
            if($response['success'] == true){
                $data['status'] = 1;
                $data['value'] = true;
                $data['message'] = '';
            }
            else{
                $data['status'] = 1;
                $data['value'] = false;
                $data['message'] = '';
            }
        }
        else{
            $data['status'] = 0;
            $data['value'] = null;
            $data['message'] = 'error';
        }
        return response()->json($data);
    }

    public function send_global(Request $request)
    {
        $rules =[
            'email' => 'required|email',
            'phone_number' => 'required',
            'sending_shopping_point' => 'numeric|min:1',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->with('unsuccess',$validator->getMessageBag()->first());
            //return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        $input = $request->all();
        $user = Auth::user();
        $sp_balance = $user->shopping_point - $input['sending_shopping_point'];
        if($sp_balance < 0){
            return redirect()->back()->with('unsuccess','Số dư shopping point không đủ!');
            // return response()->json(array('errors' => ['Số dư shopping point không đủ!']));
        }

        $url = config('app.global_ecommerce.transfer_url');
        $requestArr = array(
            'mobilenum' => $input['phone_number'],
            'email' => $input['email'],
            'sendamount' => $input['sending_shopping_point']
        );
        $gs = Generalsetting::findOrFail(1);
        $response = HTTPRequester::HTTPPostBodyAccept($url, $requestArr);
        if($response != null){
            if($response['success'] == true){

                $point_log = new UserPointLog;
                $point_log->user_id = $user->id;
                $point_log->log_type = 'Global Transfer';
                $point_log->order_ref_id = 0;
                $point_log->reward_point_balance = isset($user->reward_point) ? $user->reward_point : 0;
                $point_log->shopping_point_balance = $user->shopping_point;
                $point_log->exchange_rate = 0;
                $point_log->note = 'Transfer to phone number ['.$input['phone_number'].']';
                $point_log->descriptions = 'Bạn chuyển shopping point sang global-ecommerce cho số điện thoại ['.$input['phone_number'].']';
                $point_log->reward_point = 0;
                $point_log->shopping_point = $input['sending_shopping_point'];
                $point_log->amount = 0;
                $point_log->sp_vnd_exchange_rate = $gs->sp_vnd_exchange_rate;
                $user->shopping_point = $user->shopping_point - $point_log->shopping_point;
                $user->save();
                $point_log->save();

                return redirect()->back()->with('success','Chuyển đổi shopping point thành công!');
                //return view('user.send-shopping-point',compact('user'));
            }
            else{
                return redirect()->back()->with('unsuccess','Số điện thoại không hợp lệ');
            }
        }
        else{
            return redirect()->back()->with('unsuccess','response null');
        }
    }
}
