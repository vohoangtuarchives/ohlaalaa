<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Validator;

class ApiTokenController extends Controller
{

    public function update(Request $request)
    {
        $token = Str::random(60);
        $request->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();
        return ['token' => $token];
    }

    public function adminupdateuser($user_id)
    {
        $user = User::find($user_id);
        $token = Str::random(60);
        $user->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();
        return ['token' => $token];
    }

    public function adminupdate(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $token = Str::random(60);
        $user->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();
        return ['token' => $token];
    }

    public function getnewtoken()
    {
        $token = Str::random(60);
        return ['token' => hash('sha256', $token)];
    }

    public function gettoken1(Request $request)
    {
        return response()->json($request->email);
    }

    public function gettoken(Request $request)
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

            // Check If Email is verified or not
            if(Auth::guard('web')->user()->email_verified == 'No')
            {
                Auth::guard('web')->logout();
                return response()->json(array('errors' => [ 0 => 'Your Email is not Verified!' ]));
            }

            if(Auth::guard('web')->user()->ban == 1)
            {
                Auth::guard('web')->logout();
                return response()->json(array('errors' => [ 0 => 'Your Account Has Been Banned.' ]));
            }
            // Login as User
            return response()->json(array('data' => [ 'token' => Auth::guard('web')->user()->api_token ]));
        }

        // if unsuccessful, then redirect back to the login with the form data
        return response()->json(array('errors' => [ 0 => 'Credentials Doesn\'t Match !' ]));
    }
}
