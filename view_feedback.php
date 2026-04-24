<?php
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php'; 

// ✅ Admin protection
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ✅ Fixed query
$sql = "SELECT f.feedbackID, f.userID, u.name AS userName, f.rating, f.comments 
        FROM feedback f
        LEFT JOIN users u ON f.userID = u.userID
        ORDER BY f.feedbackID DESC";

$result = $conn_user->query($sql); 

if (!$result) {
    die("Error retrieving feedback.");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9); /* White with transparency */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #28a745;
            color: white;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn-back {
            margin-top: 20px;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            border-radius: 5px;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Feedback</h2>
        <table>
            <tr>
                <th>Feedback ID</th>
                <th>User ID</th>
                <th>Name</th>
                <th>Rating</th>
                <th>Comments</th>
            </tr>
            <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['feedbackID']) . "</td>
            <td>" . htmlspecialchars($row['userID'] ?? 'N/A') . "</td>
            <td>" . htmlspecialchars($row['userName'] ?? 'Anonymous') . "</td>
            <td>" . htmlspecialchars($row['rating']) . "</td>
            <td>" . htmlspecialchars($row['comments']) . "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5'>No feedback available</td></tr>";
}
?>
        </table>
        <a href="auth.php" class="btn-back">Back</a>
    </div>
</body>
</html>
