<?php

namespace App\Models;
use Auth;

class Alepay
{
    protected $alepayUtils;
    protected $publicKey = "";
    protected $checksumKey = "";
    protected $apiKey = "";
    protected $callbackUrl = "";
    protected $signature = "";
    protected $config = [];
    // protected $config = array(
    //     "tokenKey" => "WfTWDF1rgtllijKQOssnr3y1yNCaoG",

    //     "encryptKey" => "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCV6VKbuo3KVZTtwqbuPynhQsQRFwsXluHhlVy5SQLbat8dPOvjoRhJP17lMZ+SYmIzvUGvyTBmqRyCYevdRtY7DmPnCyt/soqg80X5hAfYwnac2GCJQ98fXtSOtQs2xSySiPVoAAR403jyNr499dbC2iEG/a7slCLjq+XSwqKewwIDAQAB",

    //     "checksumKey" => "S9c7GQPQxXT0zvDfQhG4G3YeV1gpTw",

    //     "callbackUrl" => "http://localhost:8000"
    // );
    protected $baseURL = array(
        'dev' => 'localhost:8080',
        '0' => 'https://alepay-v3-sandbox.nganluong.vn/api/v3/checkout',
        '1' => 'https://alepay-v3.nganluong.vn/api/v3/checkout'
    );
    protected $URI = array(
        'requestPayment' => '/request-payment',
        'getTransactionInfo' => '/get-transaction-info',
        'getInstallmentInfo' => '/get-installment-info',
        'getListBanks' => '/get-list-banks'
    );
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
        $this->config = config('app.alepay');
        /*
         * Require curl and json extension
         */
        if (!function_exists('curl_init')) {
            throw new Exception('Alepay needs the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new Exception('Alepay needs the JSON PHP extension.');
        }
        
        // set KEY
        if (isset($this->config) && !empty($this->config["tokenKey"])) {
            $this->apiKey = $this->config["tokenKey"];
        }
        if (isset($this->config) && !empty($this->config["checksumKey"])) {
            $this->checksumKey = $this->config["checksumKey"];
        }
        

        $this->alepayUtils = new AlepayUtils();
    }
    public function createAlepay($request,$order){
        $orderInfo = [
            'amount' => $order['pay_amount4'],
            'buyerAddress' => $order['customer_address'],
            'buyerCity' => $order['customer_city'] ?? 'null',
            'buyerCountry' => $order['customer_country'] ?? 'Viet Nam',
            'buyerEmail' => $order['customer_email'],
            'buyerName' => $order['customer_name'],
            'buyerPhone' => $order['customer_phone'],
            'cancelUrl' => $request->cancelUrl,
            'currency' => $request->currency ?? 'vnd',
            'customMerchantId' => $request->customMerchantId,
            'orderCode' => $order['order_number'],
            'orderDescription' => $request->orderDescription,
            'returnUrl' => $request->returnUrl,
            'tokenKey' => $this->config["tokenKey"],
            'totalItem' => $order['totalQty'],
            'paymentHours' => 0.3,
            'checkoutType'=> 4,
            'bankCode'=> "NAB",
            'allowDomestic'=> true,
        ];
        $data = array();
        $data = $orderInfo;
        $result = $this->sendOrderToAlepay($data);
        return $result;
    }
    public function sendOrderToAlepay($data) {
        $data['signature'] = $this->makeSignature($data,$this->checksumKey);
        $dataJSON = json_encode($data);
        $ch = curl_init($this->baseURL[$this->config['run']].$this->URI['requestPayment']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJSON);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
        );
        $result = curl_exec($ch);
        return json_decode($result);
    }
    // get transactioninfo
    public function getTransactionInfo($transactionCode) {
        $data = array(
            "tokenKey" => $this->config['tokenKey'],
            "transactionCode" => $transactionCode
        );
        $data['signature'] = $this->makeSignature($data,$this->checksumKey);
        ksort($data);
        $dataJSON = json_encode($data);
        $ch = curl_init($this->baseURL[$this->config['run']].$this->URI['getTransactionInfo']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJSON);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
        );
        $result = curl_exec($ch);
        return json_decode($result);
    }
    function getInstallmentInfo($request){
        $data = array(
            "tokenKey" => $this->config['tokenKey'],
            "amount" => $request->amount,
            "currencyCode" => $request->currencyCode,
        );
        $data['signature'] = $this->makeSignature($data,$this->checksumKey);
        ksort($data);
        $dataJSON = json_encode($data);
        $ch = curl_init($this->baseURL[$this->config['run']].$this->URI['getInstallmentInfo']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJSON);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
        );
        $result = curl_exec($ch);
        return json_decode($result);
    }
    function getListBanks(){
        $data = array(
            "tokenKey" => $this->config['tokenKey'],
        );
        $data['signature'] = $this->makeSignature($data,$this->checksumKey);
        ksort($data);
        $dataJSON = json_encode($data);
        $ch = curl_init($this->baseURL[$this->config['run']].$this->URI['getListBanks']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJSON);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
        );
        $result = curl_exec($ch);
        return json_decode($result);
    }
    function makeSignature($data, $hash_key)
    {
        // dd($data);
        $hash_data = '';
        ksort($data);
        $is_first_key = true;
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            if (!$is_first_key) {
                $hash_data .= '&' . $key . '=' . $value;
            } else {
                $hash_data .= $key . '=' . $value;
                $is_first_key = false;
            }
        }

        $signature = hash_hmac('sha256', $hash_data, $hash_key);
        return $signature;
    }
    public function createPaymentMembership($member_package) {
        $userTarget = Auth::user();
        $inputData = [
            'amount' => $member_package->package_price,
            'buyerAddress' => $userTarget->address,
            'buyerCity' => $userTarget->city ?? 'null',
            'buyerCountry' => $userTarget->country ?? 'Viet Nam',
            'buyerEmail' => $userTarget->email,
            'buyerName' => $userTarget->name,
            'buyerPhone' => $userTarget->phone,
            'cancelUrl' => route('alepay.usermembership.cancel'),
            'currency' => $request->currency ?? 'vnd',
            'customMerchantId' => $request->customMerchantId ?? 'ohlaalaa',
            'orderCode' => $member_package->payment_number,
            'orderDescription' => 'Thanh toan phi nang cap goi tai khoan '.$member_package->payment_number,
            'returnUrl' => route('alepay.return'),
            'tokenKey' => $this->apiKey,
            'totalItem' => 1,
            'paymentHours' => 0.16,
            'checkoutType'=> 4,
            'bankCode'=> "NAB",
            'allowDomestic'=> true,
        ];
        $data = array();
        $data = $inputData;
        $result = $this->sendOrderToAlepay($data);
        return $result;
    }
}
