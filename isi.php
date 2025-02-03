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
    $stmt = $koneksi->prepare($sql);
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
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .left-column {
            flex: 1;
            max-width: 50%;
            text-align: center;
        }
        .right-column {
            flex: 1;
            max-width: 50%;
        }
        .product-image img {
            width: 400px;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }
        .product-image img:hover {
            transform: scale(1.05);
        }
        .product-thumbnails {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        .product-thumbnails img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }
        .product-thumbnails img:hover {
            transform: scale(1.1);
            border: 2px solid #ff5722;
        }
        .product-title {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
        }
        .product-price {
            font-size: 28px;
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .product-description {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .keunggulan-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease-in-out;
        }
        .keunggulan-card:hover {
            transform: translateY(-8px);
        }
        .keunggulan-icon {
            background: #e3fcef;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .keunggulan-icon img {
            width: 30px;
            height: 30px;
        }
        .keunggulan-title {
            font-size: 14px;
            font-weight: bold;
            color: #444;
        }
        .buttons {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            justify-content: space-between;
        }

        .buy-now, .chat-seller {
            padding: 18px 25px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            transition: transform 0.3s ease, background 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48%;
        }

        .buy-now {
            background: #ff5722;
            color: white;
        }

        .buy-now:hover {
            background: #e64a19;
            transform: translateY(-5px);
        }

        .chat-seller {
            background: #3498db;
            color: white;
        }

        .chat-seller:hover {
            background: #2980b9;
            transform: translateY(-5px);
        }

        /* Tambahkan margin-left pada ikon untuk memberi spasi antara ikon dan teks */
        .buy-now i, .chat-seller i {
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 20px;
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

        <p class="product-price">Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>

        <!-- Deskripsi Produk -->
        <h2>Deskripsi Produk</h2>
        <p class="product-description"><?php echo nl2br(htmlspecialchars($produk['deskripsi'])); ?></p>

        <!-- Keunggulan Produk -->
        <?php if (!empty($keunggulan_produk)): ?>
            <div style="margin-top: 10vh;">
                <div class="keunggulan-card-container" style="display: flex; gap: 20px; flex-wrap: wrap;">
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
            <a href="lapak.php?id_lapak=<?php echo $produk['id_lapak']; ?>" class="chat-seller">
                <i class="fas fa-eye"></i> Lihat lapak
            </a>
        </div>
    </div>
</div>
</body>
</html>
