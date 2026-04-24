<?php
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';
include 'cashfree/config.php'; 

if (!isset($config) || !is_array($config)) {
    die("Error: Configuration file missing.");
}

$appId = $config['app_id'];
$secretKey = $config['secret_key'];
$mode = $config['mode'];

// ✅ Define BASE URL
$BASE_URL = "http://localhost/admin panel/";

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get latest package
$query = "SELECT * FROM user_packages WHERE userID = ? ORDER BY pid DESC LIMIT 1";
$stmt = $conn_user->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();

if (!$package) {
    echo "No package selected!";
    exit();
}

$packageID = $package['packageID'];
$package_name = htmlspecialchars($package['name']);
$price = number_format($package['price'], 2);
$duration = $package['duration'];

// Fetch user details
$query = "SELECT name, email, phone FROM users WHERE userID = ?";
$stmt = $conn_user->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User details not found!";
    exit();
}

$customerName = $user['name'];
$customerEmail = $user['email'];
$customerPhone = $user['phone'];

// Generate Order
$orderId = "ZEN" . time() . $user_id;
$orderAmount = $package['price'];
$orderCurrency = "INR";
$orderNote = "Zen Gym Membership - " . $package['name'];

$returnUrl = $BASE_URL . "payment-success.php?order_id=" . $orderId;
$notifyUrl = $BASE_URL . "payment-webhook.php";

// Insert payment BEFORE redirect
$insertPayment = "INSERT INTO payments 
(userID, packageID, order_id, amount_paid, payment_date, transaction_id) 
VALUES (?, ?, ?, ?, NOW(), '')";

$stmt = $conn_user->prepare($insertPayment);
$stmt->bind_param("iiss", $user_id, $packageID, $orderId, $orderAmount);
$stmt->execute();

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
foreach ($postData as $key => $value){
    $signatureData .= $key . $value;
}

$signature = base64_encode(hash_hmac('sha256', $signatureData, $secretKey, true));

// Cashfree URL
$cashfree_url = ($mode === "PROD") 
    ? "https://www.cashfree.com/checkout/post/submit" 
    : "https://test.cashfree.com/billpay/checkout/post/submit";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Payment</title>
    <style>
        body {
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: white;
            text-align: center;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
        }
        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .package-details {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        button {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: green;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: darkgreen;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Complete Your Payment</h2>
    <p><strong>Package:</strong> <?php echo $package_name; ?></p>
    <p><strong>Price:</strong> ₹<?php echo $price; ?></p>
    <p><strong>Duration:</strong> <?php echo $duration; ?></p>
    
    <form action="cashfree/payment_process.php" method="POST">
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
        <input type="hidden" name="appId" value="<?php echo $appId; ?>">
        <input type="hidden" name="packageID" value="<?php echo $packageID; ?>">
        
        <button type="submit">Pay Now</button>
    </form>
</div>

</body>
</html>
