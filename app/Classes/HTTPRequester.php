<?php

namespace App\Classes;

class HTTPRequester {
    /**
     * @description Make HTTP-GET call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPGet($url, array $params) {
        $query = http_build_query($params);
        $ch    = curl_init($url.'?'.$query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    /**
     * @description Make HTTP-POST call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPost($url, array $params) {
        $query = http_build_query($params);
        $ch    = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $response = curl_exec($ch);
        //dd($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * @description Make HTTP-POST with body call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPostBody($url, array $params) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => array(
                //'Authorization: '.$authToken,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($params)
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            //die(curl_error($ch));
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

    /**
     * @description Make HTTP-POST with body call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPostBodyAccept($url, array $params) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($params)
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            //die(curl_error($ch));
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

    /**
     * @description Make HTTP-GET with Accept
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPGetAccept($url) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json'
            )
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

    /**
     * @description Make HTTP-GET with header
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPGetWithHeader($url, array $header) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $header
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

     /**
     * @description Make HTTP-POST with body call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPostJsonBody($url, array $params) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($params)
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

    /**
     * @description Make HTTP-POST with body call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPostWithHeader($url, array $params, array $header) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => json_encode($params)
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

    /**
     * @description Make HTTP-POST with nobody call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPostWithHeaderNobody($url, array $header) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => "{\"force\":false}",
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

    /**
     * @description Make HTTP-POST with body call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPostBodyHeader($url, array $params, array $header) {
        $ch    = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => json_encode($params)
        ));
        $response = curl_exec($ch);
        if($response === FALSE){
            return null;
        }
        $responseData = json_decode($response, TRUE);
        curl_close($ch);
        return $responseData;
    }

    /**
     * @description Make HTTP-PUT call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPut($url, array $params) {
        $query = \http_build_query($params);
        $ch    = \curl_init();
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_HEADER, false);
        \curl_setopt($ch, \CURLOPT_URL, $url);
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'PUT');
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, $query);
        $response = \curl_exec($ch);
        \curl_close($ch);
        return $response;
    }
    /**
     * @category Make HTTP-DELETE call
     * @param    $url
     * @param    array $params
     * @return   HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPDelete($url, array $params) {
        $query = \http_build_query($params);
        $ch    = \curl_init();
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_HEADER, false);
        \curl_setopt($ch, \CURLOPT_URL, $url);
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'DELETE');
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, $query);
        $response = \curl_exec($ch);
        \curl_close($ch);
        return $response;
    }
}
