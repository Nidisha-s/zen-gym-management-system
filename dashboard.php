<?php 
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php'; 

if (!isset($_SESSION["user_id"])) {
    header("Location: ulogin.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// ✅ simplified query (no cross DB)
$query = "SELECT * FROM user_packages WHERE userID = ? ORDER BY start_date DESC LIMIT 1";
$stmt = $conn_user->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
            background: url("images/background.jpg") no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .sidebar {
            width: 250px;
            background: rgba(0, 0, 0, 0.9);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .sidebar a {
            display: block;
            text-decoration: none;
            color: white;
            padding: 15px;
            margin: 8px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.2);
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .dashboard-box {
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            width: 50%;
        }
        h2 {
            margin-bottom: 15px;
            font-size: 24px;
        }
        p {
            margin-bottom: 10px;
            font-size: 18px;
        }
        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .active {
            background: green;
        }
        .expired {
            background: red;
        }
        button {
            padding: 10px 15px;
            border: none;
            background: #ff6600;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        button:hover {
            background: #ff4500;
        }
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo img {
            width: 200px; 
            height: auto;
            border-radius: 10px; 
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
    <img src="images/logo.png" alt="Zen Gym Logo">
    </div>
    <h2>🏠 Dashboard</h2>
    <a href="workout_plan.php">💪 View Workout Plan</a>
    <a href="bmi.php">⚖️ Calculate BMI & Diet</a>
    <a href="feedback.php">✍️ Feedback</a>
    <a href="ulogout.php">🚪 Logout</a>
</div>

<div class="container">
    <div class="dashboard-box">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION["user_name"] ?? "User"); ?>!</h2>

        <?php if ($package) { ?>
            <h3>Your Membership Plan</h3>
            <p><strong>Package:</strong> <?php echo htmlspecialchars($package["package_name"]); ?></p>
            <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($package["price"]); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($package["duration"]); ?></p>
            <p><strong>Status:</strong> 
                <span class="status <?php echo strtolower($package["status"]); ?>">
                    <?php echo htmlspecialchars($package["status"]); ?>
                </span>
            </p>
            <p><strong>Start Date:</strong> 
    <?php echo isset($package["start_date"]) ? htmlspecialchars($package["start_date"]) : "Not Assigned"; ?>
</p>
<p><strong>End Date:</strong> 
    <?php echo isset($package["end_date"]) ? htmlspecialchars($package["end_date"]) : "Not Assigned"; ?>
</p>


            <?php if ($package["status"] === "Active") { ?>
                <p>Your membership is active! Start your workouts and stay fit.</p>
            <?php } else { ?>
                <p class="expired">Your membership has expired or pending. Please renew your plan.</p>
                <a href="package.php"><button>Renew Membership</button></a>
            <?php } ?>

        <?php } else { ?>
            <p>You have not selected any package yet.</p>
            <a href="package.php"><button>Select a Package</button></a>
        <?php } ?>
    </div>
</div>

</body>
</html>
