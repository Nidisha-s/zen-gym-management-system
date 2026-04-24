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

// ADD Diet Plan
if (isset($_POST['add_diet'])) {  
    $bmi_category = htmlspecialchars($_POST['bmi_category'] ?? '');
    $description = htmlspecialchars(trim($_POST['description'] ?? "")); 

    if (!empty($bmi_category) && !empty($description)) {  
        $query = "INSERT INTO diet_plans (bmi_category, description) VALUES (?, ?)";  
        $stmt = $conn_admin->prepare($query);  
        $stmt->bind_param("ss", $bmi_category, $description);  

        if ($stmt->execute()) {  
            echo "<script>alert('Diet plan added successfully!'); window.location='admin_diet.php';</script>";  
            exit();
        } else {  
            echo "<script>alert('Error!');</script>";  
        }  
        $stmt->close();  
    } else {  
        echo "<script>alert('BMI Category and Description cannot be empty!');</script>";  
    }
}

// EDIT Diet Plan
if (isset($_POST['edit_diet_submit'])) {
    $id = intval($_POST['edit_diet'] ?? 0);
    $description = htmlspecialchars(trim($_POST['description'] ?? ""));

    if (!empty($id) && !empty($description)) {
        $query = "UPDATE diet_plans SET description=? WHERE id=?";
        $stmt = $conn_admin->prepare($query);
        $stmt->bind_param("si", $description, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Diet plan updated successfully!'); window.location='admin_diet.php';</script>";
            exit();
        } else {
            echo "<script>alert('Update failed!');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Description cannot be empty!');</script>";
    }
}

// DELETE Diet Plan
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn_admin->prepare("DELETE FROM diet_plans WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_diet.php");
    exit();
}

// FETCH Existing Diet Plans
$result = $conn_admin->query("SELECT * FROM diet_plans");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Diet Plans</title>
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
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            display: inline-block;
            margin-top: 15px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Diet Plans</h2>
    
    <form action="" method="POST" id="dietForm">
        <input type="hidden" name="edit_diet" id="edit_id">
        
        <div class="form-group">
            <label>BMI Category:</label>
            <select name="bmi_category" id="bmi_category" required>
                <option value="Underweight">Underweight</option>
                <option value="Normal">Normal</option>
                <option value="Overweight">Overweight</option>
                <option value="Obese">Obese</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Diet Plan:</label>
            <textarea name="description" id="description" rows="3" required></textarea>
        </div>
        
        <button type="submit" name="add_diet" id="submitButton" class="submit-btn">Add Diet Plan</button>
    </form>

    <table>
        <tr>
            <th>BMI Category</th>
            <th>Diet Plan</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['bmi_category']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
            <td>
                <button class="btn btn-edit" 
                        onclick="editDiet('<?php echo $row['id']; ?>', 
                                          `<?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?>`, 
                                          '<?php echo $row['bmi_category']; ?>')">
                    Edit
                </button>
                <a href="admin_diet.php?delete=<?php echo $row['id']; ?>" 
                   class="btn btn-delete" 
                   onclick="return confirm('Delete this diet plan?')">
                    Delete
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <a href="auth.php" class="btn btn-back">← Back to Dashboard</a>
</div>

<script>
function editDiet(id, description, category) {
    document.getElementById("bmi_category").value = category;
    document.getElementById("bmi_category").disabled = true;

    document.getElementById("description").value = description.replace(/<br\s*\/?>/g, "\n");

    document.getElementById("edit_id").value = id;

    let btn = document.getElementById("submitButton");
    btn.innerText = "Update Diet Plan";
    btn.name = "edit_diet_submit";
}
</script>

</body>
</html>
