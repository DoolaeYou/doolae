<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// รับข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT nickname, weight, height, age, gender FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// ตรวจสอบการส่งข้อมูล (สำหรับการคำนวณ BMR และ TDEE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exercise = isset($_POST['exercise']) ? $_POST['exercise'] : null;

    if (!empty($exercise)) {
        $weight = $user_data['weight'];
        $height = $user_data['height'];
        $age = $user_data['age'];

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

        // ส่งข้อมูลไปยัง SweetAlert2
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'ผลการคำนวณ',
                    html: 'แคลอรี่ที่ควรกิน ต่อวัน: " . number_format($bmr, 2) . " kcal/day<br>แคลอรี่ที่ไม่ควรกินเกิน ต่อวัน: " . number_format($tdee, 2) . " kcal/day<br>BMI: " . number_format($bmi, 2) . "<br>สถานะ: " . htmlspecialchars($interpretation) . "',
                    icon: 'info',
                    showCancelButton: true,
                    cancelButtonText: 'ปิด',
                    confirmButtonText: 'บันทึก',
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            console.log('Sending data:', {
                                'user_id': $user_id,
                                'bmr': $bmr,
                                'tdee': $tdee,
                                'bmi': $bmi,
                                'bmi_status': '$interpretation'
                            });
                            fetch('save_data.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams({
                                    'user_id': $user_id,
                                    'bmr': $bmr,
                                    'tdee': $tdee,
                                    'bmi': $bmi,
                                    'bmi_status': '$interpretation'
                                })
                            }).then(response => response.text())
                            .then(result => {
                                console.log('Save result:', result);
                                resolve(result);
                            });
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'welcome.php'; // ไปที่หน้าอื่นหลังจากบันทึก
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
                    text: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    icon: 'error',
                    confirmButtonText: 'ปิด'
                });
            });
        </script>";
    }
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

<!DOCTYPE html>
<html lang="th">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยินดีต้อนรับ</title>
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
        <h1>ยินดีต้อนรับ, <?php echo htmlspecialchars($user_data['nickname']); ?></h1>
        <form action="welcome.php" method="post">
            <label for="exercise">ช่วงนี้คุณออกกำลังกายบ่อย?</label>
            <select id="exercise" name="exercise" required>
                <option value="sedentary">ไม่ได้ออกกำลังกายเลย</option>
                <option value="light">ออกกำลังกายอาทิตย์ละ 1-3 วัน</option>
                <option value="moderate">ออกกำลังกายอาทิตย์ละ 3-5 วัน</option>
                <option value="active">ออกกำลังกายอาทิตย์ละ 6-7 วัน</option>
                <option value="extremely active">ออกกำลังกายอย่างหนักทุกวันเช้าเย็น</option>
            </select>
            <input type="submit" value="คำนวณ">
            <a href="history.php" class="button_htr">ดูประวัติการคำนวณ</a>
        </form>
    </div>
</body>
</html>
