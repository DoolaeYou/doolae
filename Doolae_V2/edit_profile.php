<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// รับข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT nickname, weight, height, age FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// อัปเดตข้อมูลผู้ใช้
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nickname = $_POST['nickname'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $age = $_POST['age'];

    if (!empty($nickname) && !empty($weight) && !empty($height) && !empty($age)) {
        $stmt = $conn->prepare("UPDATE users SET nickname = ?, weight = ?, height = ?, age = ? WHERE id = ?");
        $stmt->bind_param("sddii", $nickname, $weight, $height, $age, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "ข้อมูลได้รับการอัปเดตเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        }
        header("Location: edit_profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลส่วนตัว</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #141414;
                color: #fff;
                margin: 0;
                padding: 0;
            }

            .dropdown {
                position: absolute;
                top: 20px;
                right: 20px;
                color: #fff;
                cursor: pointer;
                border-radius: 5px;
                padding: 10px;
                display: inline-block;
                text-align: center;
                font-size: 16px;
                font-weight: bold;
            }

            .dropdown-content {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                border-radius: 5px;
                box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                z-index: 1;
            }

            .dropdown-content a {
                color: #fff;
                padding: 10px 16px;
                text-decoration: none;
                display: block;
                border: none;
                border-radius: 5px;
                font-size: 16px;
            }

            .dropdown-content a:hover {
                background-color: #f40612;
            }

            .dropdown:hover .dropdown-content {
                display: block;
            }

            .container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: calc(100vh - 50px);
                padding: 20px;
            }
            .container h1 {
                margin-bottom: 20px;
            }
            .container form {
                width: 100%;
                max-width: 600px;
                background: #222;
                padding: 20px;
                border-radius: 10px;
            }
            .container label, .container input, .container select, .container button {
                width: 95%;
                padding: 10px;
                margin: 10px 0;
                border: none;
                border-radius: 5px;
            }
            .container input[type="submit"] {
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
                width: auto;
                display: inline-block;
            }
            .container input[type="submit"]:hover {
                background-color: #f40612;
            }

            .container .button_back {
                background-color: #FF6633;
                color: #fff;
                cursor: pointer;
                border: none;
                border-radius: 5px;
                padding: 10px;
                text-align: center;
                margin-top: 10px;
                font-size: 16px;
                text-decoration: none;
                width: auto;
                display: inline-block;
            }

            .container .button_back:hover {
                background-color: #ba5900;
            }

            .error, .success {
                color: #e50914;
                margin-bottom: 10px;
            }
            .success {
                color: #00ff00;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="dropdown">
                <button class="dropbtn"><?php echo htmlspecialchars($user_data['nickname']); ?>▼</button>
                <div class="dropdown-content">
                    <a href="welcome.php">หน้าแรก</a>
                    <a href="calculate_food.php">คำนวณจากอาหาร</a>
                    <a href="cu.php">เครื่องคิดเลขคำนวณแคลอรี่จากกิจกรรม</a>
                    <a href="edit_profile.php">แก้ไขข้อมูล</a>
                    <a href="nn.php">สาระความรู้</a>
                    <a href="logout.php">ออกจากระบบ</a>
                </div>
            </div>

        <div class="container">
            <h1>แก้ไขข้อมูลส่วนตัว</h1>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="error">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="edit_profile.php" method="post">
                <label for="nickname">ชื่อเล่น:</label>
                <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user_data['nickname']); ?>" required>

                <label for="weight">น้ำหนัก (kg):</label>
                <input type="number" id="weight" name="weight" step="0.1" value="<?php echo htmlspecialchars($user_data['weight']); ?>" required>

                <label for="height">ส่วนสูง (cm):</label>
                <input type="number" id="height" name="height" step="0.1" value="<?php echo htmlspecialchars($user_data['height']); ?>" required>

                <label for="age">อายุ:</label>
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user_data['age']); ?>" required>

                <input type="submit" value="อัปเดตข้อมูล">
                
                <a href="welcome.php" class="button_back" >กลับไปยังหน้าแรก</a>
            </form>

        </div>
    </body>
</html>
