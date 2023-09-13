<?php
/**
 * Created by PhpStorm.
 * User: ShaOn
 * Date: 11/29/2018
 * Time: 12:49 AM
 */

namespace App\Classes;

use Config;
use App\User;
use App\Models\Cart;
use App\Models\Order;
//use PDF;
//use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Models\Generalsetting;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Mail;

class GeniusMailer
{
    public function __construct()
    {
        $gs = Generalsetting::findOrFail(1);
        if($gs->header_email == 'smtp') {
            $mail_driver = 'smtp';
        }
        else{
            if($gs->header_email == 'sendmail') {
                $mail_driver = 'sendmail';
            }
            else {
                $mail_driver = 'smtp';
            }
        }
        Config::set('mail.driver', $mail_driver);
        Config::set('mail.host', $gs->smtp_host);
        Config::set('mail.port', $gs->smtp_port);
        Config::set('mail.encryption', $gs->email_encryption);
        Config::set('mail.username', $gs->smtp_user);
        Config::set('mail.password', $gs->smtp_pass);
    }

    public function sendAutoOrderMail(array $mailData,$id)
    {
        $setup = Generalsetting::find(1);
        $temp = EmailTemplate::where('email_type','=',$mailData['type'])->first();
        $body = preg_replace("/{customer_name}/", $mailData['cname'] ,$temp->email_body);
        $body = preg_replace("/{order_amount}/", $mailData['oamount'] ,$body);
        $body = preg_replace("/{admin_name}/", $mailData['aname'] ,$body);
        $body = preg_replace("/{admin_email}/", $mailData['aemail'] ,$body);
        $body = preg_replace("/{order_number}/", $mailData['onumber'] ,$body);
        $body = preg_replace("/{website_title}/", $setup->title ,$body);

        $data = [
            'email_body' => $body
        ];


        $objDemo = new \stdClass();
        $objDemo->to = $mailData['to'];
        $objDemo->from = $setup->from_email;
        $objDemo->title = $setup->from_name;
        $objDemo->subject = $temp->email_subject;

        try{
            // $fileName = public_path('assets/temp_files/').Str::random(10).'.pdf';
            // $order = Order::findOrFail($id);
            // $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
            // $pdf = PDF::loadView('print.test', compact('order', 'cart'));
            // $pdf->save($fileName);
            // return 'pass pdf---'.$fileName;

            Mail::send('admin.email.mailbody',$data, function ($message) use ($objDemo,$id) {
                $message->from($objDemo->from,$objDemo->title);
                $message->to($objDemo->to);
                $message->subject($objDemo->subject);
                $order = Order::findOrFail($id);
                $cart = unserialize(bzdecompress(utf8_decode($order->cart)));
                $fileName = public_path('assets/temp_files/').Str::random(10).'.pdf';
                $pdf = PDF::loadView('print.order', compact('order', 'cart'))->save($fileName);
                $message->attach($fileName);
            });
            return true;

        }
        catch (\Exception $e){
             return $e->getMessage();
        }

        $files = glob('assets/temp_files/*'); //get all file names
        foreach($files as $file){
            if(is_file($file))
            unlink($file); //delete file
        }
    }

    public function sendAutoOrderMail1(array $mailData,$id)
    {
        $setup = Generalsetting::find(1);
        $temp = EmailTemplate::where('email_type','=',$mailData['type'])->first();
        $body = preg_replace("/{customer_name}/", $mailData['cname'] ,$temp->email_body);
        $body = preg_replace("/{order_amount}/", $mailData['oamount'] ,$body);
        $body = preg_replace("/{admin_name}/", $mailData['aname'] ,$body);
        $body = preg_replace("/{admin_email}/", $mailData['aemail'] ,$body);
        $body = preg_replace("/{order_number}/", $mailData['onumber'] ,$body);
        $body = preg_replace("/{website_title}/", $setup->title ,$body);
        $order = Order::findOrFail($id);
        $cart = unserialize(bzdecompress(utf8_decode($order->cart)));

        $data = [
            'order' => $order,
            'cart' => $cart
        ];

        $objDemo = new \stdClass();
        $objDemo->to = $mailData['to'];
        $objDemo->from = $setup->from_email;
        $objDemo->title = $setup->from_name;
        $objDemo->subject = $temp->email_subject;

        try{
            Mail::send('admin.email.order',$data, function ($message) use ($objDemo,$id) {
                $message->from($objDemo->from,$objDemo->title);
                $message->to($objDemo->to);
                $message->subject($objDemo->subject);
            });
            return true;
        }
        catch (\Exception $e){
             return $e->getMessage();
        }
    }

    public function sendAutoOrderMail2($id)
    {
        $setup = Generalsetting::find(1);
        $order = Order::findOrFail($id);
        $oldCart = unserialize(bzdecompress(utf8_decode($order->cart)));
        $cart = new Cart($oldCart);
        $user = null;
        foreach ($cart->items as $prod) {
            if($prod['item']->type == 'Physical')
            {
                $user[] = $prod['item']['user_id'];
            }
        }
        if( $user == null){
            return 'Vendor not found!';
        }
        $users = array_unique($user);
        foreach($users as $vendor_id){
            $vendor = User::findOrFail($vendor_id);
            $data = [
                'order' => $order,
                'cart' => $cart,
                'user' => $vendor
            ];

            $objDemo = new \stdClass();
            $objDemo->to = $vendor->email;
            $objDemo->from = $setup->from_email;
            $objDemo->title = $setup->from_name;
            $objDemo->subject = 'Order Notification';

            try{
                Mail::send('admin.email.vendor-order', $data, function ($message) use ($objDemo,$id) {
                    $message->from($objDemo->from,$objDemo->title);
                    $message->to($objDemo->to);
                    $message->subject($objDemo->subject);
                });
            }
            catch (\Exception $e){
                 return $e->getMessage().' Email: ['.$vendor->email.']';
            }
        }
        return true;
    }

    public function sendAutoMail(array $mailData)
    {
        $setup = Generalsetting::find(1);
        $temp = EmailTemplate::where('email_type','=',$mailData['type'])->first();
        $body = preg_replace("/{customer_name}/", $mailData['cname'] ,$temp->email_body);
        $body = preg_replace("/{order_amount}/", $mailData['oamount'] ,$body);
        $body = preg_replace("/{admin_name}/", $mailData['aname'] ,$body);
        $body = preg_replace("/{admin_email}/", $mailData['aemail'] ,$body);
        $body = preg_replace("/{order_number}/", $mailData['onumber'] ,$body);
        $body = preg_replace("/{website_title}/", $setup->title ,$body);

        $data = [
            'email_body' => $body
        ];

        $objDemo = new \stdClass();
        $objDemo->to = $mailData['to'];
        $objDemo->from = $setup->from_email;
        $objDemo->title = $setup->from_name;
        $objDemo->subject = $temp->email_subject;

        try{
            Mail::send('admin.email.mailbody',$data, function ($message) use ($objDemo) {
                $message->from($objDemo->from,$objDemo->title);
                $message->to($objDemo->to);
                $message->subject($objDemo->subject);
            });
            return true;
        }
        catch (\Exception $e){
            // die($e->getMessage());
        }
        return false;
    }

    public function sendAutoMemberPackageMail(array $mailData,$id)
    {
        $setup = Generalsetting::find(1);
        $temp = EmailTemplate::where('email_type','=',$mailData['type'])->first();
        $body = preg_replace("/{customer_name}/", $mailData['cname'] ,$temp->email_body);
        $body = preg_replace("/{order_amount}/", $mailData['oamount'] ,$body);
        $body = preg_replace("/{admin_name}/", $mailData['aname'] ,$body);
        $body = preg_replace("/{admin_email}/", $mailData['aemail'] ,$body);
        $body = preg_replace("/{order_number}/", $mailData['onumber'] ,$body);
        $body = preg_replace("/{website_title}/", $setup->title ,$body);
        $order = Order::findOrFail($id);
        $cart = unserialize(bzdecompress(utf8_decode($order->cart)));

        $data = [
            'order' => $order,
            'cart' => $cart
        ];

        $objDemo = new \stdClass();
        $objDemo->to = $mailData['to'];
        $objDemo->from = $setup->from_email;
        $objDemo->title = $setup->from_name;
        $objDemo->subject = $temp->email_subject;

        try{
            Mail::send('admin.email.order',$data, function ($message) use ($objDemo,$id) {
                $message->from($objDemo->from,$objDemo->title);
                $message->to($objDemo->to);
                $message->subject($objDemo->subject);
            });
            return true;
        }
        catch (\Exception $e){
             return $e->getMessage();
        }
    }

    public function sendCustomMail(array $mailData)
    {
        $setup = Generalsetting::find(1);

        $data = [
            'email_body' => $mailData['body']
        ];

        $objDemo = new \stdClass();
        $objDemo->to = $mailData['to'];
        $objDemo->from = $setup->from_email;
        $objDemo->title = $setup->from_name;
        $objDemo->subject = $mailData['subject'];

        try{
            Mail::send('admin.email.mailbody',$data, function ($message) use ($objDemo) {
                $message->from($objDemo->from,$objDemo->title);
                $message->to($objDemo->to);
                $message->subject($objDemo->subject);
            });
        }
        catch (\Exception $e){
            //die($e->getMessage());
            // return $e->getMessage();
        }
        return true;
    }

}
