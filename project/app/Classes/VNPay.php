<?php

namespace App\Classes;

use Illuminate\Support\Facades\Request;

class VNPay {
    /**
     * @description Make VNPAY-URL
     * @param       $url
     * @param       array $params
     * @return      VNPAY-URL
     */
    public static function createPayment($order) {
        $vnp_TxnRef = $order->order_number;
        $vnp_OrderInfo = 'Thanh toan don hang '.$order->order_number;
        $vnp_OrderType = 'other';
        $vnp_Amount = $order->pay_amount2 * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = $order->payment_bank;
        $vnp_IpAddr = Request::ip();
        $config = config('app.vnpay');
        $inputData = array(
            "vnp_Version" => "2.0.0",
            "vnp_TmnCode" => $config['TmnCode'],
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $config['Returnurl'],
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        $vnp_Url = $config['Url'];

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . $key . "=" . $value;
            } else {
                $hashdata .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnp_HashSecret = $config['HashSecret'];
        if (isset($vnp_HashSecret)) {
           // $vnpSecureHash = md5($vnp_HashSecret . $hashdata);
            $vnpSecureHash = hash('sha256', $vnp_HashSecret . $hashdata);
            $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
        }
        //$vnp_Url = substr($vnp_Url, 0, -1);
        $returnData = array('code' => '00'
            , 'message' => 'success'
            , 'data' => $vnp_Url);
        return $returnData;
    }

    public static function createPaymentMembership($member_package) {
        $vnp_TxnRef = $member_package->payment_number;
        $vnp_OrderInfo = 'Thanh toan phi nang cap goi tai khoan '.$member_package->payment_number;
        $vnp_OrderType = 'other';
        $vnp_Amount = $member_package->package_price * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = $member_package->payment_bank;
        $vnp_IpAddr = Request::ip();
        $config = config('app.vnpay');
        $inputData = array(
            "vnp_Version" => "2.0.0",
            "vnp_TmnCode" => $config['TmnCode'],
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $config['Returnurl_membership'],
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        $vnp_Url = $config['Url'];

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . $key . "=" . $value;
            } else {
                $hashdata .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnp_HashSecret = $config['HashSecret'];
        if (isset($vnp_HashSecret)) {
           // $vnpSecureHash = md5($vnp_HashSecret . $hashdata);
            $vnpSecureHash = hash('sha256', $vnp_HashSecret . $hashdata);
            $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
        }
        //$vnp_Url = substr($vnp_Url, 0, -1);
        $returnData = array('code' => '00'
            , 'message' => 'success'
            , 'data' => $vnp_Url);
        return $returnData;
    }
}
