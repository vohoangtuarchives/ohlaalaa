<?php

namespace App\Http\Controllers\User;

use Auth;
use Exception;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;

use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout', 'userLogout']]);
    }

    public function showRegisterForm()
    {
      $this->code_image();
      return view('user.register');
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

    public function register(Request $request)
    {
        try{
    	$gs = Generalsetting::findOrFail(1);
    	// if($gs->is_capcha == 1)
    	// {
	    //     $value = session('captcha_string');
	    //     if ($request->codes != $value){
	    //         return response()->json(array('errors' => [ 0 => 'Mã captcha không đúng.' ]));
	    //     }
    	// }
        //--- Validation Section

        $rules = [
            'email'   => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        $user = new User;
        $input = $request->all();
        $input['password'] = bcrypt($request['password']);
        $token = md5(time().$request->name.$request->email);
        $input['verification_link'] = $token;
        $input['affilate_code'] = md5($request->name.$request->email);
        $input['CityID'] = $request->province;
        $input['DistrictID'] = $request->district;
        $input['ward_id'] = $request->ward;
        $input['api_token'] = Str::random(60);

        if(!empty($request->referral_code))
        {
            $input['referral_code'] = $request->referral_code;
            $referral_user = $user->referral_user($request->referral_code);
            if(isset($referral_user)){
                $input['referral_user_id'] = $referral_user->id;
                $input['referral_user_ids'] = $user->referral_user_ids($referral_user->referral_code, $referral_user->id);
            }
        }

        if(!empty($request->vendor))
        {
            //--- Validation Section
            $rules = [
                'shop_name' => 'unique:users',
                'shop_number'  => 'max:100'
                    ];
            $customs = [
                'shop_name.unique' => 'Tên shop này đã tồn tại! Vui lòng chọn tên khác.',
                'shop_number.max'  => 'Shop Number Must Be Less Then 100 Digit.'
            ];

            $validator = Validator::make($request->all(), $rules, $customs);
            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            $input['is_vendor'] = 1;
            $input['owner_name'] = $request->name;
            $input['shop_number'] = $request->phone;
            $input['shop_address'] = $request->address;
            $input['shop_message'] = $request->email;
        }

        $user->fill($input)->save();
        if($gs->is_verification_email == 1)
        {
            $to = $request->email;
            $subject = 'Xác minh địa chỉ email của bạn.';// 'Verify your email address.';
            $msg = "Xin chào bạn,<br> Để hoàn tất thủ tục đăng ký trên ohlaalaa.com Bạn hãy <a href=".url('user/register/verify/'.$token).">Click vào đây</a> để hoàn tất thủ tục đăng ký nhé!";
            //Sending Email To Customer

            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $msg,
                ];

                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);
            }
            else
            {
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($to,$subject,$msg,$headers);
            }
            return response()->json('Thủ tục đăng ký gần hoàn tất rồi! Bạn hãy đăng nhập vào email <b style="color:orangered;">'.$to.'</b> để xác nhận email đăng ký nữa nhé!');
        }
        else {
            $user->email_verified = 'Yes';
            $user->update();
            $notification = new Notification;
            $notification->user_id = $user->id;
            $notification->save();
            Auth::guard('web')->login($user);
            return response()->json(1);
        }

    }
    catch (\Exception $e){
        return response()->json($e->getMessage());
   }
    }

    public function token($token)
    {
        $gs = Generalsetting::findOrFail(1);
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        if($gs->is_verification_email == 1)
        {
            $user = User::where('verification_link','=',$token)->first();
            if(isset($user))
            {
                $user->verified_date = Carbon::now()->format('Y-m-d H:m:s');
                $user->email_verified = 'Yes';
                $user->update();
                $notification = new Notification;
                $notification->user_id = $user->id;
                $notification->save();
                Auth::guard('web')->login($user);
                return redirect()->route('user-dashboard')->with('success','Email Verified Successfully');
            }
        }
        else {
        return redirect()->back();
        }
    }
}
