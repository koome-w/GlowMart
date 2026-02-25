<?php
// M-Pesa / Daraja configuration placeholders.
// Replace values with your Daraja credentials and environment.

$mpesa_env = 'sandbox'; // 'sandbox' or 'production'
$mpesa_base = ($mpesa_env === 'production') ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';

$mpesa_consumer_key = 'YOUR_CONSUMER_KEY';
$mpesa_consumer_secret = 'YOUR_CONSUMER_SECRET';
$mpesa_shortcode = '174379'; // Example sandbox shortcode
$mpesa_passkey = 'YOUR_PASSKEY';

// Publicly reachable callback URL where Safaricom will POST results (must be HTTPS in production)
$mpesa_callback_url = 'https://yourdomain.com/GlowMart/php/mpesa_callback.php';

// Timeout and other defaults
$mpesa_timeout = 30; // seconds

?>
