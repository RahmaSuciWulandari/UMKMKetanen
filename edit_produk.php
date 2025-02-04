<?php
include "config.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID produk dari URL
$id_produk = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil data produk berdasarkan ID
$sql_produk = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = $koneksi->prepare($sql_produk);
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if (!$produk) {
    echo "Produk tidak ditemukan.";
    exit();
}

// Ambil data gambar terkait produk
$sql_gambar = "SELECT * FROM produk_gambar WHERE id_produk = ?";
$stmt_gambar = $koneksi->prepare($sql_gambar);
$stmt_gambar->bind_param("i", $id_produk);
$stmt_gambar->execute();
$gambar_rows = $stmt_gambar->get_result()->fetch_all(MYSQLI_ASSOC);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update data produk
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];

    $sql_update = "UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ? WHERE id_produk = ?";
    $stmt = $koneksi->prepare($sql_update);
    $stmt->bind_param("sdsi", $nama_produk, $harga, $deskripsi, $id_produk);

    if ($stmt->execute()) {
        // Proses upload gambar baru
        if (isset($_FILES['gambar']['name']) && count($_FILES['gambar']['name']) > 0) {
            foreach ($_FILES['gambar']['name'] as $index => $filename) {
                if ($_FILES['gambar']['error'][$index] == 0) {
                    $target_dir = "uploads/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true); // Pastikan folder uploads ada dan dapat menulis
                    }
                    $target_file = $target_dir . basename($filename);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
                    // Cek apakah gambar valid
                    if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                        if (move_uploaded_file($_FILES['gambar']['tmp_name'][$index], $target_file)) {
                            // Menyimpan nama gambar yang diupload ke database
                            $unique_file_name = $target_file;
                            $sql_insert_gambar = "INSERT INTO produk_gambar (id_produk, file_path, uploaded_at) VALUES (?, ?, NOW())";
                            $stmt_insert_gambar = $koneksi->prepare($sql_insert_gambar);
                            $stmt_insert_gambar->bind_param("is", $id_produk, $unique_file_name);
                            if (!$stmt_insert_gambar->execute()) {
                                echo "Gagal menyimpan gambar ke database.";
                            }
                        } else {
                            echo "Gambar gagal diupload.";
                        }
                    } else {
                        echo "Hanya gambar dengan ekstensi JPG, JPEG, PNG, GIF yang diperbolehkan.";
                    }
                }
            }
        }

        header("Location: edit_produk.php?id=" . $id_produk);
        exit();
    } else {
        echo "Gagal memperbarui data.";
    }
}

// Proses hapus gambar
if (isset($_GET['delete_gambar_id'])) {
    $id_gambar = $_GET['delete_gambar_id'];

    // Ambil file_path dari database
    $sql_hapus_gambar = "SELECT file_path FROM produk_gambar WHERE id_gambar = ?";
    $stmt_hapus_gambar = $koneksi->prepare($sql_hapus_gambar);
    $stmt_hapus_gambar->bind_param("i", $id_gambar);
    $stmt_hapus_gambar->execute();
    $result_hapus = $stmt_hapus_gambar->get_result()->fetch_assoc();

    if ($result_hapus) {
        $file_path = "uploads/" . $result_hapus['file_path'];

        // Hapus file dari folder
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Hapus data dari database
        $sql_delete_gambar = "DELETE FROM produk_gambar WHERE id_gambar = ?";
        $stmt_delete_gambar = $koneksi->prepare($sql_delete_gambar);
        $stmt_delete_gambar->bind_param("i", $id_gambar);
        $stmt_delete_gambar->execute();

        header("Location: edit_produk.php?id=" . $id_produk);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="logokkn.jpg" />
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen items-center justify-center px-4 mt-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-4xl overflow-auto max-h-screen">
            <h1 class="text-2xl font-semibold mb-4">Edit Produk</h1>
            <form method="POST" enctype="multipart/form-data">
                <!-- Form Data Produk -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Nama Produk</label>
                    <input type="text" name="nama_produk" value="<?php echo $produk['nama_produk']; ?>" class="w-full p-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Harga</label>
                    <input type="number" name="harga" value="<?php echo $produk['harga']; ?>" class="w-full p-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" class="w-full p-2 border rounded-md" required><?php echo $produk['deskripsi']; ?></textarea>
                </div>

                <!-- Form Gambar Produk -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Gambar Produk</label>
                    <?php if (!empty($gambar_rows)) { ?>
                        <?php foreach ($gambar_rows as $gambar) { ?>
                            <div class="mb-2">
                                <?php 
                                echo '<img src="' . $gambar['file_path'] . '?v=' . time() . '" alt="Gambar Produk" class="w-24 h-24 object-cover rounded-md shadow">';
                                echo '<a href="edit_produk.php?id=' . $id_produk . '&delete_gambar_id=' . $gambar["id_gambar"] . '" class="text-red-500 text-sm">Hapus Gambar</a>';
                                ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <input type="file" name="gambar[]" multiple class="w-full p-2 border rounded-md">
                </div>

                <div class="flex justify-end space-x-4">
                <a href="cekproduk.php?id=<?php echo $produk['id_lapak']; ?>" class="px-4 py-2 bg-gray-300 rounded-md">Kembali</a>
                <button type="submit" class="px-4 py-2 bg-teal-500 text-white rounded-md">Simpan</button>
            </div>
            <br />
            </form>
        </div>
    </div>
</body>

</html>
