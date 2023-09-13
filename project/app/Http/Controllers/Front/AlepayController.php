<?php

namespace App\Http\Controllers\Front;

use App\Models\Product;
use Auth;
use App\Models\Order;
use App\Models\Alepay;
use App\Models\VendorOrder;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class AlepayController extends Controller
{
    public function cancel(){
        $type = "cancelAlepay";
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
                $point_log_declined->descriptions = 'B?n du?c ho�n tr? shopping point c?a don h�ng s? ['.$data->order_number.']';
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

                $vendor_order_target = VendorOrder::where('order_id',$order_id)->get()->first();
                $vendor_order_target->status = "declined";
                $vendor_order_target->save();
                session()->forget('orderNumber');
                return view('front.vnpay-return', compact('tempcart','order','type'));
            }
        }
        // return redirect()->back()->with('unsuccess',"Unauthenticated!");
        return redirect()->route('front.index');
    }
}
