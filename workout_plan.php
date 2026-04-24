<?php 
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ulogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ FIXED (no cross DB)
$query = "SELECT status FROM user_packages WHERE userID = ? ORDER BY start_date DESC LIMIT 1";
$stmt = $conn_user->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();

if (!$package || $package["status"] !== "Active") {
    echo "<script>
        alert('Access Denied! Your membership is expired or pending.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

// ✅ FIXED ORDER BY
$query = "SELECT packageID FROM user_packages WHERE userID = ? ORDER BY pid DESC LIMIT 1";
$stmt = $conn_user->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();

if (!$package) {
    echo "No package found!";
    exit();
}

$package_id = $package['packageID'];

// ✅ FIXED (no cross DB)
$query = "SELECT exercises FROM workout_plans WHERE id = ?";
$stmt = $conn_admin->prepare($query);
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();
$workout = $result->fetch_assoc();

if (!$workout) {
    echo "No workout plan assigned!";
    exit();
}

$exercises = nl2br(htmlspecialchars($workout['exercises']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plan</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }
        .container {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            width: 50%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            color: green;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .workout-box {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            text-align: left;
            margin-top: 15px;
        }
        .error-message {
            text-align: center;
            color: #e74c3c;
            font-size: 18px;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .container {
                width: 80%;
            }
        }
        .back-btn {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background: green;
        color: white;
        text-decoration: none;
        font-size: 18px;
        border-radius: 5px;
        transition: 0.3s;
    }
    .back-btn:hover {
        background: blue;
    }
    </style>
</head>
<body>
<div class="container"> 
    <h2>Your Workout Plan</h2>
    <p><strong>Exercises:</strong></p>
    <p align="left"><?php echo $exercises; ?></p>

    <a href="dashboard.php" class="back-btn">⬅Go Back</a>
</div>

</body>
</html>
