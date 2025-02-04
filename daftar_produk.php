<?php
include "config.php";

// Fungsi untuk mencatat klik produk
function catatKlikProduk($idProduk) {
  global $koneksi;
  $ipAddress = $_SERVER['REMOTE_ADDR']; // Mengambil IP pengunjung

  // Cek apakah IP ini sudah tercatat untuk produk yang sama
  $cekQuery = "SELECT COUNT(*) AS jumlah FROM produk_pengunjung WHERE id_produk = ? AND ip_address = ?";
  $cekStmt = $koneksi->prepare($cekQuery);
  $cekStmt->bind_param("is", $idProduk, $ipAddress);
  $cekStmt->execute();
  $cekResult = $cekStmt->get_result();
  $data = $cekResult->fetch_assoc();

  if ($data['jumlah'] == 0) {
      // Jika belum ada, masukkan data
      $stmt = $koneksi->prepare("INSERT INTO produk_pengunjung (id_produk, ip_address) VALUES (?, ?)");
      $stmt->bind_param("is", $idProduk, $ipAddress);
      $stmt->execute();
  }
}


// Tangkap input pencarian dari form
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination setup
$perPage = 12; // Jumlah produk per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Query untuk menghitung total produk
$countQuery = "SELECT COUNT(*) AS total FROM produk WHERE nama_produk LIKE ? OR kategori_produk LIKE ?";
$countStmt = $koneksi->prepare($countQuery);
$searchTermWithWildcards = "%" . $searchTerm . "%";
$countStmt->bind_param("ss", $searchTermWithWildcards, $searchTermWithWildcards);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Query untuk menampilkan produk dengan pagination
$sql = "SELECT produk.id_produk, produk.nama_produk, produk.kategori_produk, produk.harga, 
               MIN(produk_gambar.file_path) AS file_path,
               COALESCE((SELECT COUNT(*) FROM produk_pengunjung WHERE id_produk = produk.id_produk), 0) AS total_klik
        FROM produk 
        LEFT JOIN produk_gambar ON produk.id_produk = produk_gambar.id_produk 
        WHERE produk.nama_produk LIKE ? OR produk.kategori_produk LIKE ? 
        GROUP BY produk.id_produk 
        LIMIT ? OFFSET ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ssii", $searchTermWithWildcards, $searchTermWithWildcards, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

?>



<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produk UMKM</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@900&display=swap" rel="stylesheet">
  <link rel="icon" href="logokkn.jpg" />
  <style>
    .custom-font { font-family: 'Roboto', sans-serif; }
    .custom-navbar {
      background-image: linear-gradient(45deg, #ff512f 0%, #f09819 51%, #ff512f 100%);
      background-size: 200% auto;
      transition: 0.5s;
    }
    .custom-navbar:hover { background-position: right center; }
  </style>
</head>
<body class="bg-gray-100">
  <div class="sticky top-0 custom-navbar p-4 flex items-center rounded-b-lg shadow-lg">
    <div class="text-white custom-font mr-4 mt-5 text-center">
      <a href="index.php" class="text-white custom-font mr-4 text-center"> 
        <h1 class="font-extrabold text-3xl italic tracking-wider">UMKM</h1>
        <h3 class="font-bold text-xl italic tracking-wider">Desa Ketanen</h3>
      </a>
    </div>
    <div class="relative flex-grow">
      <form action="daftar_produk.php" method="GET" class="relative">
        <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" class="w-full p-3 rounded-full pl-12 text-gray-700" placeholder="Cari">
        <button type="submit" class="absolute left-4 top-4 text-gray-500"><i class="fas fa-search"></i></button>
      </form>
    </div>
  </div>

  <div class="p-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $gambar = $row['file_path'] ?: 'default.jpg'; 
            echo '<a href="isi.php?id_produk=' . $row['id_produk'] . '" class="block">';
            echo '<div class="bg-white p-4 rounded-lg shadow-lg hover:shadow-xl transition duration-300">';
            echo '<img src="' . $gambar . '" alt="' . htmlspecialchars($row['nama_produk']) . '" class="w-full h-40 object-cover rounded-lg" />';
            echo '<div class="p-2">';
            echo '<h3 class="text-sm font-bold">' . htmlspecialchars($row['nama_produk']) . '</h3>';
            
            echo '<div class="flex justify-between items-center mt-2">';
            echo '<span class="text-red-500 font-bold">Rp. ' . number_format($row['harga'], 0, ',', '.') . '</span>';  
            // echo '<span class="text-gray-400 text-xs">' . $row['total_klik'] . ' Dilihat</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</a>';
        }
    } else {
        echo "<p class='text-center'>Tidak ada produk yang ditemukan.</p>";
    }
    ?>
</div>
<!-- Pagination -->
<div class="mt-8 flex justify-center space-x-2">
            <!-- Tombol Previous -->
            <?php if ($page > 1): ?>
                <a href="?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page - 1; ?>" class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600 transition duration-300">«</a>
            <?php endif; ?>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $i; ?>" 
                   class="px-3 py-2 rounded transition duration-300 <?php echo ($i == $page) ? 'bg-orange-700 text-white' : 'bg-gray-200 hover:bg-teal-500 hover:text-white'; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <?php if ($page < $totalPages): ?>
                <a href="?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page + 1; ?>" class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-teal-600 transition duration-300">»</a>
            <?php endif; ?>
        </div>
        <br/>
        <footer class="bg-orange-500 text-white py-8">
  <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
    <div class="text-center md:text-left">
      <h3 class="text-2xl font-bold">UMKM<br/>Desa Ketanen</h3>
      <!-- <div class="flex space-x-4 mt-4">
        <a href="https://wa.me/6282333888807" target="_blank" rel="noopener noreferrer" class="hover:text-teal-400">
          <i class="fab fa-whatsapp"></i>
        </a>
        <a href="https://www.instagram.com/wgp_dinopark?igsh=eHhkbXhqZW5kNG13" class="hover:text-teal-400">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="https://www.tiktok.com/@wgp.dino.park?_t=ZS-8tGecttmHgI&_r=1" class="hover:text-teal-400">
          <i class="fab fa-tiktok"></i>
        </a>
        <a href="https://www.facebook.com/profile.php?id=61559314482951" class="hover:text-teal-400">
          <i class="fab fa-facebook"></i>
        </a>
      </div> -->
    </div><br/>
    <!-- <div class="text-center md:text-left mt-8 md:mt-0">-->
      <h4 class="font-bold">KETANEN BERDAYA UMKM TERPERCAYA</h4>
    <!--  <ul class="mt-4 space-y-2">
        <li><a href="index.php" class="hover:text-teal-400">HOME</a></li>
        <li><a href="produk.php" class="hover:text-teal-400">PRODUK</a></li>
        <li><a href="lapak.php" class="hover:text-teal-400">LAPAK</a></li>
        <li><a href="tentang.php" class="hover:text-teal-400">TENTANG KAMI</a></li>
      </ul>
    </div> -->
    <div class="text-center md:text-left mt-8 md:mt-0">
      <!-- <h4 class="font-bold">Let's Talk</h4> -->
      <ul class="mt-4 space-y-2">
        <!-- <li><a href="tel:+6282333888807" class="hover:text-teal-400">(+62) 823-3388-8807</a></li> -->
        <li>Desa Ketanen, </li>
        <li>Kecamatan Panceng, Kabupaten Gresik</li>
      </ul>
    </div>
  </div>
  <div class="text-center mt-8">
    <p>
      © Copyright 2024 UMKMKetanen. All Rights Reserved Design By 
      <a class="text-white hover:text-black" href="https://www.instagram.com/kknuq_ketanen2025?igsh=MXR3OTJrcnlmcnh4bA==">
        KKNUQDesaKetanen
      </a>
    </p>
  </div>
</footer>
</body>
</html>

<?php
$koneksi->close();
?>
