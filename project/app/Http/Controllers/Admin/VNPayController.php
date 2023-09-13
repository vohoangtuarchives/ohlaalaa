<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\DevelopmentNote;
use App\Models\OrderVnpayTrackLog;
use App\Http\Controllers\Controller;
use App\Models\MemberPackageRegister;
use App\Models\MemberPackageVnpayTrackLog;

class VNPayController extends Controller
{
    public function view_order_ipn($id = -1)
    {
        $order = null;
        if($id == -1){
            $order = Order::orderByDesc('id')->take(1)->first();
        }
        else{
            $order = Order::find($id);
        }
        $logs = OrderVnpayTrackLog::where('order_id', $order->id)->get();
        $l_ipn = null;
        foreach($logs as $l){
            if($l->title == 'IPN'){
                $l_ipn[] = $l;
                $l_ipn[] = unserialize($l->content);
            }
        }
        dd($l_ipn) ;
    }

    public function view_membership_ipn($id = -1)
    {
        $order = null;
        if($id == -1){
            $order = MemberPackageRegister::orderByDesc('id')->take(1)->first();
        }
        else{
            $order = MemberPackageRegister::find($id);
        }
        $logs = MemberPackageVnpayTrackLog::where('mpr_id', $order->id)->get();
        $l_ipn = null;
        foreach($logs as $l){
            if($l->title == 'IPN'){
                $l_ipn[] = $l;
                $l_ipn[] = unserialize($l->content);
            }
        }
        dd($l_ipn) ;
    }

    public function view_request($id = '')
    {
        $logs = DevelopmentNote::where('code', '=', $id)
            ->where('title', '=', 'IPN')
            ->get();
        $l_ipn = null;
        foreach($logs as $l){
            $l_ipn[] = $l;
            $l_ipn[] = unserialize($l->content);
        }
        return $l_ipn;
    }

    public function hash_key()
    {
        $config = config('app.vnpay');
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $inputData = array();
        $returnData = array();

        //return ($_REQUEST);
        $data = $_REQUEST;
        foreach ($data as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        unset($inputData['api_token']);
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
        $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán

        //$secureHash = md5($vnp_HashSecret . $hashData);
        $secureHash = hash('sha256',$config["IPNSecret"] . $hashData);

        return array('_REQUEST' => $_REQUEST, 'inputData' => $inputData, 'secureHash' => $secureHash);
    }
}
