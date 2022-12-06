<?php

namespace App\Http\Controllers\Client;

use Exception;
use App\Models\Order;
use App\Enums\ModuleCode;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\DevelopmentNote;
use App\Models\OrderVnpayTrackLog;
use App\Http\Controllers\Controller;
use App\Models\MemberPackageRegister;
use App\Models\MemberPackageVnpayTrackLog;

class VNPayController extends Controller
{
    public function ipn(){
        try{
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

            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $inputData['vnp_TxnRef'];
            $note->content = serialize($_REQUEST);
            $note->note = 'Run VNpay IPN at '.date("Y-m-d H:m:s");
            $note->save();
            $vnp_SecureHash = $inputData['vnp_SecureHash'];
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
            $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
            $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán

            //$secureHash = md5($vnp_HashSecret . $hashData);
            $secureHash = hash('sha256',$config["IPNSecret"] . $hashData);

            //return array('_REQUEST' => $_REQUEST, 'inputData' => $inputData, 'secureHash' => $secureHash);


            $Status = 0;
            $orderId = $inputData['vnp_TxnRef'];

            if(Str::startsWith($orderId, ModuleCode::getKey(ModuleCode::AFF))){
                echo $this->payment_for_member_package($orderId, $secureHash, $vnp_SecureHash, $inputData);
            }
            else{
                echo $this->payment_for_order($orderId, $secureHash, $vnp_SecureHash, $inputData);
            }

        } catch (Exception $e) {
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->content = serialize($_REQUEST);
            $note->note = 'Run VNpay IPN error ['.$e->getMessage().']';
            $note->save();
        }
    }

    public function payment_for_order($orderId, $secureHash, $vnp_SecureHash, $inputData)
    {
        $order = Order::where('order_number','=',$orderId)->first();

        try {
            //Check Orderid
            //Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {
                //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId
                //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
                //Giả sử: $order = mysqli_fetch_assoc($result);

                if ($order != NULL) {
                    $order_amount = round($order->pay_amount2 * 100);
                    $vnp_Amount = $inputData['vnp_Amount'];
                    if($order_amount != $vnp_Amount){
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'Invalid amount';
                        $order->order_note = $order->order_note.' - payment error with code ['.$inputData['vnp_ResponseCode'].'] at '.date("Y-m-d H:m:s").'[04][Invalid amount]';
                        $order->save();
                    }
                    else{
                        if ($order["payment_status"] != 'Completed') {
                            if ($inputData['vnp_ResponseCode'] == '00') {
                                $Status = 1;
                                $order->payment_status = 'Completed';
                                $order->order_note = $order->order_note.' - payment complete at '.date("Y-m-d H:m:s");
                                $order->save();

                                $gs = Generalsetting::findOrFail(1);
                                if($gs->is_smtp == 1)
                                {
                                    $data1 = [
                                        'to' => $order->customer_email,
                                        'type' => "new_order",
                                        'cname' => $order->customer_name,
                                        'oamount' => "",
                                        'aname' => "",
                                        'aemail' => "",
                                        'wtitle' => "",
                                        'onumber' => $order->order_number,
                                    ];
                                    $mailer = new GeniusMailer();
                                    try{
                                        $rs = $mailer->sendAutoOrderMail1($data1,$order->id);
                                    } catch (Exception $e) {
                                        $note = new DevelopmentNote;
                                        $note->title = 'IPN';
                                        $note->code = $orderId;
                                        $note->note = 'Run VNpay IPN error in Send email ['.$e->getMessage().'] - Order ['.$orderId.']';
                                        $note->save();
                                    }
                                }
                                $returnData['RspCode'] = '00';
                                $returnData['Message'] = 'Confirm Success';
                            } else {
                                $returnData['RspCode'] = '00';
                                $returnData['Message'] = 'Confirm Success';
                                $Status = 2;
                                $order->order_note = $order->order_note.' - payment error with code ['.$inputData['vnp_ResponseCode'].'] at '.date("Y-m-d H:m:s");
                                $order->save();
                            }
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Chu ky khong hop le';
            }
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error';
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $orderId;
            $note->note = 'Run VNpay IPN error code 99 - id ['.$orderId.'] - message ['.$e->getMessage().']';
            $note->save();
        }
        if(isset($order)){
            $note = new OrderVnpayTrackLog();
            $note->save_ipn($order->id, array($inputData, $returnData));
            try{
                $this->check_cancel_viettel_post($order);
            } catch (Exception $e) {
                $note = new DevelopmentNote;
                $note->title = 'IPN';
                $note->code = $orderId;
                $note->note = 'Run VNpay IPN error - id ['.$orderId.'] - in viettel post ['.$e->getMessage().']';
                $note->save();
            }
        }
        else{
            $note = new OrderVnpayTrackLog();
            $note->save_ipn(-1, array($inputData, $returnData));
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $orderId;
            $note->note = 'Run VNpay IPN error [order not found with - id ['.$orderId.']';
            $note->save();
        }
        //Trả lại VNPAY theo định dạng JSON
        return json_encode($returnData);
    }

    public function payment_for_member_package($orderId, $secureHash, $vnp_SecureHash, $inputData)
    {
        $register = MemberPackageRegister::where('payment_number','=',$orderId)->first();

        try {
            //Check Orderid
            //Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {
                //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId
                //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
                //Giả sử: $order = mysqli_fetch_assoc($result);

                if ($register != NULL) {

                    $order_amount = round($register->package_price * 100);
                    $vnp_Amount = $inputData['vnp_Amount'];

                    if($order_amount != $vnp_Amount){
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'Invalid amount';
                        $register->payment_note = $register->payment_note.' - payment error with code ['.$inputData['vnp_ResponseCode'].'] at '.date("Y-m-d H:m:s").'[04][Invalid amount]';
                        $register->save();
                    }
                    else{
                        if ($register["payment_status"] != 'Completed') {
                            if ($inputData['vnp_ResponseCode'] == '00') {
                                $Status = 1;
                                $register->payment_status = 'Completed';
                                $register->payment_complete_at = date("Y-m-d H:m:s");
                                $register->payment_note = $register->payment_note.' - payment complete at '.date("Y-m-d H:m:s");
                                $register->save();
                                $mail_list = explode(';',$register->package_config->approval_list);
                                $gs = Generalsetting::findOrFail(1);
                                foreach($mail_list as $mail){
                                    if($gs->is_smtp == 1)
                                    {
                                        $data = [
                                            'to' => $mail,
                                            'type' => "admin_membership_application",
                                            'cname' => $mail,
                                            'oamount' => "",
                                            'aname' => "",
                                            'aemail' => "",
                                            'onumber' => "",
                                        ];

                                        $mailer = new GeniusMailer();
                                        try{
                                            $rs = $mailer->sendAutoMail($data);
                                        } catch (Exception $e) {
                                            $note = new DevelopmentNote;
                                            $note->title = 'IPN';
                                            $note->code = $orderId;
                                            $note->note = 'Run VNpay IPN error in Send email ['.$e->getMessage().'] - Order ['.$orderId.']';
                                            $note->save();
                                        }
                                    }
                                }
                                $returnData['RspCode'] = '00';
                                $returnData['Message'] = 'Confirm Success';
                            } else {
                                $returnData['RspCode'] = '00';
                                $returnData['Message'] = 'Confirm Success';
                                $Status = 2;
                                $register->payment_note = $register->payment_note.' - payment error - id ['.$orderId.'] - with code ['.$inputData['vnp_ResponseCode'].'] at '.date("Y-m-d H:m:s");
                                $register->save();
                            }
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Chu ky khong hop le';
            }
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error: '.$e->getMessage();
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $orderId;
            $note->note = 'Run VNpay IPN error code 99 - id ['.$orderId.'] - message ['.$e->getMessage().']';
            $note->save();
        }
        if(isset($register)){
            $log = new MemberPackageVnpayTrackLog();
            $log->save_ipn($register->id, array($inputData, $returnData));
        }
        else{
            $log = new MemberPackageVnpayTrackLog();
            $log->save_ipn(-1, array($inputData, $returnData));
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $orderId;
            $note->note = 'Run VNpay IPN error [order not found with - id ['.$orderId.']';
            $note->save();
        }
        //Trả lại VNPAY theo định dạng JSON
        return json_encode($returnData);
    }

    public function check_cancel_viettel_post($order)
    {
        if($order->payment_status != 'Completed'){
            $shippings = $order->orderconsumershippingcosts()->get();
            if($shippings->count() > 0){
                $status = config('app.viettel_post.order_status');
                foreach($shippings as $sp){
                    $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->updateorderstatus($sp->shipping_partner_code, $status['cancel_order'], 'Cancel due to failed payment from vnpay');
                }
            }
        }
    }
}
