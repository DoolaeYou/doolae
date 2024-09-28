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
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เครื่องคิดเลข คำนวณแคลอรี่</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #141414;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .calculator {
            width: 320px;
            padding: 20px;
            border-radius: 20px;
            background-color: #333;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }
        .screen { 
            width: 87%;
            height: 80px;
            background-color: #000;
            color: #fff;
            text-align: right;
            padding: 20px;
            font-size: 36px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: inset 0 4px 6px rgba(255, 255, 255, 0.1); 
        }
        .button {
            width: 70px;
            height: 70px;
            margin: 5px;
            font-size: 24px;
            border-radius: 50%;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .button-row {
            display: flex;
            justify-content: space-between;
        }
        .button.number {
            background-color: #505050;
            color: white;
        }
        .button.operator {
            background-color: #ff9500;
            color: white;
        }
        .button.special {
            background-color: #a5a5a5;
            color: black;
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
    </style>
    <script>
        let calories_goal = 0;

        function appendToScreen(value) {
            let screen = document.getElementById('screen');
            if(screen.value === "0") {
                screen.value = value;
            } else {
                screen.value += value;
            }
        }

        function clearScreen() {
            document.getElementById('screen').value = '0';
        }

        function calculateTimePerExercise(type) {
            let screen = document.getElementById('screen');
            calories_goal = parseFloat(screen.value);

            if (isNaN(calories_goal) || calories_goal <= 0) {
                screen.value = "Error";
                return;
            }

            let result = '';
            const distance_per_calorie = 60; // วิ่ง 60 cal/min
            const walk_per_calorie = 5; // เดิน 5 cal/min
            const rope_calorie = 11.6; // กระโดดเชือก 11.6 cal/min
            const bike_calorie = 9; // ปั่นจักรยาน 9 cal/min
            const swim_calorie = 7.5; // ว่ายน้ำ 7.5 cal/min
            const aerobics_calorie = 6; // แอโรบิค 6 cal/min

            switch(type) {
                
                case 'run':
                    result = Math.round(calories_goal / distance_per_calorie) + " MIN";
                    break;
                case 'walk':
                    result = Math.round(calories_goal / walk_per_calorie) + " MIN";
                    break;
                case 'rope':
                    result = Math.round(calories_goal / rope_calorie) + " MIN";
                    break;
                case 'bike':
                    result = Math.round(calories_goal / bike_calorie) + " MIN";
                    break;
                case 'swim':
                    result = Math.round(calories_goal / swim_calorie) + " MIN";
                    break;
                case 'aerobics':
                    result = Math.round(calories_goal / aerobics_calorie) + " MIN";
                    break;
                default:
                    result = "Error";
            }

            screen.value = result;
        }
    </script>
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
    </div>

<div class="calculator">
    <input type="text" id="screen" class="screen" readonly value="0">

    <div class="button-row">
        <button class="button special" onclick="clearScreen()">C</button>
        <button class="button operator" onclick="calculateTimePerExercise('rope')">🪢</button>
        <button class="button operator" onclick="calculateTimePerExercise('walk')">🚶🏾</button>
        <button class="button operator" onclick="calculateTimePerExercise('run')">🏃🏽</button>
    </div>
    <div class="button-row">
        <button class="button number" onclick="appendToScreen('7')">7</button>
        <button class="button number" onclick="appendToScreen('8')">8</button>
        <button class="button number" onclick="appendToScreen('9')">9</button>
        <button class="button operator" onclick="calculateTimePerExercise('bike')">🚴‍♂️</button>
    </div>
    <div class="button-row">
        <button class="button number" onclick="appendToScreen('4')">4</button>
        <button class="button number" onclick="appendToScreen('5')">5</button>
        <button class="button number" onclick="appendToScreen('6')">6</button>
        <button class="button operator" onclick="calculateTimePerExercise('swim')">🏊‍♀️</button>
    </div>
    <div class="button-row">
        <button class="button number" onclick="appendToScreen('1')">1</button>
        <button class="button number" onclick="appendToScreen('2')">2</button>
        <button class="button number" onclick="appendToScreen('3')">3</button>
        <button class="button operator" onclick="calculateTimePerExercise('aerobics')">💃</button>
    </div>
    <div class="button-row">
        <button class="button number" style="width: 150px;" onclick="appendToScreen('0')">0</button>
    </div>
</div>

</body>
</html>
