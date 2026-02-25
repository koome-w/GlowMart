<?php
require_once 'mpesa_config.php';
// Returns access token string or throws exception
function get_mpesa_access_token(){
    global $mpesa_consumer_key, $mpesa_consumer_secret, $mpesa_base, $mpesa_timeout;

    $url = $mpesa_base . '/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERPWD, $mpesa_consumer_key . ':' . $mpesa_consumer_secret);
    curl_setopt($curl, CURLOPT_TIMEOUT, $mpesa_timeout ?? 30);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if($err){
        throw new Exception('Token request error: ' . $err);
    }

    $data = json_decode($response, true);
    if(!isset($data['access_token'])){
        throw new Exception('Failed to obtain access token: ' . $response);
    }

    return $data['access_token'];
}
