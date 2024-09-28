<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #141414;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #222;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
        }
        .container h1 {
            margin-bottom: 20px;
        }
        .container form {
            display: flex;
            flex-direction: column;
        }
        .container input[type="text"],
        .container input[type="password"] {
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
        }
        .container input[type="submit"] {
            background-color: #e50914;
            color: #fff;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            padding: 10px;
            width: 48%;
            box-sizing: border-box;
        }
        .container input[type="submit"]:hover {
            background-color: #f40612;
        }
        .container .button {
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            width: 48%;
            box-sizing: border-box;
            text-align: center;
        }
        .container .button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <h1>เข้าสู่ระบบ</h1>
        <form action="login_process.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo '<p class="error">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
            ?>
            <div class="button-container">
                <input type="submit" value="เข้าสู่ระบบ">
                <a href="register.php" class="button">สมัครใช้งาน</a>
            </div>
        </form>
    </div>
</body>
</html>
