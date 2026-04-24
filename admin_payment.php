<?php 
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once 'db.php';

// ✅ Admin protection
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ✅ Fixed query
$query = "SELECT p.paymentID, p.userID, u.name, u.email, 
          p.amount_paid, p.payment_date, p.transaction_id 
          FROM payments AS p 
          JOIN users AS u ON p.userID = u.userID
          ORDER BY p.payment_date DESC";

$result = $conn_user->query($query);

if (!$result) {
    die("Error fetching payments.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            text-align: center;
            margin: 20px;
        }
        .container {
            width: 80%;
            margin: auto;
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        h2 {
            color: #ffcc00;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: black;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .btn {
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
            background-color: #007bff;
            color: white;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Payment Details</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Member Name</th>
            <th>Email</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Transaction ID</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['userID']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>₹<?php echo number_format($row['amount_paid'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align: center;">No payment records found.</td></tr>
        <?php endif; ?>
    </table>

    <a href="auth.php" class="btn">Go Back</a>
</div>

</body>
</html>
