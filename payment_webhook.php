<?php
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

include("db.php");
include("cashfree/config.php");

// Get raw JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validate structure
if (!isset($data['order']['order_id'])) {
    http_response_code(400);
    exit("Invalid webhook");
}

$order_id = $data['order']['order_id'];
$order_status = $data['order']['order_status'];
$transaction_id = $data['payment']['cf_payment_id'] ?? '';

// Only handle successful payments
if ($order_status === "PAID") {

    // ✅ Find user using order_id
    $query = "SELECT userID FROM payments WHERE order_id = ?";
    $stmt = $conn_user->prepare($query);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        http_response_code(404);
        exit("Order not found");
    }

    $row = $result->fetch_assoc();
    $user_id = $row['userID'];

    // ✅ Update transaction_id
    $update = "UPDATE payments SET transaction_id = ? WHERE order_id = ?";
    $stmt = $conn_user->prepare($update);
    $stmt->bind_param("ss", $transaction_id, $order_id);
    $stmt->execute();

    // ✅ Activate package
    $query = "SELECT packageID, duration FROM user_packages 
              WHERE userID = ? AND status = 'Pending' 
              ORDER BY pid DESC LIMIT 1";

    $stmt = $conn_user->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pkg = $result->fetch_assoc();

        $packageID = $pkg['packageID'];
        $duration = intval($pkg['duration']);

        $start_date = date("Y-m-d");
        $end_date = date("Y-m-d", strtotime("+$duration months"));

        $updatePkg = "UPDATE user_packages 
                      SET status='Active', start_date=?, end_date=? 
                      WHERE userID=? AND packageID=?";

        $stmt = $conn_user->prepare($updatePkg);
        $stmt->bind_param("ssii", $start_date, $end_date, $user_id, $packageID);
        $stmt->execute();
    }
}

// Respond to Cashfree
http_response_code(200);
echo "OK";
?>