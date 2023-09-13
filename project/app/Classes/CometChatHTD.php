<?php
/**
 * Created by PhpStorm.
 * User: ShaOn
 * Date: 11/29/2018
 * Time: 12:49 AM
 */

namespace App\Classes;

class CometChatHTD
{
    public function __construct()
    {
    }

    public static function get_url(){
        $app_id = config('app.comet_chat.app_id');
        $app_region = config('app.comet_chat.app_region');
        return 'https://'.$app_id.'.api-'.$app_region.'.cometchat.io/v3.0/';
    }

    public static function get_user($uid)
    {
        $data = array('data' => null, 'error' => null, 'authToken' => null);
        $api_key = config('app.comet_chat.api_key');
        $url = CometChatHTD::get_url().'users/'.$uid;
        $header = array(
            "Accept: application/json",
            'Content-Type: application/json',
            'apiKey: '.$api_key
        );
        $response = HTTPRequester::HTTPGetWithHeader($url, $header);
        if(isset($response['data'])){
            $data['data'] = $response['data'];
        }
        if(isset($response['error'])){
            $data['error'] = $response['error'];
        }
        return $data;
    }

    public static function create_user($user)
    {
        $data = array('data' => null, 'error' => null, 'authToken' => null);
        $uid = $user->affilate_code;
        $u = CometChatHTD::get_user($uid);
        if(isset($u['data'])){
            $data['data'] = $u['data'];
            $data['authToken'] =  CometChatHTD::get_auth_token($uid);
        }else{
            if($u['error']['code'] == 'ERR_UID_NOT_FOUND'){
                $api_key = config('app.comet_chat.api_key');
                $url = CometChatHTD::get_url().'users';
                $header = array(
                    "Accept: application/json",
                    'Content-Type: application/json',
                    'apiKey: '.$api_key
                );
                $photo = $user->show_photo();
                $requestArr = array(
                    "uid" => $uid,
                    "name" => $user->name,
                    "avatar" => $photo
                );
                if($user->is_vendor == 2){
                    $shop_link = route('front.vendor', str_replace(' ', '-', $user->shop_name));
                    array_push($requestArr, $shop_link);
                }

                $response = HTTPRequester::HTTPPostWithHeader($url, $requestArr, $header);
                if(isset($response['data'])){
                    $data['data'] = $response['data'];
                    $data['authToken'] =  CometChatHTD::get_auth_token($uid);
                }
                if(isset($response['error'])){
                    $data['error'] = $response['error'];
                    if($response['error']['code'] == 'ERR_UID_ALREADY_EXISTS'){
                        $data['authToken'] =  CometChatHTD::get_auth_token($uid);
                    }
                }
            } // end uid not found
        }//end else
        return $data;
    }

    public static function get_auth_token($uid)
    {
        $token = CometChatHTD::create_auth_token($uid);
        return isset($token['data']) ? $token['data']['authToken'] : null;
    }

    public static function create_auth_token($uid)
    {
        $data = array('data' => null, 'error' => null);
        $api_key = config('app.comet_chat.api_key');
        $url = CometChatHTD::get_url().'users/'.$uid.'/auth_tokens';
        $header = array(
            "Accept: application/json",
            'Content-Type: application/json',
            'apiKey: '.$api_key
        );
        $response = HTTPRequester::HTTPPostWithHeaderNobody($url, $header);
        if(isset($response['data'])){
            $data['data'] = $response['data'];
        }
        if(isset($response['error'])){
            $data['error'] = $response['error'];
        }
        return $data;
    }
}
