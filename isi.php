<?php
include "config.php";

// Periksa apakah parameter 'id_produk' ada dalam URL
if (isset($_GET['id_produk'])) {
    $id_produk = intval($_GET['id_produk']); // Sanitasi input

    // Query SQL untuk mengambil data produk dan informasi lapak
    $sql = "SELECT produk.nama_produk, produk.deskripsi, produk.harga, lapak.alamat, lapak.id_lapak, lapak.kontak
            FROM produk 
            LEFT JOIN lapak ON produk.id_lapak = lapak.id_lapak
            WHERE produk.id_produk = ?";
    
    // Menyiapkan query
    $stmt = $koneksi->prepare(query: $sql);
    if ($stmt === false) {
        die('Error preparing query: ' . $koneksi->error);
    }

    // Mengikat parameter dan menjalankan query
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();

    // Periksa apakah data ditemukan
    if ($result->num_rows > 0) {
        $produk = $result->fetch_assoc();
    } else {
        die("Produk tidak ditemukan.");
    }

    // Query untuk mengambil gambar produk
    $sql_gambar = "SELECT file_path FROM produk_gambar WHERE id_produk = ?";
    $stmt_gambar = $koneksi->prepare($sql_gambar);
    $stmt_gambar->bind_param("i", $id_produk);
    $stmt_gambar->execute();
    $result_gambar = $stmt_gambar->get_result();
    $gambar_produk = [];
    while ($row_gambar = $result_gambar->fetch_assoc()) {
        $gambar_produk[] = $row_gambar['file_path'];
    }

    // Query untuk mengambil keunggulan produk
    $sql_produk = "SELECT keunggulan FROM produk WHERE id_produk = ?";
    $stmt_produk = $koneksi->prepare($sql_produk);
    $stmt_produk->bind_param("i", $id_produk);
    $stmt_produk->execute();
    $result_produk = $stmt_produk->get_result();
    $row_produk = $result_produk->fetch_assoc();
    $keunggulan_produk = json_decode($row_produk['keunggulan'], true);
} else {
    die("ID Produk tidak ditemukan.");
}

// Peta ikon untuk setiap keunggulan
$icon_map = [
    "Sertifikasi Halal" => "images/halal.png",
    "Great Quality" => "images/higenis.png",
    "Best Seller" => "images/bs.png",
    "Affordable Price" => "images/murah.png"
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produk['nama_produk']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            display: flex;
            flex-wrap: wrap;
        }
        .left-column {
            flex: 1;
            max-width: 40%;
            padding-right: 20px;
        }
        .right-column {
            flex: 1;
            max-width: 60%;
        }
        .product-image img {
            width: 100%;
            border-radius: 10px;
        }
        .product-thumbnails {
            display: flex;
            margin-top: 10px;
        }
        .product-thumbnails img {
            width: 60px;
            height: 60px;
            margin-right: 8px;
            cursor: pointer;
            border-radius: 5px;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }
        .product-thumbnails img:hover {
            border-color: #ff5722;
        }
        .product-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .text-gray-500 {
            color: #555;
            font-size: 14px;
            margin-bottom: 6px;
        }
        .text-blue-500 {
            color: #3498db;
            text-decoration: none;
        }
        .text-blue-500:hover {
            text-decoration: underline;
        }
        .product-price {
            font-size: 26px;
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .product-description {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .grid {
            display: grid;
            gap: 6px;
        }
        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        .grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }
        .keunggulan-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
        .keunggulan-icon {
            background: #e3fcef;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .keunggulan-icon img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .keunggulan-title {
            font-size: 14px;
            font-weight: bold;
            color: #444;
        }
        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .buy-now {
            flex: 1;
            background: #ff5722;
            color: white;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
        }
        .buy-now:hover {
            background: #e64a19;
        }
        .chat-seller {
            flex: 1;
            background: #3498db;
            color: white;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
        }
        .chat-seller:hover {
            background: #2980b9;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .left-column, .right-column {
                max-width: 100%;
            }
        }
    </style>
    <script>
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
</head>
<body>
<div class="container">
    <!-- Kolom Kiri (Gambar Produk) -->
    <div class="left-column">
        <div class="product-image">
            <img id="mainImage" src="<?php echo htmlspecialchars($gambar_produk[0] ?? 'https://placehold.co/400x400'); ?>" alt="Gambar Produk">
        </div>
        <div class="product-thumbnails">
            <?php foreach ($gambar_produk as $gambar): ?>
                <img src="<?php echo htmlspecialchars($gambar); ?>" onclick="changeImage('<?php echo htmlspecialchars($gambar); ?>')" alt="Thumbnail Produk">
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kolom Kanan (Detail Produk) -->
    <div class="right-column">
        <h1 class="product-title"><?php echo htmlspecialchars($produk['nama_produk']); ?></h1>

        <!-- Alamat Lapak -->
        <p class="text-gray-500">
            <?php echo htmlspecialchars($produk['alamat']); ?>
            <a class="text-blue-500 font-semibold" href="lapak.php?id_lapak=<?php echo $produk['id_lapak']; ?>">Lihat Lapak</a>
        </p>

        <p class="product-price">Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>
        <p class="product-description"><?php echo nl2br(htmlspecialchars($produk['deskripsi'])); ?></p>

        <!-- Keunggulan Produk -->
        <?php if (!empty($keunggulan_produk)): ?>
            <div class="mt-12">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Keunggulan Produk</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach ($keunggulan_produk as $keunggulan): ?>
                        <div class="keunggulan-card">
                            <div class="keunggulan-icon">
                                <?php
                                    $icon_path = isset($icon_map[$keunggulan]) && file_exists($icon_map[$keunggulan]) ? $icon_map[$keunggulan] : 'images/default.png';
                                ?>
                                <img src="<?php echo $icon_path; ?>" alt="<?php echo htmlspecialchars($keunggulan); ?>" />
                            </div>
                            <h3 class="keunggulan-title"><?php echo htmlspecialchars($keunggulan); ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tombol Aksi -->
        <div class="buttons">
            <a href="https://wa.me/<?php echo htmlspecialchars($produk['kontak']); ?>?text=Halo,%20saya%20ingin%20memesan%20<?php echo urlencode($produk['nama_produk']); ?>" class="buy-now">
                <i class="fas fa-shopping-cart"></i> Beli Sekarang
            </a>
            <a href="https://wa.me/<?php echo htmlspecialchars($produk['kontak']); ?>" class="chat-seller">
                <i class="fas fa-comment-dots"></i> Chat Penjual
            </a>
        </div>
    </div>
</div>
</body>
</html>
