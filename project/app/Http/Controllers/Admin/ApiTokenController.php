<?php

namespace App\Http\Controllers\Admin;

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

    public function gettoken(Request $request)
    {
        $rules = [
            'email'   => 'required|email',
            'password' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            $token = Str::random(60);
            Auth::guard('admin')->user()->forceFill([
                'api_token' => hash('sha256', $token),
            ])->save();
            return response()->json(array('data' => [ 'token' => $token ]));
        }

        return response()->json(array('errors' => [ 0 => 'Credentials Doesn\'t Match !' ]));
    }
}
