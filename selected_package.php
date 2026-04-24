<?php
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php'; 

if (!isset($_SESSION["user_id"])) {
    header("Location: register.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["package_id"])) {

    $package_id = $_POST["package_id"];

    $query = "SELECT category, name, price, duration FROM membership_packages WHERE id = ?";
    $stmt = $conn_admin->prepare($query);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();

    if (!$package) {
        die("❌ Error: Package not found!");
    }

    $category = $package["category"];
    $name = $package["name"];
    $price = $package["price"];
    $duration = $package["duration"];

    $status = "Pending"; 
    $start_date = NULL;
    $end_date = NULL;

    $insert = $conn_user->prepare("INSERT INTO user_packages 
    (userID, packageID, category, name, price, duration, start_date, end_date, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // ✅ FIXED bind_param
    $insert->bind_param("iissdssss", $user_id, $package_id, $category, $name, $price, $duration, $start_date, $end_date, $status);
    
    if (!$insert->execute()) {
        die("❌ Error: " . $conn_user->error);
    }

    header("Location: payment.php?package_id=" . $package_id);
    exit();

} else {
    header("Location: package.php");
    exit();
}
?>