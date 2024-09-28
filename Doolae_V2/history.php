<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// รับข้อมูลประวัติการคำนวณจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM user_health_data WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการคำนวณ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #141414;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        table {
            width: 100%;
            max-width: 800px;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #222;
        }
        tr:nth-child(even) {
            background-color: #1e1e1e;
        }
        a.button {
            display: inline-block;
            font-size: 16px; /* ปรับขนาดตัวอักษร */
            font-family: Arial, sans-serif;
            border: none;
            border-radius: 5px;
            padding: 12px 24px; /* ปรับขนาด padding */
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            text-align: center;
            margin-top: 20px;
        }
        a.button:hover {
            background-color: #0056b3;
        }
        form {
            display: inline;
        }
        button.delete-button {
            font-size: 16px; /* ปรับขนาดตัวอักษร */
            font-family: Arial, sans-serif;
            border: none;
            border-radius: 5px;
            padding: 8px 2px; /* ปรับขนาด padding */
            background-color: #ff4d4d;
            color: #fff;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }
        button.delete-button:hover {
            background-color: #e60000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ประวัติการคำนวณ</h1>
        <table>
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>BMR</th>
                    <th>TDEE</th>
                    <th>BMI</th>
                    <th>สถานะ</th>
                    <th></th> <!-- เพิ่มคอลัมน์สำหรับปุ่มลบ -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo number_format($row['bmr'], 2); ?> kcal/day</td>
                        <td><?php echo number_format($row['tdee'], 2); ?> kcal/day</td>
                        <td><?php echo number_format($row['bmi'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['bmi_status']); ?></td>
                        <td>
                            <!-- ฟอร์มสำหรับปุ่มลบ -->
                            <form action="delete_entry.php" method="POST">
                                <input type="hidden" name="entry_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="delete-button">ลบ</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="welcome.php" class="button">กลับไปที่หน้าหลัก</a>
    </div>
</body>
</html>
