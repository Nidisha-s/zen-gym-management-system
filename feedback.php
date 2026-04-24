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

// ✅ FIXED query
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $rating = intval($_POST['rating']);
    $comments = htmlspecialchars(trim($_POST['comments']));

    $sql = "INSERT INTO feedback (userID, rating, comments) VALUES (?, ?, ?)";
    $stmt = $conn_user->prepare($sql);
    $stmt->bind_param("iis", $user_id, $rating, $comments);

    if ($stmt->execute()) {
        echo "<script>
            alert('Feedback submitted successfully!');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Error submitting feedback!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url(images/background.jpg); 
            background-size: cover;
            background-position: center;
            text-align: center;
            padding: 50px;
        }
        .container {
            background: transparent;
            padding: 20px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.8);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            margin: auto;
        }
        h2 {
            color: white;
        }
        label {
            color: white;
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        select, textarea {
            width: 95%;
            padding: 10px;
            color: white;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background: transparent;
            backdrop-filter: blur(20px);
        }
        option {
            color: black;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Submit Your Feedback</h2>
        <form action="feedback.php" method="POST">
            <label for="rating">Rating (1-5):</label>
            <select name="rating" required>
                <option value="">Select</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>
            
            <label for="comments">Comments:</label>
            <textarea name="comments" rows="4" required></textarea>

            <button type="submit">Submit Feedback</button>
        </form>
    </div>
</body>
</html>
