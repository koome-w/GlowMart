<?php
session_start();
require_once 'config.php';
require_once 'mpesa_config.php';
require_once 'mpesa_token.php';

header('Content-Type: application/json');

// Expect JSON body with order_id and phone
$input = json_decode(file_get_contents('php://input'), true);
if(!$input || !isset($input['order_id']) || !isset($input['phone'])){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

$order_id = intval($input['order_id']);
$phone = trim($input['phone']);

// Basic phone validation
if(!preg_match('/^2547\d{8}$/', $phone)){
    echo json_encode(['status'=>'error','message'=>'Phone must be in format 2547XXXXXXXX']);
    exit;
}

try{
    // Fetch order total from DB
    $stmt = $pdo->prepare('SELECT total_amount, user_id FROM orders WHERE order_id = :order_id');
    $stmt->execute([':order_id'=>$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$order){
        throw new Exception('Order not found');
    }

    $amount = (float)$order['total_amount'];
    $user_id = $order['user_id'];

    // Prepare STK Push
    $token = get_mpesa_access_token();
    $timestamp = date('YmdHis');
    $password = base64_encode($mpesa_shortcode . $mpesa_passkey . $timestamp);

    $payload = [
        'BusinessShortCode' => $mpesa_shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => (int)ceil($amount),
        'PartyA' => $phone,
        'PartyB' => $mpesa_shortcode,
        'PhoneNumber' => $phone,
        'CallBackURL' => $mpesa_callback_url,
        'AccountReference' => "GlowMartOrder:$order_id",
        'TransactionDesc' => "Payment for order #$order_id"
    ];

    $url = $mpesa_base . '/mpesa/stkpush/v1/processrequest';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, $mpesa_timeout ?? 30);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if($err){
        throw new Exception('STK request error: '.$err);
    }

    $resp = json_decode($response, true);

    // Record the payment attempt
    $stmt = $pdo->prepare('INSERT INTO payments (order_id, user_id, phone, amount, request_payload, response_payload, status, created_at) VALUES (:order_id,:user_id,:phone,:amount,:request_payload,:response_payload,:status,NOW())');
    $stmt->execute([
        ':order_id'=>$order_id,
        ':user_id'=>$user_id,
        ':phone'=>$phone,
        ':amount'=>$amount,
        ':request_payload'=>json_encode($payload),
        ':response_payload'=>$response,
        ':status'=>isset($resp['ResponseCode']) ? 'initiated' : 'error'
    ]);

    echo json_encode(['status'=>'success','data'=>$resp]);

}catch(Exception $e){
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
