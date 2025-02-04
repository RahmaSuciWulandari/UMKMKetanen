<?php
include "config.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil data lapak
$sql = "SELECT * FROM lapak";
$result = $koneksi->query($sql);

//logout
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

//pagging
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$sql = "SELECT * FROM lapak LIMIT $start, $limit";
$result = $koneksi->query($sql);

$total = $koneksi->query("SELECT COUNT(*) as count FROM lapak")->fetch_assoc()['count'];
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Lapak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="logokkn.jpg" />
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
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



        <!-- Main Content -->
        <div class="flex-1 p-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">Daftar Lapak</h1>
                <a href="tambah_lapak.php" class="bg-orange-600 text-white px-4 py-2 rounded-md">+ Tambah Lapak</a>
            </div>

            <!-- Table -->
            <div class="bg-white shadow-md rounded-md overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">No.</th>
                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Nama Lapak</th>
                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Alamat</th>
                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Produk</th>
                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody>
                            <?php 
                            $no = $start + 1; 
                            while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <!-- Nomor -->
                                    <td class="py-2 px-4 border-b font-medium text-gray-800"><?php echo $no++; ?></td>
                                    <!-- Nama Lapak -->
                                    <td class="py-2 px-4 border-b font-medium text-gray-800"><?php echo $row['nama_lapak']; ?></td>
                                    <!-- Alamat -->
                                    <td class="py-2 px-4 border-b text-gray-600"><?php echo $row['alamat']; ?></td>
                                    <!-- Produk -->
                                    <td class="py-2 px-4 border-b text-teal-500">
                                        <a href="cekproduk.php?id=<?php echo $row['id_lapak']; ?>">Cek Produk</a>
                                        
                                    </td>
                                    <!-- Action -->
                                    <td class="py-2 px-4 border-b">
                                        <div class="flex space-x-2">
                                            <!-- Edit -->
                                            <a href="edit_lapak.php?id=<?php echo $row['id_lapak']; ?>" class="text-yellow-500 hover:text-yellow-600">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <!-- Hapus -->
                                            <form action="hapus_lapak.php" method="POST" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $row['id_lapak']; ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-600" onclick="return confirm('Apakah Anda yakin ingin menghapus?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
    </table>
    <div class="mt-4 flex justify-center">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300"><?php echo $i; ?></a>
    <?php endfor; ?>
</div><br/>

</div>

            </div>
        </div>
    </div>
    <script>
  document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll("nav a");

    // Function to handle active link
    const setActiveLink = (link) => {
      navLinks.forEach((nav) => nav.classList.remove("bg-orange-600", "text-white"));
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
