<?php
include "config.php";

// Tangkap input pencarian dari form
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk menampilkan produk
$sql = "SELECT produk.id_produk, produk.nama_produk, produk.kategori_produk, produk.harga, 
               MIN(produk_gambar.file_path) AS file_path 
        FROM produk 
        LEFT JOIN produk_gambar ON produk.id_produk = produk_gambar.id_produk 
        WHERE produk.nama_produk LIKE ? OR produk.kategori_produk LIKE ? 
        GROUP BY produk.id_produk 
        LIMIT 6"; 

$stmt = $koneksi->prepare($sql);
$searchTermWithWildcards = "%" . $searchTerm . "%"; 
$stmt->bind_param("ss", $searchTermWithWildcards, $searchTermWithWildcards);
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
    <div class="text-white custom-font mr-4 text-center">
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

  <div class="p-2 grid grid-cols-2 md:grid-cols-4 gap-2">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $gambar = $row['file_path'] ?: 'default.jpg'; 
            echo '<a href="isi.php?id_produk=' . $row['id_produk'] . '" class="block">';
            echo '<div class="bg-white p-1 rounded-md shadow-md hover:shadow-lg transition duration-300">';
            echo '<img src="' . $gambar . '" alt="' . htmlspecialchars($row['nama_produk']) . '" class="w-full rounded-md" height="150" />';
            echo '<p class="text-xs mt-1">' . htmlspecialchars($row['nama_produk']) . '</p>';
            echo '<div class="flex justify-between items-center mt-1">';
            echo '<span class="text-red-500 font-bold">Rp. ' . number_format($row['harga'], 0, ',', '.') . '</span>';  
            echo '<span class="text-gray-500 text-xs">10RB+ Dilihat</span>';
            echo '</div>';
            echo '</div>';
            echo '</a>';
        }
    } else {
        echo "<p class='text-center'>Tidak ada produk yang ditemukan.</p>";
    }
    ?>
</div>

</body>
</html>

<?php
$koneksi->close();
?>
