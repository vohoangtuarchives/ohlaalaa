<?php

namespace App\Http\Controllers\User;

use App\Models\DevelopmentNote;
use App\Http\Controllers\Controller;
use App\Models\MemberPackageRegister;
use App\Models\MemberPackageVnpayTrackLog;

class VNPayController extends Controller
{
    public function getTrack($id)
    {
        $track = MemberPackageVnpayTrackLog::where('mpr_id', '=', $id)
            ->where('title', '=', 'IPN')
            ->first();
        if(!isset($track))
            return $this->getTrack($id);
        return $track;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config = config('app.vnpay');
        $order_number = $_GET['vnp_TxnRef'];
        $register = MemberPackageRegister::where('payment_number','=',$order_number)->first();
        //$track = $this->getTrack($register->id);
        $register = MemberPackageRegister::findOrFail($register->id);
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }
        return view('user.vnpay-return', compact('register'));
    }
}
