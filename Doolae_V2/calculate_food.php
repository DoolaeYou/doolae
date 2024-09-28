<?php
// เริ่มเซสชั่นและเชื่อมต่อฐานข้อมูล
session_start();
require 'db_connection.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// รับข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id']; // เพิ่มบรรทัดนี้เพื่อให้แน่ใจว่า $user_id มีค่า
$stmt = $conn->prepare("SELECT nickname, weight, height, age, gender FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// ตรวจสอบว่า $user_data เป็น null หรือไม่
if ($user_data === null) {
    echo "ไม่พบข้อมูลผู้ใช้ หรือเกิดข้อผิดพลาดในการดึงข้อมูล.";
    exit();
}

// ฟังก์ชันดึงประเภทอาหาร
function getFoodTypes($conn) {
    $stmt = $conn->prepare("SELECT DISTINCT type_food FROM db_food");
    $stmt->execute();
    $result = $stmt->get_result();
    $types = [];
    while ($row = $result->fetch_assoc()) {
        $types[] = $row['type_food'];
    }
    return $types;
}

// ฟังก์ชันดึงรายชื่ออาหารตามประเภท
function getFoodByType($conn, $type) {
    $stmt = $conn->prepare("SELECT name_food, calories_food FROM db_food WHERE type_food = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    return $stmt->get_result();
}

$food_types = getFoodTypes($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณแคลอรี่ของอาหาร</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #141414;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .container1 {
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

        .container label, .container select, .container input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }
        .container input[type="submit"],

        .container .button {
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
            width: auto; /* ทำให้ขนาดของปุ่มปรับขนาดตามเนื้อหา */
            display: inline-block; /* ให้ปุ่มแสดงในแนวนอน */
        }

        .container input[type="submit"]:hover,

        .container .button:hover {
            background-color: #f40612;
        }

        .container .button_htr {
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

        .container .button_htr:hover {
            background-color: #0056b3;
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

        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .form-row > div {
            flex: 1;
            margin-right: 10px;
        }
        .form-row > div:last-child {
            margin-right: 0;
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

    </style>
</head>
<body>

    <div class="container1">
        <div class="dropdown">
            <button class="dropbtn"><?php echo htmlspecialchars($user_data['nickname']); ?> ▼</button>
            <div class="dropdown-content">
                <a href="welcome.php">หน้าแรก</a>
                <a href="calculate_food.php">คำนวณจากอาหาร</a>
                <a href="cu.php">เครื่องคิดเลขคำนวณแคลอรี่จากกิจกรรม</a>
                <a href="edit_profile.php">แก้ไขข้อมูล</a>
                <a href="nn.php">สาระความรู้</a>
                <a href="logout.php">ออกจากระบบ</a>
            </div>
        </div>
    </div>
    
<div class="container">
    <h2>เลือกเมนูอาหาร</h2>
    <form method="post" action="">
        <?php for ($i = 1; $i <= 6; $i++): ?>
            <div class="form-row">
                <div>
                    <label for="type_food_<?php echo $i; ?>">ประเภทอาหารที่ <?php echo $i; ?>:</label>
                    <select class="form-select" id="type_food_<?php echo $i; ?>" name="type_food_<?php echo $i; ?>">
                        <option value="">-- เลือกประเภทอาหาร --</option>
                        <?php foreach ($food_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="menu_choices_<?php echo $i; ?>">เลือกเมนูอาหารที่ <?php echo $i; ?>:</label>
                    <input list="menu_choices_list_<?php echo $i; ?>" id="menu_choices_<?php echo $i; ?>" name="menu_choices_<?php echo $i; ?>" placeholder="-- เลือกเมนู --">
                    <datalist id="menu_choices_list_<?php echo $i; ?>">
                        <!-- ตัวเลือกจะถูกเพิ่มด้วย JavaScript -->
                    </datalist>
                </div>
                    <div>
                        <label for="menu_quantity_<?php echo $i; ?>">จำนวน:</label>
                        <input type="number" id="menu_quantity_<?php echo $i; ?>" name="menu_quantity_<?php echo $i; ?>" min="1" value="1">
                    </div>
            </div>
        <?php endfor; ?>
        <button type="submit" class="button">สรุปแคลลอรี่</button>
        <a href="calorie_history.php" class="button_htr">ตารางประวัติการรับประทานอาหารของคุณ</a><br>
        <a href="welcome.php" class="button_back">กลับไปยังหน้าแรก</a>
    </form>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $total_calories = 0;
    
        // คำนวณแคลอรี่รวมจากเมนูที่เลือก
        for ($i = 1; $i <= 6; $i++) {
            $type_food_key = 'type_food_' . $i;
            $menu_choice_key = 'menu_choices_' . $i;
            if (isset($_POST[$menu_choice_key]) && !empty($_POST[$menu_choice_key])) {
                $menu_choice = $_POST[$menu_choice_key];
                $menu_quantity_key = 'menu_quantity_' . $i;
                $menu_quantity = isset($_POST[$menu_quantity_key]) ? intval($_POST[$menu_quantity_key]) : 1;
                $stmt = $conn->prepare("SELECT calories_food FROM db_food WHERE name_food = ?");
                $stmt->bind_param("s", $menu_choice);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $total_calories += $row['calories_food'] * $menu_quantity;
                    }
                }
            }
        }
    
        // ดึงข้อมูล BMR และ TDEE
        $stmt = $conn->prepare("SELECT bmr, tdee FROM user_health_data WHERE user_id = ? ORDER BY date DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $health_data = $result->fetch_assoc();
    
        if ($health_data === null) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'ข้อผิดพลาด',
                        text: 'ไม่สามารถดึงข้อมูล BMR หรือ TDEE ได้',
                        icon: 'error',
                        confirmButtonText: 'รับทราบ'
                    });
                });
            </script>";
        } else {
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
    
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'ผลการคำนวณแคลอรี่',
                        html: 'แคลอรี่รวมทั้งหมด: " . $total_calories . " กิโลแคลอรี่<br>" . $calories_status . "',
                        icon: '" . ($total_calories > $tdee ? "error" : "success") . "',
                        confirmButtonText: 'รับทราบ'
                    });
                });
            </script>";
        }
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
// โหลดรายการอาหารเมื่อเลือกประเภทอาหาร
document.querySelectorAll('select[name^="type_food"]').forEach((select, index) => {
    select.addEventListener('change', function () {
        let type_food = this.value;
        let menu_datalist = document.getElementById('menu_choices_list_' + (index + 1));
        if (type_food) {
            fetch('get_food.php?type=' + encodeURIComponent(type_food))
                .then(response => response.json())
                .then(data => {
                    menu_datalist.innerHTML = '';
                    data.forEach(food => {
                        let option = document.createElement('option');
                        option.value = food.name_food;
                        option.setAttribute('data-calories', food.calories_food);
                        menu_datalist.appendChild(option);
                    });
                });
        } else {
            menu_datalist.innerHTML = '';
        }
    });
});
</script>

</body>
</html>
