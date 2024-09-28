<?php
session_start();

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "user_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nickname = $_POST['nickname'];
    $age = $_POST['age'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $gender = $_POST['gender'];

    // ตรวจสอบว่าชื่อผู้ใช้ไม่ซ้ำ
    $check_username = "SELECT username FROM users WHERE username = '$username'";
    $result = $conn->query($check_username);

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "ชื่อผู้ใช้นี้มีอยู่แล้ว กรุณาใช้ชื่อผู้ใช้อื่น";
        header("Location: register.php");
        exit();
    } else {
        $sql = "INSERT INTO users (username, password, nickname, age, height, weight, gender) VALUES ('$username', '$password', '$nickname', '$age', '$height', '$weight', '$gender')";
        
        if ($conn->query($sql) === TRUE) {
            echo "Registration successful";
            header("Location: login_form.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
            header("Location: register.php");
            exit();
        }
    }
}

$conn->close();
?>
