<?php
// คำนวณแคลอรี่รวมจากเมนูที่เลือก
$total_calories = 0;
for ($i = 1; $i <= 6; $i++) {
    // ดำเนินการคำนวณแคลอรี่
}

// ดึงข้อมูล BMR และ TDEE
$stmt = $conn->prepare("SELECT bmr, tdee FROM user_health_data WHERE user_id = ? ORDER BY date DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$health_data = $result->fetch_assoc();

if ($health_data !== null) {
    $bmr = $health_data['bmr'];
    $tdee = $health_data['tdee'];

    $calories_status = '';
    if ($total_calories < $bmr) {
        $calories_status = 'คุณต้องการพลังงานเพิ่มอีก ' . ($bmr - $total_calories) . ' กิโลแคลอรี่';
    } elseif ($total_calories > $tdee) {
        $calories_status = 'คุณรับประทานแคลอรี่เกินจำนวนที่ควรทาน ' . ($total_calories - $tdee) . ' กิโลแคลอรี่';
    } else {
        $calories_status = 'แคลอรี่รวมทั้งหมดอยู่ในช่วงที่เหมาะสม';
    }

    // บันทึกข้อมูลลงในตาราง calorie_history
    $stmt = $conn->prepare("INSERT INTO calorie_history (user_id, date, total_calories, status) VALUES (?, NOW(), ?, ?)");
    $stmt->bind_param("ids", $user_id, $total_calories, $calories_status);
    $stmt->execute();
}
?>
