<?php
include "config.php";
session_start();

$login_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Ambil hash password dari database berdasarkan username
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $koneksi->query($sql);

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            // Verifikasi password
            if (password_verify($password, $data['password'])) {
                $_SESSION["username"] = $data["username"];
                header("location: admin2.php");
                exit();
            } else {
                $login_message = "Password salah.";
            }
        } else {
            $login_message = "Akun tidak ditemukan.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="iconlogo.jpg" />
    <link rel="stylesheet" href="style3.css" type="text/css">
</head>
<body class="body_login" >
    <div class="kotak">
        <form action="" method="post" class="formlogin">
        <h2>Login</h2><br/>
            <i><?php echo $login_message; ?></i><br/>
            <div class="inputbox">
                <label>Username</label>
                <input type="text" id="username" name="username" required autocomplete="off"><br>
            </div>
            <div class="inputbox">
                <label>Password</label>
                <input type="password" id="password" name="password" required autocomplete="off"><br>
            </div>
            <input type="submit" name="login" value="Login">
        </form>
    </div>
</body>
</html>
