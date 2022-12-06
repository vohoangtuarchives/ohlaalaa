<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alepay;
use App\Models\DevelopmentNote;
use Illuminate\Support\Str;
use App\Enums\ModuleCode;
use Illuminate\Support\Facades\Session;
use App\Models\Order;
use App\Models\Generalsetting;
use App\Models\OrderAlepayTrackLog;
use App\Classes\GeniusMailer;
use App\Models\MemberPackageRegister;
use App\Models\MemberPackageAlepayTracklog;



class AlepayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        try{
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $inputData = array();
            $returnData = array();
            $data = $_REQUEST;
            $alepay = new Alepay;
            $result = $alepay->getTransactionInfo($data['transactionCode']);
            $orderTable = Order::where("order_number",$result->orderCode)->get()->first();
            if($orderTable){
                $order_id = $orderTable->id;
                $orderTable->method .= " ". $result->method;
                $orderTable->payment_to_company_partner = 'Alepay';
                $orderTable->payment_to_company_amount = $this->payment_to_company_amount_calculator($result->amount,$result->method);
                $orderTable->save();
            }
            foreach ($data as $key => $value) {
                $inputData[$key] = $value;
            }
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $result->orderCode;
            $note->content = serialize($_REQUEST);
            $note->note = 'Run Alepay IPN at '.date("Y-m-d H:m:s");
            $note->save();
        
            $Status = 0;
            $orderId = $result->orderCode;
            if(Str::startsWith($orderId, ModuleCode::getKey(ModuleCode::AFF))){
                $this->payment_for_member_package($orderId,$inputData);
                $register = MemberPackageRegister::where('payment_number','=',$orderId)->first();
                //$track = $this->getTrack($register->id);
                $register = MemberPackageRegister::findOrFail($register->id);
                return view('user.payment-return', compact('register'));
            }
            else{
                $this->payment_for_order($orderId, $inputData);
            }
            if(Session::has('temporder_id')){
                $order_id = Session::get('temporder_id');
                $order = Order::find($order_id);
                $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
            }
            else{
                $order_number = $result->orderCode;
                $order = Order::where('order_number','=',$order_number)->first();
                $order_id = $order->id;
                $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
            }
            $order = Order::findOrFail($order->id);
            return view('front.vnpay-return', compact('tempcart','order'));

        } catch (Exception $e) {
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->content = serialize($_REQUEST);
            $note->note = 'Run Alepay IPN error ['.$e->getMessage().']';
            $note->save();
        }
    }
    public function ipn(Request $request){
        try{
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $inputData = array();
            $returnData = array();
            $data = $request->transactionInfo;
            $alepay = new Alepay;
            $result = $alepay->getTransactionInfo($data['transactionCode']);
            $orderTable = Order::where("order_number",$result->orderCode)->get()->first();
            if($orderTable){
                $order_id = $orderTable->id;
                $orderTable->method .= " ". $result->method;
                $orderTable->payment_to_company_partner = 'Alepay';
                $orderTable->payment_to_company_amount = $this->payment_to_company_amount_calculator($result->amount,$result->method);
                $orderTable->save();
            }
            foreach ($data as $key => $value) {
                $inputData[$key] = $value;
            }
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $result->orderCode;
            $note->content = serialize($_REQUEST);
            $note->note = 'Run Alepay IPN at '.date("Y-m-d H:m:s");
            $note->save();
        
            $Status = 0;
            $orderId = $result->orderCode;
            if(Str::startsWith($orderId, ModuleCode::getKey(ModuleCode::AFF))){
                $this->payment_for_member_package($orderId,$inputData);
                $register = MemberPackageRegister::where('payment_number','=',$orderId)->first();
                //$track = $this->getTrack($register->id);
                $register = MemberPackageRegister::findOrFail($register->id);
            }
            else{
                $this->payment_for_order($orderId, $inputData);
            }
            if(Session::has('temporder_id')){
                $order_id = Session::get('temporder_id');
                $order = Order::find($order_id);
                $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
            }
            else{
                $order_number = $result->orderCode;
                $order = Order::where('order_number','=',$order_number)->first();
                $order_id = $order->id;
                $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
            }
            $order = Order::findOrFail($order->id);

        } catch (Exception $e) {
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->content = serialize($_REQUEST);
            $note->note = 'Run Alepay IPN error ['.$e->getMessage().']';
            $note->save();
        }
    }
    public function payment_for_order($order_number, $inputData)
    {
        $returnData = array();
        $order = Order::where('order_number','=',$order_number)->first();
        try {
            //Check Orderid
            if ($order != NULL) {
                if ($order["payment_status"] != 'Completed') {
                    if ((isset($inputData['errorCode']) ? $inputData['errorCode']== '000': false) || (isset($inputData['status']) ? $inputData['status']== '000': false)) {
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
                                $note->code = $order_number;
                                $note->note = 'Run Alepay IPN error in Send email ['.$e->getMessage().'] - Order ['.$order_number.']';
                                $note->save();
                            }
                        }

                    } else {
                        $Status = 2;
                        $order->order_note = $order->order_note.' - payment error with code ['.$inputData['errorCode'].'] at '.date("Y-m-d H:m:s");
                        $order->save();
                    }
                } 
            } 
        } catch (Exception $e) {
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $order_number;
            $note->note = 'Run Alepay IPN error code 99 - id ['.$order_number.'] - message ['.$e->getMessage().']';
            $note->save();
        }
        if(isset($order)){
            $note = new OrderAlepayTrackLog();
            $note->save_ipn($order->id, array($inputData, $returnData));
            try{
                $this->check_cancel_viettel_post($order);
            } catch (Exception $e) {
                $note = new DevelopmentNote;
                $note->title = 'IPN';
                $note->code = $order_number;
                $note->note = 'Run Alepay IPN error - id ['.$order_number.'] - in viettel post ['.$e->getMessage().']';
                $note->save();
            }
        }
        else{
            $note = new OrderAlepayTrackLog();
            $note->save_ipn(-1, array($inputData, $returnData));
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $order_number;
            $note->note = 'Run Alepay IPN error [order not found with - id ['.$orderId.']';
            $note->save();
        }
    }
    public function payment_for_member_package($order_number, $inputData)
    {
        $returnData = array();
        $register = MemberPackageRegister::where('payment_number','=',$order_number)->first();

        try {
            //Check Orderid
            if ($register != NULL) {

                if ($register["payment_status"] != 'Completed') {
                    if ($inputData['errorCode'] == '000' || $inputData['status']== '000') {
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
                                    $note->code = $order_number;
                                    $note->note = 'Run Alepay IPN error in Send email ['.$e->getMessage().'] - Order ['.$order_number.']';
                                    $note->save();
                                }
                            }
                        }
                    } else {
                        $Status = 2;
                        $register->payment_note = $register->payment_note.' - payment error - id ['.$order_number.'] - with code ['.$inputData['errorCode'].'] at '.date("Y-m-d H:m:s");
                        $register->save();
                    }
                }
            }
        } catch (Exception $e) {
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $order_number;
            $note->note = 'Run Alepay IPN error code 99 - id ['.$order_number.'] - message ['.$e->getMessage().']';
            $note->save();
        }
        if(isset($register)){
            $log = new MemberPackageAlepayTrackLog();
            $log->save_ipn($register->id, array($inputData, $returnData));
        }
        else{
            $log = new MemberPackageAlepayTrackLog();
            $log->save_ipn(-1, array($inputData, $returnData));
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $order_number;
            $note->note = 'Run Alepay IPN error [order not found with - id ['.$orderId.']';
            $note->save();
        }
    }

    public function check_cancel_viettel_post($order)
    {
        if($order->payment_status != 'Completed'){
            $shippings = $order->orderconsumershippingcosts()->get();
            if($shippings->count() > 0){
                $status = config('app.viettel_post.order_status');
                foreach($shippings as $sp){
                    $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->updateorderstatus($sp->shipping_partner_code, $status['cancel_order'], 'Cancel due to failed payment from alepay');
                }
            }
        }
    }
 
    public function check($orderID){
        $data = OrderAlepayTrackLog::where('order_id',$orderID)->get()->first()->content;
        $transactionCode = json_decode($data)->transactionCode;
        $alepay = new Alepay;
        $result = $alepay->getTransactionInfo($transactionCode);
        dd($result);
    }
    public function payment_to_company_amount_calculator($finalAmount,$method){
        $result = 0;
        switch ($method) {
            case 'VISA':
               $result = $finalAmount - ((($finalAmount*2.7)/100) + 3300);
               break;
            case 'MASTERCARD':
                $result = $finalAmount - ((($finalAmount*2.7)/100) + 3300);
            break;
            case 'JCB':
                $result = $finalAmount - ((($finalAmount*2.7)/100) + 3300);
                break;
            case 'ATM_ON':
                $result = $finalAmount - ((($finalAmount*1.1)/100) + 1760);
                break;
            case 'QRCODE':
                $result = $finalAmount - ((($finalAmount*1.8)/100));
                break;
            case 'VIETQR':
                $result = $finalAmount - ((($finalAmount*0.55)/100));
                break;
            case 'IB_ON':
                $result = $finalAmount - ((($finalAmount*0.55)/100));
                break;
        }
        return $result;
    }
    public function updatePaymentTo(){
        $listTransactionCode = [];
        $listOrder = Order::where('method','like','%Alepay%');
        $listOrder->update(array('payment_to_company_partner'=>'Alepay'));
        
        $listOrderJoin = $listOrder->join('order_alepay_track_logs','orders.id','order_alepay_track_logs.order_id')->get();
        // dd($listOrderJoin[35]);
        foreach($listOrderJoin as $key=>$item){
            if(substr($item->content,0,1) == 'a'){
                array_push($listTransactionCode,unserialize($item->content)[0]['transactionCode']);
            }
            elseif(substr($item->content,0,1) == 'A'){
                array_push($listTransactionCode,$item->content);
            }
            elseif(substr($item->content,0,1) == '{'){
                array_push($listTransactionCode,json_decode($item['content'])->transactionCode);
            }
            elseif(substr($item->content,0,1) == 'h'){
                $parts = parse_url($item->content);
                parse_str($parts['query'], $query);
                array_push($listTransactionCode,$query['transactionCode']);
            }
            // echo (unserialize($item->content)[0]['transactionCode']);
        }
        // dd($listTransactionCode);
        $alepay = new Alepay;
        foreach($listTransactionCode as $item){
            $transactionInfoTarget = $alepay->getTransactionInfo($item);
            $result_amount_alepay = $this->payment_to_company_amount_calculator($transactionInfoTarget->amount,$transactionInfoTarget->method);
            Order::where('order_number',$transactionInfoTarget->orderCode)->get()->first()
            ->update(['payment_to_company_amount'=>$result_amount_alepay]);
        }
        return 'Success';
        // return json_encode(($alepay->getTransactionInfo($listTransactionCode[1]))->transactionCode);

        // dd($listOrderJoin[14]->content);
        // return json_encode($listOrderJoin[11]->content);
        // return(unserialize($listOrderJoin[11]->content));
    }
}
