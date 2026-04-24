<?php 
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';

// ✅ Admin session check
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch members
$sql = "SELECT userID AS id, name, email, phone FROM users"; 
$result = $conn_user->query($sql);

if (!$result) {
    die("Error fetching members.");
}

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id']) && !empty($_POST['id'])) {

        $id = intval($_POST['id']); // ✅ safe

        $sql = "DELETE FROM users WHERE userID=?";
        $stmt = $conn_user->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }

        header("Location: admin_members.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member Management</title>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background: url('images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
}
.container {
    width: 80%;
    margin: 50px auto;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: rgba(0, 0, 0, 0.7);
}
th, td {
    padding: 10px;
    border: 1px solid #ddd;
}
th {
    background: green;
}
.delete-btn {
    color: red;
    background: none;
    border: none;
    cursor: pointer;
}
</style>
</head>

<body>

<div class="container">
    <h2>Member Management</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>

        <?php if (!empty($members)): ?>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['id']); ?></td>
                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                    <td><?php echo htmlspecialchars($member['phone']); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($member['id']); ?>">
                            <button type="submit" class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this user?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No members found.</td></tr>
        <?php endif; ?>
    </table>

    <br>
    <button onclick="window.location.href='auth.php'">Go Back</button>
</div>

</body>
</html>
