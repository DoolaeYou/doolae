<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// รับข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id'];

// ฟังก์ชันดึงข้อมูลประวัติการคำนวณแคลอรี่
function getCalorieHistory($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, date, total_calories, status FROM calorie_history WHERE user_id = ? ORDER BY date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// ฟังก์ชันลบข้อมูล
function deleteCalorieEntry($conn, $entry_id, $user_id) {
    $stmt = $conn->prepare("DELETE FROM calorie_history WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    return $stmt->execute();
}

// ตรวจสอบว่ามีการส่งคำขอลบข้อมูลหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    if (deleteCalorieEntry($conn, $delete_id, $user_id)) {
        header("Location: calorie_history.php");
        exit();
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบข้อมูล');</script>";
    }
}

$calorie_history = getCalorieHistory($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการคำนวณแคลอรี่</title>
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
            justify-content: center;
            padding: 20px;
        }
        .container h1 {
            margin-bottom: 20px;
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
        .button_back {
            background-color: #007bff;
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
        .button_back:hover {
            background-color: #0056b3;
        }
        .button_delete {
            background-color: #dc3545;
            color: #fff;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            text-align: center;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .button_delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>ประวัติการคำนวณแคลอรี่</h1>
    <table>
        <tr>
            <th>วันที่</th>
            <th>แคลอรี่รวม</th>
            <th>สถานะ</th>
            <th></th>
        </tr>
        <?php while ($row = $calorie_history->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['total_calories']); ?> กิโลแคลอรี่</td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <form method="post" action="">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="button_delete">ลบ</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table> <br>
    <a href="calculate_food.php" class="button_back">กลับไปยังหน้า เลือกเมนูอาหาร</a>
</div>
</body>
</html>
