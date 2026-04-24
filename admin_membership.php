<?php 
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';

// ✅ Admin protection
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Add membership
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_membership'])) {

    $category = trim($_POST['category']);  
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $duration = trim($_POST['duration']);
    $description = trim($_POST['description']);

    if (empty($category)) {
        die("Category cannot be empty!");
    }

    $sql = "INSERT INTO membership_packages (category, name, price, duration, description) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn_admin->prepare($sql);
    $stmt->bind_param("ssdss", $category, $name, $price, $duration, $description);
    $stmt->execute();

    header("Location: admin_membership.php?success=added");
    exit();
}

// Delete membership
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_membership'])) {

    $id = intval($_POST['id']);

    $stmt = $conn_admin->prepare("DELETE FROM membership_packages WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin_membership.php?success=deleted");
    exit();
}

// Edit membership
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_membership'])) {

    $id = intval($_POST['id']);
    $category = trim($_POST['category']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $duration = trim($_POST['duration']);
    $description = trim($_POST['description']);

    $sql = "UPDATE membership_packages 
            SET category=?, name=?, price=?, duration=?, description=? 
            WHERE id=?";
    
    $stmt = $conn_admin->prepare($sql);
    $stmt->bind_param("ssdssi", $category, $name, $price, $duration, $description, $id);
    $stmt->execute();

    header("Location: admin_membership.php?success=edited");
    exit();
}

// Fetch memberships
$sql = "SELECT * FROM membership_packages 
ORDER BY FIELD(category, 'Bodybuilding','Fitness training','Personal training','Weight loss','Yoga','Zumba','Couple training'), id ASC";

$result = $conn_admin->query($sql);

$memberships = [];
while ($row = $result->fetch_assoc()) {
    $memberships[] = $row;
}

// Edit data
$editData = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);

    $stmt = $conn_admin->prepare("SELECT * FROM membership_packages WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Membership</title>

<style>
body {
    font-family: Arial;
    background: url('images/background.jpg') no-repeat center;
    background-size: cover;
    color: white;
}
table {
    width: 100%;
    background: rgba(0,0,0,0.7);
}
th, td {
    padding: 10px;
}
th { background: green; }

.delete-btn { background:red; color:white; }
.edit-btn { background:blue; color:white; }

.form-container {
    background: rgba(0,0,0,0.8);
    padding:20px;
}
</style>
</head>

<body>

<h2>Membership Packages</h2>

<table>
<tr>
<th>ID</th><th>Category</th><th>Name</th><th>Price</th><th>Duration</th><th>Description</th><th>Action</th>
</tr>

<?php 
$current_category = null;
foreach ($memberships as $membership): 
?>

<?php if ($membership['category'] !== $current_category): ?>
<tr>
<td colspan="7" style="background:#ccc;color:black;text-align:center;">
<?php echo htmlspecialchars($membership['category']); ?>
</td>
</tr>
<?php $current_category = $membership['category']; endif; ?>

<tr>
<td><?php echo htmlspecialchars($membership['id']); ?></td>
<td><?php echo htmlspecialchars($membership['category']); ?></td>
<td><?php echo htmlspecialchars($membership['name']); ?></td>
<td><?php echo htmlspecialchars($membership['price']); ?></td>
<td><?php echo htmlspecialchars($membership['duration']); ?></td>
<td><?php echo htmlspecialchars($membership['description']); ?></td>

<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="id" value="<?php echo $membership['id']; ?>">

<button name="delete_membership" class="delete-btn"
onclick="return confirm('Delete this package?')">
Delete
</button>
</form>

<a href="admin_membership.php?edit=<?php echo $membership['id']; ?>#form" class="edit-btn">Edit</a>
</td>
</tr>

<?php endforeach; ?>
</table>

<div class="form-container" id="form">

<h3><?php echo $editData ? "Edit Package" : "Add Package"; ?></h3>

<form method="POST">

<input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">

<input type="text" name="category" placeholder="Category"
value="<?php echo htmlspecialchars($editData['category'] ?? ''); ?>" required>

<input type="text" name="name" placeholder="Name"
value="<?php echo htmlspecialchars($editData['name'] ?? ''); ?>" required>

<input type="text" name="price" placeholder="Price"
value="<?php echo htmlspecialchars($editData['price'] ?? ''); ?>" required>

<input type="text" name="duration" placeholder="Duration"
value="<?php echo htmlspecialchars($editData['duration'] ?? ''); ?>" required>

<input type="text" name="description" placeholder="Description"
value="<?php echo htmlspecialchars($editData['description'] ?? ''); ?>" required>

<button type="submit" name="<?php echo $editData ? 'edit_membership' : 'add_membership'; ?>">
<?php echo $editData ? "Update" : "Add"; ?>
</button>

</form>
</div>

<br>
<a href="auth.php">Go Back</a>

</body>
</html>
