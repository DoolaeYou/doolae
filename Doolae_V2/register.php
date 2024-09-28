<?php
require 'db_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nickname = $_POST['nickname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    // ตรวจสอบว่ามี username นี้ในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'ข้อผิดพลาด',
                    text: 'username นี้มีการลงทะเบียนแล้ว',
                    icon: 'error',
                    confirmButtonText: 'ปิด'
                });
            });
        </script>";
    } else {
        // บันทึกข้อมูลผู้ใช้ใหม่ลงในฐานข้อมูล
        $stmt = $conn->prepare("INSERT INTO users (nickname, username, password, weight, height, age, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiis", $nickname, $username, $password, $weight, $height, $age, $gender);

        if ($stmt->execute()) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'สำเร็จ',
                        text: 'การลงทะเบียนสำเร็จ',
                        icon: 'success',
                        confirmButtonText: 'ปิด'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login_form.php';
                        }
                    });
                });
            </script>";
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'ข้อผิดพลาด',
                        text: 'ไม่สามารถลงทะเบียนได้ กรุณาลองใหม่อีกครั้ง',
                        icon: 'error',
                        confirmButtonText: 'ปิด'
                    });
                });
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
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
            background-color: #222;
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input[type="text"], input[type="username"], input[type="password"], input[type="number"], select {
            width: 95%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #e50914;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #f40612;
        }
        a.button {
            display: inline-block;
            font-size: 16px;
            width: 95%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff; /* สีเดิมของปุ่ม */
            color: #fff;
            text-decoration: none;
            text-align: center;
            margin-top: 20px;
        }
        a.button:hover {
            background-color: #0056b3; /* สีเดิมของปุ่ม */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ลงทะเบียน</h1>
        <form action="register.php" method="post">
            <label for="nickname">ชื่อเล่น</label>
            <input type="text" id="nickname" name="nickname" required>
            
            <label for="username">Username</label>
            <input type="username" id="username" name="username" required>
            
            <label for="password">รหัสผ่าน</label>
            <input type="password" id="password" name="password" required>
            
            <label for="weight">น้ำหนัก (กก.)</label>
            <input type="number" id="weight" name="weight" required>
            
            <label for="height">ส่วนสูง (ซม.)</label>
            <input type="number" id="height" name="height" required>
            
            <label for="age">อายุ</label>
            <input type="number" id="age" name="age" required>
            
            <label for="gender">เพศ</label>
            <select id="gender" name="gender" required>
                <option value="male">ชาย</option>
                <option value="female">หญิง</option>
            </select>
            
            <input type="submit" value="ลงทะเบียน">
            <a href="login_form.php" class="button">กลับไปที่หน้าหลัก</a>
        </form>
    </div>
</body>
</html>
