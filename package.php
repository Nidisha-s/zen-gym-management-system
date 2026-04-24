<?php
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: register.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$query = "SELECT * FROM membership_packages ORDER BY category, id";
$result = $conn_admin->query($query);

$packages_by_category = [];
while ($row = $result->fetch_assoc()) {
    $packages_by_category[$row['category']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Package</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: url("images/background.jpg") no-repeat center center fixed;
    background-size: cover;
    text-align: center;
    color: white;
}
.container {
    width: 80%;
    margin: auto;
    background: rgba(0, 0, 0, 0.8);
    padding: 20px;
    border-radius: 10px;
}
.category-header {
    font-size: 22px;
    font-weight: bold;
    margin-top: 20px;
    background: rgba(255, 255, 255, 0.2);
    padding: 10px;
    border-radius: 5px;
}
.package {
    border: 1px solid white;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
}
button {
    background: green;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
button:hover {
    background: darkgreen;
}
</style>
</head>

<body>

<div class="container">
    <h2>Select a Package</h2>

    <?php foreach ($packages_by_category as $category => $packages) { ?>
        <div class="category-header"><?php echo strtoupper($category); ?></div>

        <?php foreach ($packages as $package) { ?>
            <div class="package">
                <p><strong><?php echo htmlspecialchars($package["name"]); ?></strong></p>
                <p>Price: ₹<?php echo htmlspecialchars($package["price"]); ?></p>
                <p>Duration: <?php echo htmlspecialchars($package["duration"]); ?></p>
                <p><?php echo htmlspecialchars($package["description"]); ?></p>

                <form method="POST" action="selected_package.php">
                    <input type="hidden" name="package_id" value="<?php echo $package["id"]; ?>">
                    <button type="submit">Select Package</button>
                </form>
            </div>
        <?php } ?>
    <?php } ?>

</div>

</body>
</html>