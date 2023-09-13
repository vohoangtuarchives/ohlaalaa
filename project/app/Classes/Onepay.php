<?php

namespace App\Classes;


class Onepay
{
    protected $vpc_Merchant = "";
    protected $vpc_AccessCode = "";
    protected $vpc_HashKey = "";
    protected $baseURL = "";
    protected $md5HashData = "";
    // protected $URI = array(
    //     'requestPayment' => ''
    // )

    public function __construct() {
        // header('Access-Control-Allow-Origin: *');
        // header("Access-Control-Allow-Credentials: true");
        // header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        // header('Access-Control-Max-Age: 1000');
        // header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
        $this->config = config('app.onepay');
        
        // set KEY
        if (isset($this->config) && !empty($this->config["vpc_Merchant"])) {
            $this->vpc_Merchant = $this->config["vpc_Merchant"];
        }
        if (isset($this->config) && !empty($this->config["vpc_AccessCode"])) {
            $this->vpc_AccessCode = $this->config["vpc_AccessCode"];
        }
        if (isset($this->config) && !empty($this->config["vpc_HashKey"])) {
            $this->vpc_HashKey = $this->config["vpc_HashKey"];
        }
        if (isset($this->config) && !empty($this->config["vpc_BaseUrl"])) {
            $this->baseURL = $this->config["vpc_BaseUrl"];
        }
    }
    public function createOnepay($request,$order){
        $orderInfo = [
            'vpc_Version' => 2,//d
            'vpc_Command' => 'pay',//d
            'vpc_AccessCode' => $this->vpc_AccessCode,//d
            'vpc_Merchant' => $this->vpc_Merchant, //d
            'vpc_Locale' => 'vn',//d
            'vpc_ReturnURL' => route('onepay.checkout'),//d
            'vpc_MerchTxnRef' => date('YmdHis') . rand(), //d
            'vpc_OrderInfo' => $order['order_number'],//d
            'vpc_Amount' => $order['pay_amount4']*100,//d
            'vpc_TicketNo' => $_SERVER ['REMOTE_ADDR'],//d
            'AgainLink' => route('front.checkout'),//d
            'Title' => 'Ohlaalaa',//d
            // 'vpc_SHIP_Street01' => '39A Ngo Quyen',//d
            // 'vpc_SHIP_Provice' => 'Hoan Kiem',//d
            // 'vpc_SHIP_City' => 'Ha Noi',//d
            // 'vpc_SHIP_Country' => 'Viet Nam',//d
            // 'vpc_Customer_Phone' => '0123456789',//d
            // 'vpc_Customer_Email' => 'support@onepay.vn',//d
            // 'vpc_Customer_Id' => 'thanhvt',//d
            // 'AVS_Street01' => '194 Tran Quang Khai',//d
            // 'AVS_City' => 'Hanoi',//d
            // 'AVS_StateProv' => 'Hoan Kiem',//d
            // 'AVS_PostCode' => '10000',//d
            // 'AVS_Country' => "",//d
            // 'display' => ""//d
        ];
        $data = array();
        $data = $orderInfo;
        ksort($data);
        $result = $this->sendOrderToOnepay($data);

        return $result;
    }
    public function sendOrderToOnepay($data) {
        // $result = $this->vpc_HashKey;
        $appendAmp = 0;
        foreach($data as $key => $value) {
            if (strlen($value) > 0) {
                if ($appendAmp == 0) {
                    $this->baseURL .= urlencode($key) . '=' . urlencode($value);
                    $appendAmp = 1;
                } else {
                    $this->baseURL .= '&' . urlencode($key) . "=" . urlencode($value);
                }
                if ((strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
                    $this->md5HashData .= $key . "=" . $value . "&";
                }
            }
        }
        $this->md5HashData = rtrim($this->md5HashData, "&");
        if (strlen($this->vpc_HashKey) > 0) {
            $this->baseURL .= "&vpc_SecureHash=" . strtoupper(hash_hmac('SHA256', $this->md5HashData, pack('H*',$this->vpc_HashKey)));
        }
        return $this->baseURL;
    }
    public function createSecureHash($data){
        ksort($data);
        $md5HashData = "";
        foreach ($data as $key => $value) {
        //chỉ lấy các tham số bắt đầu bằng "vpc_" hoặc "user_" và khác trống và không phải chuỗi hash code trả về
            if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
                $md5HashData .= $key . "=" . $value . "&";
            }
        }
        //  Xóa dấu & thừa cuối chuỗi dữ liệu
        $md5HashData = rtrim($md5HashData, "&");
        if (strtoupper ( $data['vpc_SecureHash'] ) == strtoupper(hash_hmac('SHA256', $md5HashData, pack('H*',$this->vpc_HashKey)))) {
            return true;
        } else {
            return false;
        }
    }
    public function getSecureHash($data){
        ksort($data);
        $md5HashData = "";
        foreach ($data as $key => $value) {
        //chỉ lấy các tham số bắt đầu bằng "vpc_" hoặc "user_" và khác trống và không phải chuỗi hash code trả về
            if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
                $md5HashData .= $key . "=" . $value . "&";
            }
        }
        //  Xóa dấu & thừa cuối chuỗi dữ liệu
        $md5HashData = rtrim($md5HashData, "&");
        return strtoupper(hash_hmac('SHA256', $md5HashData, pack('H*',$this->vpc_HashKey)));
    }
    public function queryDR($vpc_MerchTxnRef){
        $data = array(
            'vpc_Command' => 'queryDR',
            'vpc_Version' => '1',
            'vpc_Merchant' => $this->vpc_Merchant,
            'vpc_AccessCode' =>$this->vpc_AccessCode,
            'vpc_User' => $this->config['vpc_User'],
            'vpc_Password' => $this->config['vpc_Password']
        );
        $data['vpc_MerchTxnRef'] = $vpc_MerchTxnRef;
        $data['vpc_SecureHash'] = $this->getSecureHash($data);
        $postData = "";

        $ampersand = "";
        foreach ($data as $key => $value) {
            // create the POST data input leaving out any fields that have no value
            if (strlen($value) > 0) {
                $postData .= $ampersand . urlencode($key) . '=' . urlencode($value);
                $ampersand = "&";
            }
        }
        ob_start();
        $ch = curl_init();
        $vpcURL = "https://mtf.onepay.vn/msp/api/v1/vpc/invoices/queries";
        // set the URL of the VPC
        curl_setopt($ch, CURLOPT_URL, $vpcURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_exec($ch);
        $response = ob_get_contents();
        ob_end_clean();
        curl_close($ch);
        return $response;
    }
}
