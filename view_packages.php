<?php 
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ✅ fixed query
$sql = "SELECT up.packageID, up.userID, u.name AS member_name, u.email,
               mp.name AS package_name, mp.category, mp.price, mp.duration,
               up.start_date, up.end_date, up.status
        FROM user_packages AS up
        JOIN users AS u ON up.userID = u.userID
        JOIN admin.membership_packages AS mp ON up.packageID = mp.id
        ORDER BY up.packageID ASC";

$result = $conn_user->query($sql);

if (!$result) {
    die("Error fetching member packages: " . $conn_user->error);
}

$member_packages = [];
while ($row = $result->fetch_assoc()) {
    $member_packages[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Selected Packages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.7); 
            color: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: green;
            color: white;
        }
        .btn {
            display: block;
            width: 200px;
            text-align: center;
            background: #007BFF;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            margin: 20px auto;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Selected Membership Packages</h2>

        <table>
            <thead>
                <tr>
                    <th>Package ID</th>
                    <th>Member ID</th>
                    <th>Member Name</th>
                    <th>Email</th>
                    <th>Category</th>
                    <th>Package</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($member_packages)): ?>
                    <?php foreach ($member_packages as $package): ?>
                        <tr>
                            <td><?= isset($package['packageID']) ? htmlspecialchars($package['packageID']) : 'N/A'; ?></td>
                            <td><?= isset($package['userID']) ? htmlspecialchars($package['userID']) : 'N/A'; ?></td>
                            <td><?= isset($package['member_name']) ? htmlspecialchars($package['member_name']) : 'N/A'; ?></td>
                            <td><?= isset($package['email']) ? htmlspecialchars($package['email']) : 'N/A'; ?></td>
                            <td><?= isset($package['category']) ? htmlspecialchars($package['category']) : 'N/A'; ?></td>
                            <td><?= isset($package['package_name']) ? htmlspecialchars($package['package_name']) : 'N/A'; ?></td>
                            <td>₹<?= isset($package['price']) ? number_format($package['price'], 2) : '0.00'; ?></td>
                            <td><?= isset($package['duration']) ? htmlspecialchars($package['duration']) : 'N/A'; ?></td>
                            <td><?= isset($package['start_date']) ? htmlspecialchars($package['start_date']) : 'N/A'; ?></td>
                            <td><?= isset($package['end_date']) ? htmlspecialchars($package['end_date']) : 'N/A'; ?></td>
                            <td><?= isset($package['status']) ? htmlspecialchars($package['status']) : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="11" style="text-align: center;">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="auth.php" class="btn">Go Back</a>
    </div>
</body>
</html>
