<?php

namespace App\Http\Controllers\User;

use App\Classes\CometChatHTD;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Models\User;
use Validator;

class LoginController extends Controller
{
    const IS_VENDOR  = '2'; //active
    const PREFERRED  = '1';
    const KOL  = '1';
    const SPECIAL_KOL  = '1';

    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout', 'userLogout']]);
    }

    public function showLoginForm()
    {
        if (Session::has('kolbonus')) {
            Session::forget('kolbonus');
        }
        $this->code_image();
        return view('user.login');
    }

    public function login(Request $request)
    {
        //--- Validation Section
        $rules = [
                  'email'   => 'required|email',
                  'password' => 'required'
                ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

      // Attempt to log the user in
      if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        // if successful, then redirect to their intended location
        $user = Auth::guard('web')->user();
        $this->checkUser($user->id);
        // Check If Email is verified or not
            if($user->email_verified == 'No')
            {
                Auth::guard('web')->logout();
                return response()->json(array('errors' => [ 0 => 'Email này chưa được Xác nhận! Vui lòng kiểm tra Email.' ]));
            }

            if($user->ban == 1)
            {
                Auth::guard('web')->logout();
                return response()->json(array('errors' => [ 0 => 'Tài khoản đã bị khóa.' ]));
            }

            $user->checkDownRank();

            //check and create comet_chat user
            $comet_detail = CometChatHTD::create_user($user);
            if($comet_detail['authToken'] != null){
                $user->comet_token = $comet_detail['authToken'];
                $user->save();
            }

            // Login Via Modal
            if(!empty($request->modal))
            {
                // Login as Vendor
                if(!empty($request->vendor))
                {
                    if($user->is_vendor == 2)
                    {
                        return response()->json(route('vendor-dashboard'));
                    }
                    else {
                        return response()->json(route('user-package'));
                    }
                }

                // Login as User
                return response()->json(1);
            }
            // Login as User
            return response()->json(route('user-dashboard'));
      }

      // if unsuccessful, then redirect back to the login with the form data
          return response()->json(array('errors' => [ 0 => 'Email hoặc mật khẩu không đúng!' ]));
    }

    public function logout()
    {
        if (Session::has('kolbonus')) {
            Session::forget('kolbonus');
        }
        Auth::guard('web')->logout();
        return redirect('/');
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

    public function checkUser($id)
    {
        $user =  User::select('id')
        // ->where('is_vendor', '=', static::IS_VENDOR)
        // ->where(function($query)  {
        //     $query->where('kol', '=', static::KOL)
        //     ->orWhere('special_kol', '=', static::SPECIAL_KOL)
        //     ->orWhere('preferred', '=', static::PREFERRED);
        // })
        ->where('kol', '=', static::KOL)
        ->orWhere('special_kol', '=', static::SPECIAL_KOL)
        ->orWhere('preferred', '=', static::PREFERRED)
        ->pluck('id')->toArray();

        if (in_array($id, $user)) {
            Session::put('kolbonus', $id);
        }
        return;
    }
}
