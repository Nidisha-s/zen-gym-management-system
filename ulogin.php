<?php  
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php'; 

// ✅ FIX: redirect to dashboard (not same page)
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$success_message = isset($_GET['message']) ? $_GET['message'] : "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['email'], $_POST['password'])) {

        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "❌ Invalid email format!";
        } else {

            $stmt = $conn_user->prepare("SELECT userID, name, password FROM users WHERE email = ?");
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {

                        $_SESSION['user_id'] = $user['userID'];
                        $_SESSION['user_name'] = $user['name'];

                        header("Location: dashboard.php");
                        exit();

                    } else {
                        $error = "❌ Incorrect password!";
                    }

                } else {
                    $error = "❌ User not found! Please register.";
                }

                $stmt->close();

            } else {
                $error = "❌ Database error!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login</title>
    <style> 
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background: url("images/background.jpg") no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            backdrop-filter: blur(20px);
            color: white;
            width: 350px;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 20px 35px rgba(0, 0, 1, 0.9);
            text-align: center;
            background: rgba(0, 0, 0, 0.7);
        }
        .form-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.4rem;
        }
        input {
            width: 80%;
            background-color: transparent;
            border: none;
            border-bottom: 2px solid white;
            color: white;
            padding: 10px;
            font-size: 15px;
            margin: 10px 0;
            outline: none;
        }
        input::placeholder {
            color: white;
        }
        input:focus {
            border-bottom: 2px solid #f48fb1;
        }
        button {
            width: 120px;
            height: 40px;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.4);
            border: none;
            border-radius: 30px;
            color: white;
            margin-top: 10px;
            cursor: pointer;
        }
        button:hover {
            background: rgba(255, 255, 255, 0.6);
        }
        p {
            margin-top: 10px;
        }
        a {
            color: lightblue;
            text-decoration: none;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        .success {
            color: lightgreen;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="form-title">Member Login</h1>
        
        <?php if (!empty($success_message)) { echo "<p class='success'>$success_message</p>"; } ?>

        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email">
            <input type="password" name="password" placeholder="Password">
            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</body>
</html>
