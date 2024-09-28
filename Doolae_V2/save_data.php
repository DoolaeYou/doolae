<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $bmr = $_POST['bmr'];
    $tdee = $_POST['tdee'];
    $bmi = $_POST['bmi'];
    $bmi_status = $_POST['bmi_status'];

    // เตรียมการบันทึกข้อมูล
    $stmt = $conn->prepare("
        INSERT INTO user_health_data (user_id, bmr, tdee, bmi, bmi_status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iddss", $user_id, $bmr, $tdee, $bmi, $bmi_status);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
