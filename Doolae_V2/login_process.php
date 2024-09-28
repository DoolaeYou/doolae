<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการส่งข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลผู้ใช้
    $stmt = $conn->prepare("SELECT id, password, nickname FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nickname'] = $row['nickname'];
            header("Location: welcome.php");
            exit();
        } else {
            $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $_SESSION['error'] = "ชื่อผู้ใช้ไม่ถูกต้อง";
    }
    // เปลี่ยนเส้นทางกลับไปที่ login.html
    header("Location: login_form.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #141414;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #333;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 300px;
        }
        .container h1 {
            margin-bottom: 20px;
        }
        .container input[type="text"],
        .container input[type="password"] {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }
        .container input[type="submit"],
        .container a.button {
            background-color: #e50914;
            color: #fff;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            margin-top: 10px;
            font-size: 16px;
            text-decoration: none;
        }
        .container input[type="submit"]:hover,
        .container a.button:hover {
            background-color: #464646;
        }
        .error {
            color: red;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>เข้าสู่ระบบ</h1>
        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo '<p class="error">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
            ?>
            <input type="submit" value="Login">
            <a href="register.php" class="button">สมัคร</a>
        </form>
    </div>
</body>
</html>

