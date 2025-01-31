<?php
include "config.php";

// Periksa apakah parameter 'id_lapak' ada dalam URL
if (!isset($_GET['id_lapak'])) {
    die("ID Lapak tidak ditemukan.");
}

$id_lapak = intval($_GET['id_lapak']); // Sanitasi input

// Query untuk mengambil informasi lapak dan gambar lapak
$sql_lapak = "SELECT lapak.id_lapak, lapak.nama_lapak, lapak.deskripsi,lapak.alamat, lapak.kontak, MIN(lapak_gambar.file_pathg) AS file_pathg 
        FROM lapak 
        LEFT JOIN lapak_gambar ON lapak.id_lapak = lapak_gambar.id_lapak
              WHERE lapak.id_lapak = ? 
              LIMIT 1"; // Ambil satu gambar saja
$stmt_lapak = $koneksi->prepare($sql_lapak);
$stmt_lapak->bind_param("i", $id_lapak);
$stmt_lapak->execute();
$result_lapak = $stmt_lapak->get_result();

// Periksa apakah lapak ditemukan
if ($result_lapak->num_rows > 0) {
    $lapak = $result_lapak->fetch_assoc();
} else {
    die("Lapak tidak ditemukan.");
}

// Query untuk mengambil daftar produk dari lapak ini
$sql_produk = "SELECT id_produk, nama_produk, harga, 
              (SELECT file_path FROM produk_gambar WHERE produk_gambar.id_produk = produk.id_produk LIMIT 1) AS gambar 
              FROM produk WHERE id_lapak = ?";
$stmt_produk = $koneksi->prepare($sql_produk);
$stmt_produk->bind_param("i", $id_lapak);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
$produk_list = [];

while ($row_produk = $result_produk->fetch_assoc()) {
    $produk_list[] = $row_produk;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lapak['nama_lapak']); ?> - Lapak</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f1f1;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #ff5722;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .logo {
            font-size: 24px;
            color: white;
            font-weight: bold;
        }

        .header .search-bar {
            display: flex;
            background-color: white;
            border-radius: 25px;
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .header .search-bar input {
            border: none;
            padding: 10px;
            width: 100%;
            font-size: 14px;
        }

        .header .search-bar button {
            background-color: #ff5722;
            border: none;
            padding: 10px 15px;
            color: white;
            cursor: pointer;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .lapak-header {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .lapak-header img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-right: 20px;
        }

        .lapak-info h1 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .lapak-info p {
            font-size: 14px;
            color: #777;
            margin: 5px 0;
        }

        .lapak-info .contact {
            margin-top: 15px;
        }

        .lapak-info .contact a {
            color: white;
            background-color: #25D366;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .produk-card {
            background: #fff;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .produk-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .produk-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }

        .produk-card h2 {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        .produk-card .price {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 10px;
        }

        .produk-card a {
            display: block;
            text-decoration: none;
                        color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
            text-align: center;
            transition: background 0.3s;
        }

        
    </style>
</head>
<body>
    <!-- Header -->
    <!-- <div class="header">
        <div class="logo">Marketplace</div>
        <div class="search-bar">
            <input type="text" placeholder="Cari produk...">
            <button><i class="fas fa-search"></i></button>
        </div>
    </div> -->

    <div class="container">
        <!-- Header Lapak -->
        <div class="lapak-header">
            <img src="<?php echo htmlspecialchars($lapak['file_pathg'] ?? 'https://placehold.co/70x70'); ?>" alt="Lapak">
            <div class="lapak-info">
                <h1><?php echo htmlspecialchars($lapak['nama_lapak']); ?></h1>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($lapak['alamat']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($lapak['deskripsi'])); ?></p>
                <div class="contact">
                    <a href="https://wa.me/<?php echo htmlspecialchars($lapak['kontak']); ?>" class="chat-seller">
                        <i class="fas fa-comment-dots"></i> Hubungi Penjual
                    </a>
                </div>
            </div>
        </div>

        <!-- Daftar Produk -->
        <div class="produk-grid">
            <?php foreach ($produk_list as $produk): ?>
                <div class="produk-card">
                    <!-- Link untuk mengarahkan ke halaman isi.php -->
                    <a href="isi.php?id_produk=<?php echo $produk['id_produk']; ?>">
                        <img src="<?php echo htmlspecialchars($produk['gambar'] ?? 'https://placehold.co/200x200'); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                        <h2><?php echo htmlspecialchars($produk['nama_produk']); ?></h2>
                        <p class="price">Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>
            
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>