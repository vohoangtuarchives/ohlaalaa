<?php

namespace App\Http\Controllers\Client;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\Onepay;
use App\Models\DevelopmentNote;
use Illuminate\Support\Str;
use App\Enums\ModuleCode;
use Illuminate\Support\Facades\Session;
use App\Models\Order;
use App\Models\Generalsetting;
use App\Models\OrderOnepayTrackLog;
use App\Classes\GeniusMailer;
use App\Models\MemberPackageRegister;
use App\Models\VendorOrder;
use App\Models\UserPointLog;

class OnepayController extends Controller
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
            $onepay = new Onepay;
            // Thanh toán thành không khi code = 0, và dữ liệu toàn vẹn
            if($data['vpc_TxnResponseCode'] == '0' && $onepay->createSecureHash($data)){
                $orderTable = Order::where("order_number",$data['vpc_OrderInfo'])->get()->first();
                $order_id = $orderTable->id;
                // $orderTable->method .= " ". $result->method;
                // $orderTable->payment_to_company_amount = $this->payment_to_company_amount_calculator($data['vpc_Amount']/100,$data['']);
                // $orderTable->save();
                foreach ($data as $key => $value) {
                    $inputData[$key] = $value;
                }
    
    
                $note = new DevelopmentNote;
                $note->title = 'IPN';
                $note->code = $inputData['vpc_TxnResponseCode'];
                $note->content = serialize($_REQUEST);
                $note->note = 'Run Onepay IPN at '.date("Y-m-d H:m:s");
                $note->save();
            
                $Status = 0;
                $order_number = $data['vpc_OrderInfo'];
                // if(Str::startsWith($orderId, ModuleCode::getKey(ModuleCode::AFF))){
                //     $this->payment_for_member_package($orderId,$inputData);
                // }
                // else{
                $this->payment_for_order($order_number, $inputData);

                // }
                if(Session::has('temporder_id')){
                    $order_id = Session::get('temporder_id');
                    $order = Order::find($order_id);
                    $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
                }
                else{
                    $order = Order::where('order_number','=',$order_number)->first();
                    $order_id = $order->id;
                    $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
                }
                $order = Order::findOrFail($order->id);
                return redirect()->route('onepay.return')->with(['tempcart'=>$tempcart,'order'=>$order]);
                // return view('front.vnpay-return', compact('tempcart','order'));
                
            }
            else{
                echo $this->cancel();
            }

        } catch (Exception $e) {
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->content = serialize($_REQUEST);
            $note->note = 'Run Onepay IPN error ['.$e->getMessage().']';
            $note->save();
        }
    }
    public function payment_for_order($orderNumber, $inputData)
    {
        $returnData = array();
        $order = Order::where('order_number','=',$orderNumber)->first();
        try {
            //Check Orderid
            if ($order != NULL) {
                if ($order["payment_status"] != 'Completed') {
                    if ($inputData['vpc_TxnResponseCode'] == '0') {
                        
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
                                $note->note = 'Run Onepay IPN error in Send email ['.$e->getMessage().'] - Order ['.$orderNumber.']';
                                $note->save();
                            }
                        }

                    } else {
                        $Status = 2;
                        $order->order_note = $order->order_note.' - payment error with code ['.$inputData['vpc_TxnResponseCode'].'] at '.date("Y-m-d H:m:s");
                        $order->save();
                    }
                } 
            } 
        } catch (Exception $e) {
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $orderNumber;
            $note->note = 'Run Onepay IPN error code 99 - id ['.$orderNumber.'] - message ['.$e->getMessage().']';
            $note->save();
        }
        if(isset($order)){
            $note = new OrderOnepayTrackLog();
            $note->save_ipn($order->id, array($inputData, $returnData));
            $note = new OrderOnepayTrackLog();
            $note->save_ipn_url($order->id, (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            try{
                $this->check_cancel_viettel_post($order);
            } catch (Exception $e) {
                $note = new DevelopmentNote;
                $note->title = 'IPN';
                $note->code = $orderId;
                $note->note = 'Run Onepay IPN error - id ['.$orderId.'] - in viettel post ['.$e->getMessage().']';
                $note->save();
            }
        }
        else{
            $note = new OrderOnepayTrackLog();
            $note->save_ipn(-1, array($inputData, $returnData));
            $note = new DevelopmentNote;
            $note->title = 'IPN';
            $note->code = $orderId;
            $note->note = 'Run Onepay IPN error [order not found with - id ['.$orderId.']';
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
    public function cancel(){
        $order = Order::where('order_number','=',$_REQUEST['vpc_OrderInfo'])->first();
        if( isset($order) ) {
            $note = new OrderOnepayTrackLog();
            $note->save_ipn($order->id, array($_REQUEST, array()));
        }
        $type = "cancelOnepay";
        if(Auth::check()){
            $data = Order::findOrFail(session()->get('orderNumber'));
            $cart = unserialize(bzdecompress(utf8_decode($data->cart)));
            try{
                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['stock'];

                    if($x != null)
                    {
                        
                        $product = Product::find($prod['item']['id']);
                        if(isset($product)){
                            $product->stock = $product->stock + $prod['qty'];
                            $product->update();
                        }
                    }
                }
                foreach($cart->items as $prod)
                {
                    $x = (string)$prod['size_qty'];
                    if(!empty($x))
                    {
                        $product = Product::find($prod['item']['id']);
                        if(isset($product)){
                            $x = (int)$x;
                            $temp = $product->size_qty;
                            $temp[$prod['size_key']] = $x;
                            $temp1 = implode(',', $temp);
                            $product->size_qty =  $temp1;
                            $product->update();
                        }
                    }
                }
            }
            catch (\Exception $e){
                $msg = $e->getMessage();
            }
            $gs = Generalsetting::findOrFail(1);
            if($gs->is_smtp == 1)
            {
                $maildata = [
                    'to' => $data->customer_email,
                    'subject' => 'Your order '.$data->order_number.' is Declined!',
                    'body' => "Hello ".$data->customer_name.","."\n We are sorry for the inconvenience caused. We are looking forward to your next visit.",
                ];
                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($maildata);
            }
            else
            {
                $to = $data->customer_email;
                $subject = 'Your order '.$data->order_number.' is Declined!';
                $msg = "Hello ".$data->customer_name.","."\n We are sorry for the inconvenience caused. We are looking forward to your next visit.";
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($to,$subject,$msg,$headers);
            }

            $sp_declined =  $data->vendororders->where('status', 'declined')->sum('shopping_point_used');
            
            $sp_handling = $data->shopping_point_used - $sp_declined;
            $consumer = $data->user()->first();
            if($sp_handling > 0){
                $point_log_declined = new UserPointLog;
                $point_log_declined->user_id = $consumer->id;
                $point_log_declined->log_type = 'Order Declined';
                $point_log_declined->order_ref_id = $data->id;
                $point_log_declined->reward_point_balance = isset($consumer->reward_point) ? $consumer->reward_point : 0;
                $point_log_declined->shopping_point_balance = isset($consumer->shopping_point) ? $consumer->shopping_point : 0;
                $point_log_declined->exchange_rate = 0;
                $point_log_declined->note = 'Return from order ['.$data->id.'] - SP Declined = '.$sp_declined;
                $point_log_declined->descriptions = 'Bạn được hoàn trả shopping point của đơn hàng số ['.$data->order_number.']';
                $point_log_declined->reward_point = 0;
                $point_log_declined->shopping_point = $sp_handling;
                $point_log_declined->amount = $data->pay_amount1;
                $point_log_declined->sp_vnd_exchange_rate = $data->shopping_point_exchange_rate;
                $consumer->shopping_point = $consumer->shopping_point + $sp_handling;
                $consumer->save();
                $point_log_declined->save();
            }
            $shippings = $data->orderconsumershippingcosts()->get();
            if($shippings->count() > 0){
                $status_vtel = config('app.viettel_post.order_status');
                foreach($shippings as $sp){
                    $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->updateorderstatus($sp->shipping_partner_code, $status_vtel['cancel_order'], 'Cancel due to declined order');
                }
            }
            if(session()->get('orderNumber')){
                $order_id = session()->get('orderNumber');
                $order = Order::find($order_id);
                $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
                $order->status = "declined";
                $order->save();
                
                // $vendor_order_target = VendorOrder::where('order_id',$order_id)->get()->first();
                // $vendor_order_target->status = "declined";
                // $vendor_order_target->save();
                session()->forget('orderNumber');
                return redirect()->route('onepay.cancel')->with(['tempcart'=>$tempcart,'order'=>$order,'type'=>$type]);
            }
        }
        // return redirect()->back()->with('unsuccess',"Unauthenticated!");
        return redirect()->route('front.index');
    }
    public function payment_to_company_amount_calculator($finalAmount,$method){
        $result = 0;
        switch ($method) {
            case 'VISA':
               $result = $finalAmount - ((($finalAmount*2.7)/100) + 3300);
               break;
        }
        return $result;
    }

    public function returnResult(){
        $order = Session::get('order');
        $tempcart = Session::get('tempcart');
        return view('front.payment-return', compact('tempcart','order'));
    }
    public function returnCancelView(){
        $order = Session::get('order');
        $tempcart = Session::get('tempcart');
        $type = Session::get('type');
        return view('front.payment-return', compact('tempcart','order','type'));
    }
    public function queryDRResult($order_id){
        $queryData = OrderOnepayTrackLog::where('order_id',$order_id)->where('title','IPN')->get()->first();
        if(!$queryData){
            $data['status'] = "001";
            return response()->json($data);
        }
        $queryData = unserialize($queryData->content)[0];
        $onepay = new Onepay;
        $result = $onepay->queryDR($queryData['vpc_MerchTxnRef']);
        $order = Order::where('id','=',$order_id)->first();
        $this->payment_for_order($order->order_number,$this->http_parse_query($result));
        $data['status'] = "000";
        return response()->json($data);

    }
    public function http_parse_query($query) {
        $parameters = array();
        $queryParts = explode('&', $query);
        foreach ($queryParts as $queryPart) {
            $keyValue = explode('=', $queryPart, 2);
            $parameters[$keyValue[0]] = $keyValue[1];
        }
        return $parameters;
    }
}
