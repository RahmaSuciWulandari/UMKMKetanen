<?php
include "config.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Ambil ID lapak dari URL
$id_lapak = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil data lapak
$sql_lapak = "SELECT * FROM lapak WHERE id_lapak = ?";
$stmt = $koneksi->prepare($sql_lapak);
$stmt->bind_param("i", $id_lapak);
$stmt->execute();
$lapak = $stmt->get_result()->fetch_assoc();

if (!$lapak) {
    echo "Lapak tidak ditemukan.";
    exit();
}

// Ambil data produk untuk lapak
$sql_produk = "SELECT * FROM produk WHERE id_lapak = ?";
$stmt = $koneksi->prepare($sql_produk);
$stmt->bind_param("i", $id_lapak);
$stmt->execute();
$result_produk = $stmt->get_result();

if (isset($_POST['logout'])) {
    $_SESSION['message'] = "Anda telah logout, Silahkan login terlebih dahulu";
    session_unset();
    session_destroy();
    header('location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Produk - <?php echo htmlspecialchars($lapak['nama_lapak']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="logokkn.jpg" />
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex">
        <!-- Sidebar Fixed -->
  
        <div class="w-64 bg-white shadow-md fixed h-screen overflow-y-auto" style="background-image: linear-gradient(45deg, #ff512f 0%, #f09819 51%, #ff512f 100%);">
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
        <div class="flex-1 p-6 ml-64 overflow-y-auto">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h1 class="text-2xl font-semibold mb-6">Produk dari Lapak: <?php echo htmlspecialchars($lapak['nama_lapak']); ?></h1>

                <!-- Tombol Tambah Produk -->
                <div class="mb-4">
                    <a href="tambah_produk.php?id_lapak=<?php echo $id_lapak; ?>" class="bg-orange-600 text-white px-4 py-2 rounded-md">+ Tambah Produk</a>
                </div>

                <!-- Table -->
                <div class="bg-white shadow-md rounded-md overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">No.</th>
                                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Nama Produk</th>
                                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Harga</th>
                                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Deskripsi</th>
                                <th class="py-2 px-4 bg-gray-100 text-left text-sm font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = $result_produk->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 border-b font-medium text-gray-800"><?php echo $no++; ?></td>
                                    <td class="py-2 px-4 border-b font-medium text-gray-800"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                    <td class="py-2 px-4 border-b text-gray-600"><?php echo "Rp " . number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td class="py-2 px-4 border-b text-gray-600"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <div class="flex space-x-2">
                                            <a href="edit_produk.php?id=<?php echo $row['id_produk']; ?>" class="text-yellow-500 hover:text-yellow-600">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="hapus_produk.php" method="POST" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $row['id_produk']; ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-600" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
