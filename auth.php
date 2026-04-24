<?php  
ini_set('display_errors', 0);
error_reporting(0);

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zen Gym Admin Panel</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            background: url('images/background.jpg') no-repeat center center fixed; 
            background-size: cover; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .admin-panel { display: flex; width: 80%; max-width: 1200px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-radius: 10px; overflow: hidden; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2); }
        .sidebar { width: 250px; background: rgba(0, 0, 0, 0.8); color: white; padding: 20px; height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar h1 { text-align: center; font-size: 22px; }
        .sidebar ul { list-style-type: none; padding: 0; }
        .sidebar ul li { margin: 10px 0; }
        .sidebar ul li a { color: white; text-decoration: none; display: block; padding: 10px; border-radius: 5px; transition: 0.3s; }
        .sidebar ul li a:hover { background: #575757; }
        .logout-btn { display: inline-block; padding: 10px 15px; background: red; color: white; text-decoration: none; border-radius: 5px; text-align: center; }
        .logout-btn:hover { background: darkred; }
        .content { flex: 1; padding: 40px;text-align: center;background: rgba(255, 255, 255, 0.2);backdrop-filter: blur(20px);border-radius: 10px;box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);margin: auto;}
        .content h1, p { color: white; }
        .logo {text-align: center;margin-bottom: 10px;}
        .logo img {width: 200px; height: auto;border-radius: 10px; }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Zen Gym Logo">
            </div>
            <h1>Dashboard</h1>
            <ul>
                <li><a href="admin_members.php">View Members</a></li>
                <li><a href="admin_membership.php">Manage Membership Packages</a></li>
                <li><a href="view_packages.php">View Member Packages</a></li>
                <li><a href="admin_workout.php">Workout Plans</a></li>
                <li><a href="admin_diet.php">Diet Plans</a></li>
                <li><a href="admin_payment.php">Payments</a></li>
                <li><a href="view_feedback.php">View Feedback</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?>!</h1>
            <p>Manage gym operations from this dashboard.</p>
        </div>
    </div>
</body>
</html>
