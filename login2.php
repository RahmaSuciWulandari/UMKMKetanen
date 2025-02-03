<?php
session_start();
include "config.php"; // Pastikan file ini berisi koneksi ke database

$login_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $password = $_POST['password'];

        // Ambil data user berdasarkan username
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $koneksi->query($sql);

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            // Verifikasi password
            if (password_verify($password, $data['password'])) {
                $_SESSION["username"] = $data["username"];
                header("Location: admin2.php");
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

<html>
  <head>
    <title>Admin Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>
    <style>
      body {
        margin: 0;
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(135deg, #ff7043, #ffb74d); /* Orange gradient */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        color: white;
      }
      .container {
        display: flex;
        max-width: 1000px;
        background-color: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      }
      .left {
        background-image: url('bg5.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        flex: 1.5;
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
      }
      .left h1 {
        font-size: 48px;
        font-weight: 700;
        margin-top: 45vh;
      }
      .left h2 {
        font-size: 20px;
        font-weight: 500;
      }
      .right {
        padding: 40px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
      }
      .right h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #333;
      }
      .right p {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
        text-align: center;
      }
      .right input[type="text"],
      .right input[type="password"] {
        width: 100%;
        padding: 14px;
        margin: 12px 0;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 16px;
        outline: none;
        transition: border-color 0.3s, box-shadow 0.3s;
      }
      .right input[type="text"]:focus,
      .right input[type="password"]:focus {
        border-color: #ff7043;
        box-shadow: 0 0 8px rgba(255, 112, 67, 0.3);
      }
      .right button {
        background-color: #ff7043;
        color: white;
        padding: 14px 40px;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        font-size: 18px;
        transition: background-color 0.3s, transform 0.3s;
      }
      .right button:hover {
        background-color: #ff5722;
        transform: translateY(-3px);
      }
      .terms {
        font-size: 12px;
        color: #888;
        margin-top: 20px;
        text-align: center;
      }
      .terms a {
        color: #ff7043;
        text-decoration: none;
      }
      .error-message {
        color: red;
        font-size: 14px;
        margin-bottom: 15px;
      }

      /* Animasi untuk tombol login */
      @keyframes buttonClick {
        0% { transform: scale(1); }
        50% { transform: scale(0.95); }
        100% { transform: scale(1); }
      }

      .right button:active {
        animation: buttonClick 0.3s ease;
      }

      @media (max-width: 768px) {
        .container {
          flex-direction: column;
          width: 80%;
        }
        .container {
          align-items: center;
          padding: 20px;
        }
        .right input[type="text"],
        .right input[type="password"] {
          width: 80%;
        }
        .left {
          display: none;
        }
        .right {
          width: 100%;
          padding: 30px;
        }
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="left">
        <h1>UMKM Desa Ketanen</h1>
        <h2>Empowering Local Businesses</h2>
      </div>
      <div class="right">
        <h1><i class="fas fa-user-lock"></i><br/>Admin Portal</h1>
        <p>Please enter your credentials to access the admin dashboard</p>
        <i class="error-message"><?php echo $login_message; ?></i>
        <form method="POST" action="">
          <input type="text" name="username" placeholder="Username" required/>
          <input type="password" name="password" placeholder="Password" required/>
          <button type="submit" name="login">Login</button>
        </form>
        /* <div class="terms">
          By logging in, you agree to our <a href="#">Terms of Service</a>.
        </div> */
      </div>
    </div>
  </body>
</html>