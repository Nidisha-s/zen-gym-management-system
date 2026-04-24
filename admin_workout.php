
<?php   
// 🔒 Disable error display
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once 'db.php';

// ✅ Admin session protection
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ➕ Add workout
if (isset($_POST['add_workout'])) {

    $package_id = intval($_POST['id']);
    $package_name = htmlspecialchars(trim($_POST['name']));
    $exercises = htmlspecialchars(trim($_POST['exercises']));

    if (!empty($exercises) && !empty($package_name)) {

        // ✅ Prevent duplicate
        $check = $conn_admin->prepare("SELECT * FROM workout_plans WHERE id=?");
        $check->bind_param("i", $package_id);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            echo "<script>alert('Workout already exists for this package!');</script>";
        } else {

            $query = "INSERT INTO workout_plans (id, name, exercises) VALUES (?, ?, ?)";
            $stmt = $conn_admin->prepare($query);
            $stmt->bind_param("iss", $package_id, $package_name, $exercises);
            $stmt->execute();

            echo "<script>alert('Workout plan added successfully!'); window.location='admin_workout.php';</script>";
        }

    } else {
        echo "<script>alert('Fields cannot be empty!');</script>";
    }
}

// ✏️ Edit workout
if (isset($_POST['edit_workout'])) {

    $wid = intval($_POST['wid']);
    $exercises = htmlspecialchars(trim($_POST['exercises']));

    if (!empty($exercises)) {

        $query = "UPDATE workout_plans SET exercises=? WHERE wid=?";
        $stmt = $conn_admin->prepare($query);
        $stmt->bind_param("si", $exercises, $wid);
        $stmt->execute();

        echo "<script>alert('Workout updated successfully!'); window.location='admin_workout.php';</script>";

    } else {
        echo "<script>alert('Workout cannot be empty!');</script>";
    }
}

// ❌ Delete workout (SAFE)
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {

    $wid = intval($_GET['delete']);

    $stmt = $conn_admin->prepare("DELETE FROM workout_plans WHERE wid=?");
    $stmt->bind_param("i", $wid);
    $stmt->execute();

    header("Location: admin_workout.php");
    exit();
}

// 📊 Fetch data
$packages = $conn_admin->query("SELECT * FROM membership_packages");

$result = $conn_admin->query("
    SELECT workout_plans.*, membership_packages.name 
    FROM workout_plans 
    JOIN membership_packages 
    ON workout_plans.id = membership_packages.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workout Plans</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .btn {
            padding: 8px 12px;
            margin: 5px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-delete {
            background-color: red;
            color: white;
        }
        .btn-edit {
            background-color: blue;
            color: white;
        }
        .form-group {
            margin: 10px 0;
        }
        select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
        }
        .submit-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background-color: #218838;
        }

        #editWorkoutForm {
            display: none;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            margin: auto;
        }

        #editWorkoutPlan {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            overflow: auto; 
            font-size: 16px;
            line-height: 1.5;
        }
        td .btn {
            margin-right: 10px; 
        }
        .btn-back {
            background-color: green;
            color: white;
            padding: 8px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
<h2>Manage Workout Plans</h2>

<form method="POST">
<select name="id" required onchange="updatePackageName()">
<option value="">Select Package</option>

<?php while ($package = $packages->fetch_assoc()) { ?>
<option value="<?= $package['id']; ?>" 
data-name="<?= htmlspecialchars($package['name']); ?>">
<?= htmlspecialchars($package['name']); ?>
</option>
<?php } ?>

</select>

<input type="hidden" name="name" id="packageName">

<textarea name="exercises" required></textarea>

<button name="add_workout">Add</button>
</form>

<table>
<tr>
<th>Package</th>
<th>Workout</th>
<th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()) { ?>
<tr>
<td><?= htmlspecialchars($row['name']); ?></td>
<td><?= nl2br(htmlspecialchars($row['exercises'])); ?></td>

<td>
<button onclick="editWorkout('<?= $row['wid']; ?>', `<?= htmlspecialchars($row['exercises'], ENT_QUOTES); ?>`)">Edit</button>

<a href="admin_workout.php?delete=<?= $row['wid']; ?>"
onclick="return confirm('Delete this workout?')">Delete</a>
</td>
</tr>
<?php } ?>
</table>

<!-- Edit Form -->
<div id="editWorkoutForm" style="display:none;">
<form method="POST">
<input type="hidden" name="wid" id="editWid">
<textarea name="exercises" id="editWorkoutPlan"></textarea>
<button name="edit_workout">Update</button>
</form>
</div>

<br>
<a href="auth.php">Go Back</a>
</div>

<script>
function updatePackageName() {
let select = document.querySelector("select[name='id']");
let selected = select.options[select.selectedIndex];
document.getElementById("packageName").value = selected.getAttribute("data-name");
}

function editWorkout(wid, text) {
document.getElementById('editWorkoutForm').style.display='block';
document.getElementById('editWid').value=wid;
document.getElementById('editWorkoutPlan').value=text;
}
</script>

</body>
</html>
