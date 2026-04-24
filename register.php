<?php  
ob_start(); 

// 🔒 Disable error display for security
ini_set('display_errors', 0);
error_reporting(0);

session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {

    if (isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['password'])) {

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);

        // ✅ Validations
        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            $error = "❌ Name cannot contain numbers or special characters!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\.com$/", $email)) {
            $error = "❌ Only Gmail addresses (@gmail.com) are allowed!";
        } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
            $error = "❌ Invalid phone number! (Must be 10 digits)";
        } elseif (strlen($password) < 6 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
            $error = "❌ Password must be at least 6 characters and include an uppercase letter and a number!";
        } else {

            // 🔐 Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // ✅ Check if email exists
            $checkEmail = $conn_user->prepare("SELECT * FROM users WHERE email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $result = $checkEmail->get_result();

            if ($result->num_rows > 0) {
                $error = "❌ Email already registered!";
            } else {

                // ✅ Insert user
                $stmt = $conn_user->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                
                if ($stmt) {
                    $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

                    if ($stmt->execute()) {
                        $_SESSION["user_id"] = $conn_user->insert_id;
                        $_SESSION["user_name"] = $name;

                        ob_end_clean(); 
                        header("Location: package.php");
                        exit();
                    } else {
                        $error = "❌ Database Error!";
                    }

                    $stmt->close();
                } else {
                    $error = "❌ Failed to prepare statement!";
                }
            }

            $checkEmail->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Registration</title>

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
        .password-container {
            position: relative;
            width: 80%;
            margin: auto;
        }
        .eye-icon {
            position: absolute;
            top: 12px;
            right: 10px;
            cursor: pointer;
            color: white;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var eyeIcon = document.getElementById("eye-icon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Sign Up</h1>

        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone" placeholder="Phone" required>

            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="fas fa-eye eye-icon" id="eye-icon" onclick="togglePassword()"></i>
            </div>

            <button type="submit" name="register">Register</button>
        </form>

        <p>Already have an account? <a href="ulogin.php">Login</a></p>
    </div>
</body>
</html>