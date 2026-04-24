<?php
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include '../db.php';
include 'config.php'; 

if (!isset($config) || !is_array($config)) {
    die("Error: Configuration missing.");
}

$appId = $config['app_id'];
$secretKey = $config['secret_key'];
$mode = $config['mode'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

// ✅ Safe input handling
$orderId = $_POST['orderId'] ?? '';
$orderAmount = $_POST['orderAmount'] ?? '';
$orderCurrency = $_POST['orderCurrency'] ?? '';
$orderNote = $_POST['orderNote'] ?? '';
$customerName = $_POST['customerName'] ?? '';
$customerEmail = $_POST['customerEmail'] ?? '';
$customerPhone = $_POST['customerPhone'] ?? '';
$returnUrl = $_POST['returnUrl'] ?? '';
$notifyUrl = $_POST['notifyUrl'] ?? '';

// Generate signature
$postData = [
    "appId" => $appId,
    "orderId" => $orderId,
    "orderAmount" => $orderAmount,
    "orderCurrency" => $orderCurrency,
    "orderNote" => $orderNote,
    "customerName" => $customerName,
    "customerPhone" => $customerPhone,
    "customerEmail" => $customerEmail,
    "returnUrl" => $returnUrl,
    "notifyUrl" => $notifyUrl
];

ksort($postData);
$signatureData = "";
foreach ($postData as $key => $value) {
    $signatureData .= $key . $value;
}

$signature = base64_encode(hash_hmac('sha256', $signatureData, $secretKey, true));

$cashfree_url = ($mode === "PROD") 
    ? "https://www.cashfree.com/checkout/post/submit" 
    : "https://test.cashfree.com/billpay/checkout/post/submit";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Cashfree...</title>
</head>
<body onload="document.forms['cashfreeForm'].submit();">
    <h2>Redirecting to Cashfree...</h2>
    <form name="cashfreeForm" method="POST" action="<?php echo $cashfree_url; ?>">
        <input type="hidden" name="appId" value="<?php echo $appId; ?>">
        <input type="hidden" name="orderId" value="<?php echo $orderId; ?>">
        <input type="hidden" name="orderAmount" value="<?php echo $orderAmount; ?>">
        <input type="hidden" name="orderCurrency" value="<?php echo $orderCurrency; ?>">
        <input type="hidden" name="orderNote" value="<?php echo $orderNote; ?>">
        <input type="hidden" name="customerName" value="<?php echo $customerName; ?>">
        <input type="hidden" name="customerEmail" value="<?php echo $customerEmail; ?>">
        <input type="hidden" name="customerPhone" value="<?php echo $customerPhone; ?>">
        <input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>">
        <input type="hidden" name="notifyUrl" value="<?php echo $notifyUrl; ?>">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
    </form>
</body>
</html>
