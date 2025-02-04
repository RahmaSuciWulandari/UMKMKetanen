<?php
include "config.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ID lapak diberikan
if (!isset($_GET['id'])) {
    header("Location: adminlapak.php");
    exit();
}

$id = $_GET['id'];

// Ambil data lapak berdasarkan ID
$sql = "SELECT * FROM lapak WHERE id_lapak = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: adminlapak.php");
    exit();
}

// Ambil data gambar terkait dengan lapak
$sql_images = "SELECT * FROM lapak_gambar WHERE id_lapak = ?";
$stmt_images = $koneksi->prepare($sql_images);
$stmt_images->bind_param("i", $id);
$stmt_images->execute();
$result_images = $stmt_images->get_result();
$images = $result_images->fetch_all(MYSQLI_ASSOC);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lapak = $_POST['nama_lapak'];
    $alamat = $_POST['alamat'];
    $kontak = $_POST['kontak'];

    // Proses mengganti gambar
    $gambar_baru = $_FILES['gambar'];
    $hapus_gambar = isset($_POST['hapus_gambar']) ? $_POST['hapus_gambar'] : [];

    // Hapus gambar lama jika diminta
    foreach ($hapus_gambar as $hapus_id) {
        $sql_delete = "DELETE FROM lapak_gambar WHERE id = ?";
        $stmt_delete = $koneksi->prepare($sql_delete);
        $stmt_delete->bind_param("i", $hapus_id);
        $stmt_delete->execute();

        // Hapus file fisik dari server
        foreach ($images as $img) {
            if ($img['id'] == $hapus_id && file_exists($img['file_pathg'])) {
                unlink($img['file_pathg']);
            }
        }
    }

    // Upload gambar baru jika ada
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    foreach ($gambar_baru['name'] as $index => $file_name) {
        if ($file_name) {
            $file_tmp = $gambar_baru['tmp_name'][$index];
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_pathg = $upload_dir . uniqid() . "." . $file_type;

            if (in_array($file_type, ["jpg", "jpeg", "png", "gif"]) && move_uploaded_file($file_tmp, $file_pathg)) {
                $sql_image = "INSERT INTO lapak_gambar (id_lapak, file_pathg) VALUES (?, ?)";
                $stmt_image = $koneksi->prepare($sql_image);
                $stmt_image->bind_param("is", $id, $file_pathg);
                $stmt_image->execute();
            }
        }
    }

    // Update data lapak
    $update_sql = "UPDATE lapak SET nama_lapak = ?, alamat = ?, kontak = ? WHERE id_lapak = ?";
    $update_stmt = $koneksi->prepare($update_sql);
    $update_stmt->bind_param("sssi", $nama_lapak, $alamat, $kontak, $id);

    if ($update_stmt->execute()) {
        header("Location: adminlapak.php");
        exit();
    } else {
        echo "Error: " . $koneksi->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lapak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="logokkn.png" />
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-md shadow-md">
        <h1 class="text-2xl font-bold mb-4">Edit Lapak</h1>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="nama_lapak" class="block text-sm font-medium text-gray-700">Nama Lapak</label>
                <input type="text" name="nama_lapak" id="nama_lapak" value="<?php echo $data['nama_lapak']; ?>" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="mb-4">
                <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                <textarea name="alamat" id="alamat" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500"><?php echo $data['alamat']; ?></textarea>
            </div>
            <div class="mb-4">
                <label for="kontak" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                <input type="text" name="kontak" id="kontak" value="<?php echo $data['kontak']; ?>" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="mb-4">
                <label for="gambar" class="block text-sm font-medium text-gray-700">Foto Saat Ini</label>
                <div class="grid grid-cols-3 gap-4">
                    <?php foreach ($images as $img): ?>
                        <div>
                            <img src="<?php echo $img['file_pathg']; ?>" alt="Gambar" class="w-24 h-24 object-cover rounded-md shadow">
                            <label>
                                <input type="checkbox" name="hapus_gambar[]" value="<?php echo $img['id']; ?>"> Hapus
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mb-4">
                <label for="gambar" class="block text-sm font-medium text-gray-700">Tambahkan Gambar Baru</label>
                <input type="file" name="gambar[]" multiple
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="flex justify-end space-x-4">
                <a href="adminlapak.php" class="px-4 py-2 bg-gray-300 rounded-md">Batal</a>
                <button type="submit" class="px-4 py-2 bg-teal-500 text-white rounded-md">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>
