<?php
include "config.php";

// Pastikan parameter 'id_lapak' ada di URL
if (!isset($_GET['id_lapak'])) {
    die("ID Lapak tidak ditemukan.");
}

$id_lapak = intval($_GET['id_lapak']);

// Ambil data lapak dan gambar lapak
$sql_lapak = "SELECT lapak.id_lapak, lapak.nama_lapak, lapak.deskripsi, lapak.alamat, lapak.kontak, MIN(lapak_gambar.file_pathg) AS file_pathg 
    FROM lapak 
    LEFT JOIN lapak_gambar ON lapak.id_lapak = lapak_gambar.id_lapak
    WHERE lapak.id_lapak = ? 
    LIMIT 1";
$stmt_lapak = $koneksi->prepare($sql_lapak);
$stmt_lapak->bind_param("i", $id_lapak);
$stmt_lapak->execute();
$result_lapak = $stmt_lapak->get_result();

if ($result_lapak->num_rows > 0) {
    $lapak = $result_lapak->fetch_assoc();
} else {
    die("Lapak tidak ditemukan.");
}

// Ambil data produk dari lapak
$sql_produk = "SELECT id_produk, nama_produk, harga, 
    (SELECT file_path FROM produk_gambar WHERE produk_gambar.id_produk = produk.id_produk LIMIT 1) AS gambar 
    FROM produk WHERE id_lapak = ?";
$stmt_produk = $koneksi->prepare($sql_produk);
$stmt_produk->bind_param("i", $id_lapak);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
$produk_list = [];
while ($row = $result_produk->fetch_assoc()) {
    $produk_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($lapak['nama_lapak']); ?> - Lapak</title>
  <!-- Menggunakan Font Awesome untuk ikon -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    /* Reset dasar */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f9f9f9;
      color: #333;
      line-height: 1.6;
    }
    a {
      text-decoration: none;
      color: inherit;
    }
    /* Header Sticky ala Shopee */
    .header {
      position: sticky;
      top: 0;
      z-index: 1000;
      background-color: #fff;
      border-bottom: 1px solid #eaeaea;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .header .logo {
      font-size: 26px;
      font-weight: bold;
      color: #ff5722;
    }
    .header .search-container {
      flex: 1;
      margin: 0 20px;
      position: relative;
    }
    .header .search-container input {
      width: 100%;
      padding: 12px 45px 12px 20px;
      border: 1px solid #ddd;
      border-radius: 30px;
      font-size: 16px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .header .search-container button {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #888;
      font-size: 18px;
      cursor: pointer;
    }
    .header .user-icon {
      font-size: 24px;
      color: #888;
    }
    /* Kontainer Utama */
    .main-container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 0 15px;
    }
    /* Header Lapak */
    .lapak-header {
      background-color: #fff;
      border-radius: 5px;
      padding: 25px;
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .lapak-header img {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 20px;
    }
    .lapak-info h1 {
      font-size: 28px;
      margin-bottom: 10px;
      color: #ff5722;
    }
    .lapak-info p {
      font-size: 16px;
      color: #777;
      margin-bottom: 10px;
    }
    .lapak-info .contact-btn {
      display: inline-block;
      background-color: #25D366;
      color: #fff;
      padding: 10px 20px;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 500;
      transition: background 0.3s;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .lapak-info .contact-btn:hover {
      background-color: #128C7E;
    }
    /* Grid Produk */
    .produk-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
    }
    .produk-card {
      background-color: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    .produk-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .produk-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      transition: transform 0.3s;
    }
    .produk-card img:hover {
      transform: scale(1.05);
    }
    .produk-card .card-content {
      padding: 15px;
    }
    .produk-card .card-content h2 {
      font-size: 20px;
      margin-bottom: 8px;
      color: #333;
      height: 50px;
      overflow: hidden;
      text-align: center;
    }
    .produk-card .card-content .price {
      font-size: 18px;
      font-weight: bold;
      color: #e60012;
      text-align: center;
    }
    /* Footer Sederhana */
    .footer {
      background-color: #fff;
      border-top: 1px solid #eaeaea;
      padding: 15px;
      text-align: center;
      margin-top: 30px;
      color: #777;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <!-- <header class="header">
    <div class="logo">Shopee-Like</div>
    <div class="search-container">
      <input type="text" placeholder="Cari produk...">
      <button><i class="fas fa-search"></i></button>
    </div>
    <div class="user-icon"><i class="fas fa-user-circle"></i></div>
  </header> -->

  <div class="main-container">
    <!-- Header Lapak -->
    <section class="lapak-header">
      <img src="<?php echo htmlspecialchars($lapak['file_pathg'] ?? 'https://placehold.co/90x90'); ?>" alt="Lapak">
      <div class="lapak-info">
        <h1><?php echo htmlspecialchars($lapak['nama_lapak']); ?></h1>
        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($lapak['alamat']); ?></p>
        <a class="contact-btn" href="https://wa.me/<?php echo htmlspecialchars($lapak['kontak']); ?>" target="_blank">
          <i class="fas fa-comment-dots"></i> Hubungi Penjual
        </a>
      </div>
    </section>

    <!-- Grid Produk -->
    <section class="produk-grid">
      <?php foreach ($produk_list as $produk): ?>
        <a class="produk-card" href="isi.php?id_produk=<?php echo $produk['id_produk']; ?>">
          <img src="<?php echo htmlspecialchars($produk['gambar'] ?? 'https://placehold.co/200x200'); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
          <div class="card-content">
            <h2 ><?php echo htmlspecialchars($produk['nama_produk']); ?></h2>
            <div class="price">Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </section>
  </div>
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
