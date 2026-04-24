<?php 
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ulogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ FIXED: removed cross-database reference
$query = "SELECT status FROM user_packages 
          WHERE userID = ? 
          ORDER BY start_date DESC LIMIT 1";

$stmt = $conn_user->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();

if (!$package || $package["status"] !== "Active") {
    echo "<script>
        alert('Access Denied! Your membership is expired or pending. Please renew your plan.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

// Input sanitize function
function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$heightErr = $weightErr = "";
$bmi = $output = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["height"]) || !is_numeric($_POST["height"]) || $_POST["height"] <= 0) {
        $heightErr = "Please enter a valid height.";
    } else {
        $height = test_input($_POST["height"]);
    }
    
    if (empty($_POST["weight"]) || !is_numeric($_POST["weight"]) || $_POST["weight"] <= 0) {
        $weightErr = "Please enter a valid weight.";
    } else {
        $weight = test_input($_POST["weight"]);
    }

    if (empty($heightErr) && empty($weightErr)) {

        $mtheight = $height / 100;
        $bmi = $weight / ($mtheight * $mtheight);

        if ($bmi <= 18.5) {
            $output = "Underweight";
        } elseif ($bmi <= 24.9) {
            $output = "Normal";
        } elseif ($bmi <= 29.9) {
            $output = "Overweight";
        } else {
            $output = "Obese";
        }

        // Insert into DB
        $stmt = $conn_user->prepare(
            "INSERT INTO bmi (userID, height, weight, bmi_value, bmi_category) 
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("iddds", $user_id, $height, $weight, $bmi, $output);
        $stmt->execute();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BMI Calculator</title>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: url('images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    height: 100vh;
    text-align: center;
}

.container {
    color: white;
    width: 50%;
    padding: 1.5rem;
    margin: 50px auto;
    border-radius: 25px;
    background: rgba(0, 0, 0, 0.8);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

h1 { color: white; }

label { font-weight: bold; }

input {
    width: 50%;
    background: transparent;
    border: none;
    border-bottom: 1px solid white;
    padding-left: 1.5rem;
    font-size: 15px;
    color: white;
    outline: none;
}

button {
    width: 120px;
    height: 35px;
    font-weight: 500;
    background: rgba(255, 255, 255, 0.4);
    border: none;
    border-radius: 30px;
    color: white;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: rgba(255, 255, 255, 0.6);
}

.result, .error {
    margin-top: 20px;
    padding: 15px;
    border-radius: 5px;
    backdrop-filter: blur(20px);
}

.error { color: red; }

.link-btn, .back-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 30px;
    color: white;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}

.link-btn:hover, .back-btn:hover {
    background: rgba(255, 255, 255, 0.6);
}
</style>
</head>

<body>

<div class="container">
    <h1>BMI Calculator</h1>

    <form method="POST">
        <!-- ✅ FIX: required added -->
        <label>Height (cm)</label><br>
        <input type="text" name="height" placeholder="Enter your height" required><br>
        <span class="error"><?php echo $heightErr; ?></span><br>

        <label>Weight (kg)</label><br>
        <input type="text" name="weight" placeholder="Enter your weight" required><br>
        <span class="error"><?php echo $weightErr; ?></span><br>

        <button type="submit">Calculate BMI</button>
        <a href="dashboard.php" class="back-btn">Back</a>
    </form>

    <?php if (!empty($bmi)) { ?> 
        <div class="result">
            <h2>Your BMI: <?php echo number_format($bmi, 2); ?></h2>
            <h3>Category: <?php echo $output; ?></h3>
        </div>

        <a href="view_diet.php" class="link-btn">View Diet Plan</a>
    <?php } ?>

</div>

</body>
</html>