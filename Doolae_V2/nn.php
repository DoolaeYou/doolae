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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doolae (ดูแล)</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
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
            color: #fff;
            top: 100%;
            right: 0;
            border-radius: 5px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            background-color: #141414;
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
            color: #fff;
        }

        footer {
            color: #fff;
        }
    </style>
</head>
<body style="background-color: #141414;">
    <div class="container">
        <div class="dropdown">
            <button class="dropbtn"><?php echo htmlspecialchars($user_data['nickname']); ?>▼</button>
            <div class="dropdown-content" color: #fff>
                <a href="welcome.php">หน้าแรก</a>
                <a href="calculate_food.php">คำนวณจากอาหาร</a>
                <a href="cu.php">เครื่องคิดเลขคำนวณแคลอรี่จากกิจกรรม</a>
                <a href="edit_profile.php">แก้ไขข้อมูล</a>
                <a href="nn.php">สาระความรู้</a>
                <a href="logout.php">ออกจากระบบ</a>
            </div>
        </div>
    </div>    
        
<br><br><br><br>
        <main>
            <div class="container">
                <div class="p-4 mb-4 bg-light rounded-3">
                    <div class="container-fluid py-5">
                        <h1 class="display-5 fw-bold">โปรแกรมคำนวนค่าดัชนีมวลกาย - BMI</h1>
                        <p class="col-md-8 fs-4">คือ เป็นมาตรการที่ใช้ประเมินภาวะอ้วนและผอมในผู้ใหญ่ ตั้งแต่อายุ 20 ปีขึ้นไป สามารถทำได้โดยการชั่งน้ำหนักตัวเป็นกิโลกรัม และวัดส่วนสูงเป็นเซนติเมตร แล้วนำมาหาดัชนีมวลกาย โดยใช้โปรแกรมวัดค่าความอ้วนข้างต้น<br>Note. ทฤษฎี การประเมินระดับความอ้วนด้วยสูตรคำนวน BMI เป็นการประเมินจากค่าเฉลี่ยเชิงสถิติ ผลการคำนวณที่ได้อาจคลาดเคลื่อนจากความเป็นจริง โดยเฉพาะผู้ที่ออกกำลังกายเป็นประจำ หรือกลุ่มนักเพาะกายที่มีปริมาณกล้ามเนื้อสูง</p>
                    </div>
                </div>

                <div class="row align-items-md-stretch">
                    <div class="col-md-6">
                        <div class="h-90 p-5 bg-light border rounded-3">
                            <h2>คำนวณการเผาผลาญพลังงาน (BMR)</h2>
                            <p>คือ อัตราการความต้องการเผาผลาญของร่างกายในชีวิตประจำวัน หรือจำนวนแคลอรี่ขั้นต่ำที่ต้องการใช้ในชีวิตแต่ละวัน ดังนั้นการคำนวณ BMR จะช่วยให้คุณคำนวณปริมาณแคลอรี่ที่ใช้ต่อวันเพื่อรักษาน้ำหนักปัจจุบันได้ และเมื่ออายุมากขึ้นเราจะควบคุมน้ำหนักได้ยากขึ้น เพราะ BMR เราลดลง วิธีป้องกันคือ "หมั่นออกกำลังกาย" เพื่อเพิ่มประสิทธิภาพของการเผาผลาญ ซึ่งจะทำให้ BMR ไม่ลดลงเร็วเกินไป</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="h-90 p-5 bg-light border rounded-3">
                            <h2>คำนวณแคลลอรี่อาหาร (Food Energy)</h2>
                            <p>คือ การทราบหน่วยของพลังงาน ไม่ว่าเป็นพลังงานที่เราเผาผลาญ พลังงานที่เราใช้ หรือว่าพลังงานที่อยู่ในอาหารที่เรากินเข้าไป ล้วนแต่มีหน่วยเป็นแคลอรี่ หากคำนวณเสร็จแล้ว นั่นคือปริมาณแคลอรี่ที่ร่างกายเราใช้ต่อวัน โดยส่วนใหญ่ผู้ชายจะอยู่ที่ 1600-2200 kcal. ผู้หญิงจะอยู่ที่ 1400-1800 kcal. เมื่อรู้ปริมาณแคลอรี่ที่ร่างกายเราเผาผลาญต่อวันแล้วเราก็มาวางแผนเพื่อคำนวณแคลอรี่ของอาหารแต่ละมื้อ</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="container">
            <header>
                <br><br><br>
                <h1 style="color: #fff;">แนะนำการควบคุมการรับประทานอาหารและการออกกำลังกาย</h1>
            </header>

            <section id="introduction" style="color: #fff;">
                <h2>ควบคุมการรับประทานอาหาร</h2>
                <p>การควบคุมการรับประทานอาหารเป็นส่วนสำคัญในการรักษาสุขภาพของร่างกาย โดยการรับประทานอาหารที่มีประโยชน์และความสมดุล เช่น ผักผลไม้ อาหารที่มีโปรตีนสูง เป็นต้น จะช่วยให้ร่างกายได้รับสารอาหารที่จำเป็น และลดความเสี่ยงต่อโรคต่างๆ อย่างมีประสิทธิภาพ</p>
            </section>

            <section id="benefits" style="color: #fff;">
                <h2>ข้อดีของการควบคุมการรับประทานอาหาร</h2>
                <ul>
                    <li>ช่วยควบคุมน้ำหนักตัว</li>
                    <li>ลดความเสี่ยงต่อโรคต่างๆ เช่น โรคหัวใจ โรคเบาหวาน และความดันโลหิตสูง</li>
                    <li>เพิ่มพลังงานและสมรรถภาพร่างกาย</li>
                    <li>เสริมสร้างสุขภาพทั้งกายและจิตใจ</li>
                </ul>
            </section>

            <section id="exercise" style="color: #fff;">
                <h2>การออกกำลังกาย</h2>
                <p>การออกกำลังกายเป็นส่วนสำคัญของการรักษาสุขภาพ นอกจากการควบคุมการรับประทานอาหารอย่างถูกต้องแล้ว การออกกำลังกายสม่ำเสมอจะช่วยเสริมสร้างกล้ามเนื้อ ลดไขมันส่วนเกิน และเพิ่มระดับพลังงานในร่างกาย</p>
            </section>

            <section id="exercise_benefits" style="color: #fff;">
                <h2>ข้อดีของการออกกำลังกาย</h2>
                <ul>
                    <li>เพิ่มระดับพลังงานและความเคลื่อนไหว</li>
                    <li>ปรับสมดุลน้ำหนักตัว</li>
                    <li>ลดความเครียดและเสี่ยงต่อโรคที่เกี่ยวข้องกับการขาดการออกกำลังกาย เช่น โรคหัวใจ โรคเบาหวาน และภาวะเสี่ยงต่อมะเร็ง</li>
                    <li>เสริมสร้างระบบภูมิคุ้มกัน</li>
                </ul>
            </section>

        </div>
    </div>
    <div class="container"style="color: #fff;">
    <header >
        <h1>แนะนำการควบคุมการรับประทานอาหารและการออกกำลังกาย</h1>
    </header>
    <section id="exercise" style="color: #fff;">
        <p>เพื่อความสะดวกในการวางแผนการออกกำลังกายของคุณ คุณสามารถใช้การคำนวณต่อไปนี้เพื่อหาว่าต้องการออกกำลังกายอย่างไรเพื่อเผาผลาญแคลอรี่ตามที่ต้องการ</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <label for="calories_goal">จำนวนแคลอรี่ที่ต้องการเผาผลาญ (กิโลแคลอรี่):</label>
            <input type="number" class="form-control" id="calories_goal" name="calories_goal" min="1" required><br>
            <button type="submit" class="btn btn-primary" style="display: block; margin: 0 auto;">คำนวณ</button>
        </form>
    </section>
    
        <section id="calculation_result" style="color: #fff;">
            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $calories_goal = $_POST['calories_goal'];

                    $distance_per_calorie = 60;   // คำนวณวิ่ง 60 calorie per min
                    $wall_per_calorie = 5;    //  คำนวณเดิน 5 calorie per min
                    $jumprope_calorie = 11.6;   //  คำนวณกระโดดเชือก 11.6 calorie per min
                    $ride_a_bike_calorie = 9;   //  คำนวณปั่นจักรยาน 9 calorie per min
                    $swim_calorie = 7.5;   //  คำนวณว่ายน้ำ 7.5 calorie per min
                    $Aerobics_calorie = 6;   //  คำนวณแอโรบิค 6 calorie per min
                    
                    $distance_required = $calories_goal / $distance_per_calorie;
                    $wall_required = $calories_goal / $wall_per_calorie; 
                    $jumprope_required = $calories_goal / $jumprope_calorie;
                    $ride_a_bike_required = $calories_goal / $ride_a_bike_calorie;
                    $swim_required = $calories_goal / $swim_calorie;
                    $Aerobics_required = $calories_goal / $Aerobics_calorie;

                    echo "<p>เพื่อเผาผลาญแคลอรี่ $calories_goal กิโลแคลอรี่</p>";

                    echo "<p>วิ่งประมาณ   : " . round($distance_required) . " นาที</p>";
                    echo "<p>การเดิน : " . round($wall_required) . " นาที</p>";
                    echo "<p>กระโดดเชือก : " . round($jumprope_required) . " นาที</p>";
                    echo "<p>ปั่นจักรยาน : " . round($ride_a_bike_required) . " นาที</p>";
                    echo "<p>ว่ายน้ำ : " . round($swim_required) . " นาที</p>";
                    echo "<p>แอโรบิค : " . round($Aerobics_required) . " นาที</p>";
                }
            ?>
        </section>  <br><br><br>
    <script src="js/bootstrap.min.js"></script>

</body>
</html>
