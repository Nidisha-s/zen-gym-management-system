<?php
session_start();
include("db.php"); 
include("cashfree/config.php"); 

// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Get order ID
$order_id = htmlspecialchars($_GET['order_id'] ?? '');
if (empty($order_id)) {
    die("Invalid request.");
}

// Fetch payment status from Cashfree
$cashfree_url = ($config['mode'] === "PROD") 
    ? "https://api.cashfree.com/pg/orders/$order_id"
    : "https://sandbox.cashfree.com/pg/orders/$order_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $cashfree_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "x-api-version: 2022-09-01",
    "x-client-id: " . $config['app_id'],
    "x-client-secret: " . $config['secret_key']
]);

$response = curl_exec($ch);
curl_close($ch);

$payment_data = json_decode($response, true);

if (!isset($payment_data['order_status'])) {
    die("Failed to fetch payment status.");
}

$txStatus = $payment_data['order_status'];
$transaction_id = $payment_data['cf_order_id'] ?? '';

// Fetch user from payments table
$query = "SELECT userID, transaction_id FROM payments WHERE order_id = ?";
$stmt = $conn_user->prepare($query);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invalid order.");
}

$row = $result->fetch_assoc();
$user_id = $row['userID'];

// Set session
$_SESSION['user_id'] = $user_id;

// Update transaction ID if empty
if (empty($row['transaction_id']) && !empty($transaction_id)) {
    $updateQuery = "UPDATE payments SET transaction_id = ? WHERE order_id = ?";
    $stmt = $conn_user->prepare($updateQuery);
    $stmt->bind_param("ss", $transaction_id, $order_id);
    $stmt->execute();
}

// If payment success
if ($txStatus === "PAID") {

    $query = "SELECT packageID, duration FROM user_packages 
              WHERE userID = ? AND status = 'Pending' 
              ORDER BY pid DESC LIMIT 1";

    $stmt = $conn_user->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("No package found.");
    }

    $row = $result->fetch_assoc();
    $packageID = $row['packageID'];
    $duration = intval($row['duration']);

    $start_date = date("Y-m-d");
    $end_date = date("Y-m-d", strtotime("+$duration months"));

    $updatePackage = "UPDATE user_packages 
                      SET status='Active', start_date=?, end_date=? 
                      WHERE userID=? AND packageID=?";

    $stmt = $conn_user->prepare($updatePackage);
    $stmt->bind_param("ssii", $start_date, $end_date, $user_id, $packageID);
    $stmt->execute();

    // ✅ Redirect after success
    header("Location: ulogin.php?status=success");
    exit();

} else {
    header("Location: ulogin.php?status=failure");
    exit();
}
?>