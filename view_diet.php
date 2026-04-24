<?php   
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ulogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get latest BMI
$query = "SELECT bmi_category FROM bmi WHERE userID = ? ORDER BY bmiID DESC LIMIT 1";
$stmt = $conn_user->prepare($query);

if (!$stmt) {
    die("Database error: " . $conn_user->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($bmi_category);
$stmt->fetch();
$stmt->close();

if (empty($bmi_category)) {
    echo "<script>
        alert('Please calculate BMI first.');
        window.location.href='bmi.php';
    </script>";
    exit();
}

// Get diet plan
$query = "SELECT description FROM diet_plans WHERE bmi_category = ?";
$stmt = $conn_admin->prepare($query);

if (!$stmt) {
    die("Database error: " . $conn_admin->error); // ✅ FIXED
}

$stmt->bind_param("s", $bmi_category);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Diet Plan</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: url('images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    text-align: center;
    color: white;
}

.container {
    width: 50%;
    padding: 1.5rem;
    margin: 50px auto;
    border-radius: 25px;
    background: rgba(0, 0, 0, 0.8);
}

.diet-plan {
    margin-top: 20px;
    padding: 15px;
    border-radius: 5px;
    background: rgba(255, 255, 255, 0.2);
}

.link-btn, .back-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 30px;
    color: white;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="container">
    <h1>Your Diet Plan</h1>
    <h2>BMI Category: <?php echo htmlspecialchars($bmi_category); ?></h2>

    <?php if ($result->num_rows > 0) { ?>
        <div class="diet-plan">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <p align="left"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p>No diet plan found.</p>
    <?php } ?>

    <a href="bmi.php" class="link-btn">Recalculate BMI</a>
    <a href="dashboard.php" class="back-btn">Back</a>
</div>

</body>
</html>

<?php
$stmt->close();
$conn_user->close();
?>