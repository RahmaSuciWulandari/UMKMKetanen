<?php
include "config.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$data = [
    "total_pengunjung" => 0,
    "total_produk" => 0,
    "total_lapak" => 0
];

try {
    // Dapatkan alamat IP pengunjung
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $current_time = date('Y-m-d H:i:s');

    // Periksa apakah tabel 'total_kunjungan' memiliki entri
    $sql_check_total_kunjungan = "SELECT COUNT(*) as count FROM total_kunjungan WHERE id = 1";
    $result_check_total_kunjungan = $koneksi->query($sql_check_total_kunjungan);
    $row_check_total_kunjungan = $result_check_total_kunjungan->fetch_assoc();

    if ($row_check_total_kunjungan['count'] == 0) {
        // Jika tidak ada data, tambahkan entri default
        $sql_insert_total = "INSERT INTO total_kunjungan (id, total) VALUES (1, 0)";
        $koneksi->query($sql_insert_total);
    }

    // Periksa apakah pengunjung ini sudah tercatat
    $sql_check_ip = "SELECT * FROM pengunjung WHERE ip_address = ?";
    $stmt_check_ip = $koneksi->prepare($sql_check_ip);
    $stmt_check_ip->bind_param("s", $ip_address);
    $stmt_check_ip->execute();
    $result_check_ip = $stmt_check_ip->get_result();

    if ($result_check_ip->num_rows > 0) {
        // Jika IP sudah ada, periksa waktu kunjungan terakhir
        $row = $result_check_ip->fetch_assoc();
        $last_visit = strtotime($row['kunjungan_terakhir']);
        $current_time_unix = strtotime($current_time);

        $timeout = 3600; // Batas waktu (1 jam)

        if (($current_time_unix - $last_visit) > $timeout) {
            // Perbarui waktu kunjungan terakhir
            $sql_update_time = "UPDATE pengunjung SET kunjungan_terakhir = ? WHERE ip_address = ?";
            $stmt_update_time = $koneksi->prepare($sql_update_time);
            $stmt_update_time->bind_param("ss", $current_time, $ip_address);
            $stmt_update_time->execute();

            // Tambahkan total kunjungan
            $sql_update_total = "UPDATE total_kunjungan SET total = total + 1 WHERE id = 1";
            $koneksi->query($sql_update_total);
        }
    } else {
        // Jika IP belum ada, masukkan data baru
        $sql_insert_ip = "INSERT INTO pengunjung (ip_address, kunjungan_terakhir) VALUES (?, ?)";
        $stmt_insert_ip = $koneksi->prepare($sql_insert_ip);
        $stmt_insert_ip->bind_param("ss", $ip_address, $current_time);
        $stmt_insert_ip->execute();

        // Tambahkan total kunjungan
        $sql_update_total = "UPDATE total_kunjungan SET total = total + 1 WHERE id = 1";
        $koneksi->query($sql_update_total);
    }

    // Ambil total pengunjung
    $sql_total_visits = "SELECT total FROM total_kunjungan WHERE id = 1";
    $result_total_visits = $koneksi->query($sql_total_visits);
    if ($result_total_visits && $row = $result_total_visits->fetch_assoc()) {
        $data['total_pengunjung'] = $row['total'] ?? 0;
    }
} catch (Exception $e) {
    echo "Kesalahan: " . $e->getMessage();
}

// Hitung total produk
$sql_produk = "SELECT COUNT(*) as total FROM produk";
$result_produk = $koneksi->query($sql_produk);
$data['total_produk'] = ($result_produk && $row_produk = $result_produk->fetch_assoc()) ? $row_produk['total'] : 0;

// Hitung total lapak
$sql_lapak = "SELECT COUNT(*) as total FROM lapak";
$result_lapak = $koneksi->query($sql_lapak);
$data['total_lapak'] = ($result_lapak && $row_lapak = $result_lapak->fetch_assoc()) ? $row_lapak['total'] : 0;

// Logout
if (isset($_POST['logout'])) {
    $_SESSION['message'] = "Anda telah logout, Silahkan login terlebih dahulu";
    session_unset();
    session_destroy();
    header('location: index.php');
    exit();
}

if (!isset($_SESSION["username"])) {
    $_SESSION['message'] = "Anda telah logout, Silahkan login terlebih dahulu";
    header('location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="icon" href="logokkn.png" />
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="flex h-screen" >
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-md" style="background-image: linear-gradient(45deg, #ff512f 0%, #f09819 51%, #ff512f 100%);">
    <div class="p-6">
        <div class="text-white custom-font mr-4 text-center">
            <a href="index.php" class="text-white custom-font mr-4 text-center">
                <h1 class="font-extrabold text-3xl italic tracking-wider">UMKM</h1>
                <h3 class="font-bold text-xl italic tracking-wider">Desa Ketanen</h3>
            </a>
        </div>
        <nav class="space-y-2">
            <a href="admin2.php" class="text-white flex items-center p-2 hover:bg-orange-600 hover:text-white rounded-md">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <span>Dashboard</span>
            </a>
            <a href="adminlapak.php" class="text-white flex items-center p-2 hover:bg-orange-600 hover:text-white rounded-md">
                <i class="fas fa-map-marker-alt mr-3"></i>
                <span>Lapak</span>
            </a>
        </nav>
    </div>
    <div class="absolute bottom-0 p-6">
        <form action="" method="post" class="flex items-center p-2 text-white hover:bg-orange-600 hover:text-white rounded-md w-58">
            <i class="fas fa-sign-out-alt mr-3"></i>
            <input type="submit" name="logout" value="Logout" class="bg-transparent text-white hover:text-white cursor-pointer">
        </form>
        <p class="text-gray-200 text-sm mt-6">© KKNUQDesaKetanen</p>
    </div>
</div>


    <!-- Main content -->
    <div class="flex-1 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-gray-600 mb-4">Total Pengunjung</h2>
                <div class="text-3xl font-bold text-gray-900"><?php echo $data['total_pengunjung']; ?></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-gray-600 mb-4">Total Produk</h2>
                <div class="text-3xl font-bold text-gray-900"><?php echo $data['total_produk']; ?></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-gray-600 mb-4">Total Lapak</h2>
                <div class="text-3xl font-bold text-gray-900"><?php echo $data['total_lapak']; ?></div>
            </div>
        </div>
    </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll("nav a");

    // Function to handle active link
    const setActiveLink = (link) => {
      navLinks.forEach((nav) => nav.classList.remove("bg-black", "text-white"));
      link.classList.add("bg-orange-600", "text-white");
    };

    // Attach event listener to all nav links
    navLinks.forEach((link) => {
      link.addEventListener("click", () => {
        setActiveLink(link); // Set active class
      });
    });

    // Set active link based on the current URL
    const currentPath = window.location.pathname.split("/").pop();
    navLinks.forEach((link) => {
      if (link.getAttribute("href").endsWith(currentPath)) {
        setActiveLink(link);
      }
    });
  });
</script>
</body>
</html>
