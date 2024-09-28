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
$stmt = $conn->prepare("SELECT nickname, weight, height, age, gender FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// รับข้อมูลการคำนวณจากแบบฟอร์ม
$weight = isset($_POST['weight']) ? $_POST['weight'] : null;
$height = isset($_POST['height']) ? $_POST['height'] : null;
$age = isset($_POST['age']) ? $_POST['age'] : null;
$exercise = isset($_POST['exercise']) ? $_POST['exercise'] : null;

if (!empty($weight) && !empty($height) && !empty($age) && !empty($exercise)) {
    // คำนวณ BMR และ TDEE
    if ($user_data['gender'] == "male") {
        $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
    } elseif ($user_data['gender'] == "female") {
        $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
    }

    switch ($exercise) {
        case "sedentary":
            $tdee = $bmr * 1.2;
            break;
        case "light":
            $tdee = $bmr * 1.375;
            break;
        case "moderate":
            $tdee = $bmr * 1.55;
            break;
        case "active":
            $tdee = $bmr * 1.725;
            break;
        case "extremely active":
            $tdee = $bmr * 1.9;
            break;
    }

    // คำนวณ BMI และการตีความ
    $bmi = calculateBMI($weight, $height);
    $interpretation = interpretBMI($bmi);

    // เปลี่ยนเส้นทางไปยัง welcome.php พร้อมกับพารามิเตอร์
    header("Location: welcome.php?bmr=" . urlencode(number_format($bmr, 2)) . "&tdee=" . urlencode(number_format($tdee, 2)) . "&bmi=" . urlencode(number_format($bmi, 2)) . "&interpretation=" . urlencode($interpretation));
    exit();
} else {
    echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน'); window.location.href='welcome.php';</script>";
}

function calculateBMI($weight, $height) {
    $bmi = $weight / (($height / 100) * ($height / 100));
    return $bmi;
}

function interpretBMI($bmi) {
    if ($bmi < 18.5) {
        return "ผอมเกินไป";
    } elseif ($bmi >= 18.5 && $bmi < 24.9) {
        return "น้ำหนักปกติ";
    } elseif ($bmi >= 24.9 && $bmi < 29.9) {
        return "อ้วน";
    } elseif ($bmi >= 29.9) {
        return "อ้วนมาก";
    }
}
?>
