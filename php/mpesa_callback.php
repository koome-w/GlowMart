<?php
// Safaricom will POST the STK result here. We record results and update the order/payment status.
require_once 'config.php';
header('Content-Type: application/json');

$body = file_get_contents('php://input');
$data = json_decode($body, true);

// Log raw callback for debugging (optional) - write to file (ensure folder permissions)
file_put_contents(__DIR__.'/mpesa_callbacks.log', date('c').' '. $body ."\n", FILE_APPEND);

// Basic structure: result contains Body->stkCallback
if(!isset($data['Body']['stkCallback'])){
    // Not a valid callback
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid callback']);
    exit;
}

$cb = $data['Body']['stkCallback'];
$checkoutRequestId = $cb['CheckoutRequestID'] ?? null;
$merchantRequestId = $cb['MerchantRequestID'] ?? null;
$resultCode = $cb['ResultCode'] ?? null;
$resultDesc = $cb['ResultDesc'] ?? null;

$metadata = $cb['CallbackMetadata']['Item'] ?? [];
$mpesaReceipt = null;
$amount = null;
$phone = null;

foreach($metadata as $m){
    if(isset($m['Name'])){
        if($m['Name'] === 'MpesaReceiptNumber') $mpesaReceipt = $m['Value'];
        if($m['Name'] === 'Amount') $amount = $m['Value'];
        if($m['Name'] === 'PhoneNumber') $phone = $m['Value'];
    }
}

// Update payments table matching request id if available
try{
    if($checkoutRequestId){
        $stmt = $pdo->prepare('UPDATE payments SET merchant_request_id = :merchant, checkout_request_id = :checkout, result_code = :result_code, result_desc = :result_desc, mpesa_receipt = :receipt, response_payload = :payload, status = :status, updated_at = NOW() WHERE checkout_request_id = :checkout');
        $status = ($resultCode === 0) ? 'paid' : 'failed';
        $stmt->execute([
            ':merchant'=>$merchantRequestId,
            ':checkout'=>$checkoutRequestId,
            ':result_code'=>$resultCode,
            ':result_desc'=>$resultDesc,
            ':receipt'=>$mpesaReceipt,
            ':payload'=>$body,
            ':status'=>$status
        ]);

        // If paid, try to mark corresponding order as paid
        if($resultCode === 0){
            // Find payment row to get order_id
            $stmt2 = $pdo->prepare('SELECT order_id FROM payments WHERE checkout_request_id = :checkout LIMIT 1');
            $stmt2->execute([':checkout'=>$checkoutRequestId]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            if($row){
                $order_id = $row['order_id'];
                $pdo->prepare('UPDATE orders SET status = :status WHERE order_id = :order_id')->execute([':status'=>'paid',':order_id'=>$order_id]);
            }
        }
    }
}catch(Exception $e){
    // log error
    file_put_contents(__DIR__.'/mpesa_callbacks.log', "Error: " . $e->getMessage() ."\n", FILE_APPEND);
}

echo json_encode(['status'=>'ok']);
