<?php

namespace App\Http\Controllers\Front;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderVnpayTrackLog;
use Illuminate\Support\Facades\Session;

class VNPayController extends Controller
{
    public function getTrack($id)
    {
        $track = OrderVnpayTrackLog::where('order_id', '=', $id)
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
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $output->writeln("<info>Info message</info>");
        $this->code_image();
        if(Session::has('temporder_id')){
            $order_id = Session::get('temporder_id');
            $order = Order::find($order_id);
            $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
        }
        else{
            $order_number = $_GET['vnp_TxnRef'];
            $order = Order::where('order_number','=',$order_number)->first();
            $order_id = $order->id;
            $tempcart = unserialize(bzdecompress(utf8_decode($order['cart'])));
        }
        $track = $this->getTrack($order->id);
        $order = Order::findOrFail($order->id);
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $output->writeln("<info>vnp_SecureHash</info>");
        $output->writeln("<info>".$vnp_SecureHash."</info>");

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

        //$secureHash = md5($vnp_HashSecret . $hashData);
        $secureHash = hash('sha256',$config["HashSecret"] . $hashData);

        if ($secureHash == $vnp_SecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {
                $output->writeln("<info>GD Thanh cong</info>");
                return view('front.vnpay-return', compact('tempcart','order'));
                // if($order->payment_status != 'Completed'){
                //     $order->payment_status = 'Completed';
                //     $order->save();

                //     $gs = Generalsetting::findOrFail(1);
                //     if($gs->is_smtp == 1)
                //     {
                //         $data1 = [
                //             'to' => $order->customer_email,
                //             'type' => "new_order",
                //             'cname' => $order->customer_name,
                //             'oamount' => "",
                //             'aname' => "",
                //             'aemail' => "",
                //             'wtitle' => "",
                //             'onumber' => $order->order_number,
                //         ];

                //         $mailer = new GeniusMailer();
                //         $rs = $mailer->sendAutoOrderMail($data1,$order->id);
                //     }
                // }
            } else {
                $output->writeln("<info>GD Khong thanh cong</info>");

            }
        } else {
            $output->writeln("<info>Chu ky khong hop le</info>");
        }

        // if($order->payment_status != 'Completed'){
        //     $shippings = $order->orderconsumershippingcosts()->get();
        //     //dd($shippings);
        //     if($shippings->count() > 0){
        //         $status = config('app.viettel_post.order_status');
        //         foreach($shippings as $sp){
        //             $result_viettel_post = app('App\Http\Controllers\Front\ViettelPostController')->updateorderstatus($sp->shipping_partner_code, $status['cancel_order'], 'Cancel due to failed payment from vnpay');
        //             if($result_viettel_post){
        //                 $output->writeln("<info>update viettelpost status successfully</info>");
        //             }
        //         }
        //     }
        // }


        return view('front.vnpay-return', compact('tempcart','order'));
    }

    // Capcha Code Image
    private function  code_image()
    {
        $actual_path = str_replace('project','',base_path());
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image,0,0,200,50,$background_color);

        $pixel = imagecolorallocate($image, 0,0,255);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixel);
        }

        $font = $actual_path.'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $letter = $allowed_letters[rand(0, $length-1)];
        $word='';
        //$text_color = imagecolorallocate($image, 8, 186, 239);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $cap_length=6;// No. of character in image
        for ($i = 0; $i< $cap_length;$i++)
        {
            $letter = $allowed_letters[rand(0, $length-1)];
            imagettftext($image, 25, 1, 35+($i*25), 35, $text_color, $font, $letter);
            $word.=$letter;
        }
        $pixels = imagecolorallocate($image, 8, 186, 239);
        for($i=0;$i<500;$i++)
        {
            imagesetpixel($image,rand()%200,rand()%50,$pixels);
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path."assets/images/capcha_code.png");
    }
}
