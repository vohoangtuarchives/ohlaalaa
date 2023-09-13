<?php

namespace App\Http\Controllers\User;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MemberPackageRegister;
use App\Enums\ApprovalStatus;
use App\Models\Generalsetting;
use App\Classes\GeniusMailer;

class AlepayController extends Controller
{
    public function cancel(){
        $orderId = session()->get('orderID');
        $register = MemberPackageRegister::find($orderId);
        // $issuer = Auth::guard('admin')->user();
        $dateString = date("Y-m-d H:m:s");
        $register->rejected_at = $dateString;
        // $register->rejected_by = $issuer->id;
        $register->approval_status = ApprovalStatus::Rejected;
        $register->save();
        $user = $register->user()->first();
        $settings = Generalsetting::findOrFail(1);
        session()->forget('orderID');
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
            $register = MemberPackageRegister::findOrFail($orderId);

            return view('user.payment-return', compact('register'));
        }
        else
        {
            $headers = "From: ".$settings->from_name."<".$settings->from_email.">";
            mail($user->email,'Your request for membership have been rejected','Your request for membership have been rejected. Please contact us for more details.',$headers);
        }
    }
}
