<?php
include "config.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lapak = trim($_POST['nama_lapak']);
    $alamat = trim($_POST['alamat']);
    $deskripsi = trim($_POST['deskripsi']);
    $kontak = trim($_POST['kontak']); // Ambil nomor WhatsApp
    $gambar = $_FILES['gambar'];

    // Validasi input
    if (empty($nama_lapak) || empty($alamat) || empty($deskripsi) || empty($kontak) || empty($gambar['name'][0])) {
        echo "<script>alert('Semua kolom wajib diisi!'); history.back();</script>";
        exit();
    }

    // Simpan data lapak ke database
    $sql = "INSERT INTO lapak (nama_lapak, alamat, deskripsi, kontak) VALUES (?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);

    if (!$stmt) {
        echo "<script>alert('Error preparing statement: " . htmlspecialchars($koneksi->error) . "'); history.back();</script>";
        exit();
    }

    $stmt->bind_param("ssss", $nama_lapak, $alamat, $deskripsi, $kontak);

    if ($stmt->execute()) {
        $id_lapak = $stmt->insert_id; // Ambil ID lapak yang baru dibuat
        $_SESSION['id_lapak'] = $id_lapak;

        // Direktori upload
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Proses unggahan gambar
        foreach ($gambar['name'] as $index => $file_name) {
            $file_tmp = $gambar['tmp_name'][$index];
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_types = ["jpg", "jpeg", "png", "gif"];

            // Validasi tipe file
            if (!in_array($file_type, $allowed_types)) {
                echo "<script>alert('Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan!'); history.back();</script>";
                exit();
            }

            // Pastikan nama file tidak konflik
            $base_name = pathinfo($file_name, PATHINFO_FILENAME);
            $final_name = $base_name . '.' . $file_type;
            $file_path = $upload_dir . $final_name;

            $counter = 1;
            while (file_exists($file_path)) {
                $final_name = $base_name . "_" . $counter . '.' . $file_type;
                $file_path = $upload_dir . $final_name;
                $counter++;
            }

            // Pindahkan file ke direktori upload
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Simpan informasi gambar ke database
                $file_pathg = $file_path; // Gunakan variabel $file_pathg untuk tabel lapak_gambar
                $sql_image = "INSERT INTO lapak_gambar (id_lapak, file_pathg, created_at) VALUES (?, ?, NOW())";
                $stmt_image = $koneksi->prepare($sql_image);

                if (!$stmt_image) {
                    echo "<script>alert('Error preparing statement for images: " . htmlspecialchars($koneksi->error) . "'); history.back();</script>";
                    exit();
                }

                $stmt_image->bind_param("is", $id_lapak, $file_pathg);

                if (!$stmt_image->execute()) {
                    echo "<script>alert('Error saving image: " . htmlspecialchars($stmt_image->error) . "'); history.back();</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Error uploading image: $file_name'); history.back();</script>";
                exit();
            }
        }

        // Redirect ke halaman admin
        header("Location: adminlapak.php?id_lapak=" . $id_lapak);
        exit();
    } else {
        echo "<script>alert('Error executing statement: " . htmlspecialchars($stmt->error) . "'); history.back();</script>";
        exit();
    }
}
?>



<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Tambah Lapak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link rel="icon" href="logokkn.png" />
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fileInput = document.getElementById("gambar");
            const dropArea = document.getElementById("drop-area");
            const fileLabel = document.getElementById("file-label");
            const previewArea = document.getElementById("preview-area");

            dropArea.addEventListener("click", function() {
                fileInput.click();
            });

            dropArea.addEventListener("dragover", function(event) {
                event.preventDefault();
                dropArea.classList.add("border-blue-400", "bg-blue-100");
            });

            dropArea.addEventListener("dragleave", function() {
                dropArea.classList.remove("border-blue-400", "bg-blue-100");
            });

            dropArea.addEventListener("drop", function(event) {
                event.preventDefault();
                dropArea.classList.remove("border-blue-400", "bg-blue-100");

                const files = event.dataTransfer.files;
                handleFiles(files);
            });

            fileInput.addEventListener("change", function() {
                const files = fileInput.files;
                handleFiles(files);
            });

            function handleFiles(files) {
                previewArea.innerHTML = "";
                Array.from(files).forEach(file => {
                    if (file && file.type.startsWith("image/")) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            const img = document.createElement("img");
                            img.src = event.target.result;
                            img.alt = file.name;
                            img.classList.add("w-24", "h-24", "object-cover", "rounded", "shadow");
                            previewArea.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
                fileLabel.textContent = files.length > 1 ? `${files.length} files selected` : files[0].name;
            }
        });
    </script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex h-screen">
<div class="w-64 bg-white shadow-md" style="background-image: linear-gradient(45deg, #ff512f 0%, #f09819 51%, #ff512f 100%);">
    <div class="p-6">
        <div class="text-white custom-font mr-4 text-center">
            <a href="index.php" class="text-white custom-font mr-4 text-center">
                <h1 class="font-extrabold text-3xl italic tracking-wider">UMKM</h1>
                <h3 class="font-bold text-xl italic tracking-wider">Desa Ketanen</h3><br/>
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
    <div class="flex-1 p-6">
        <form action="tambah_lapak.php" method="POST" enctype="multipart/form-data">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">Tambah Lapak</h1>
                    <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-md">Simpan</button>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lapak</label>
                        <input class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan Nama Lapak" type="text" name="nama_lapak" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                        <input class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan Lokasi" type="text" name="alamat" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor WhatsApp</label>
                        <input class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan Nomor WhatsApp" type="text" name="kontak" required />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tuliskan Deskripsi Lapak" rows="4" name="deskripsi" required></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gambar</label>
                        <div id="drop-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 flex flex-col items-center justify-center w-full">
                            <div class="text-center">
                                <i class="fas fa-image text-gray-400 text-4xl mb-2"></i>
                                <p class="text-gray-500" id="file-label">+ Tambahkan Gambar</p>
                            </div>
                            <input type="file" id="gambar" name="gambar[]" class="hidden" accept="image/*" multiple required/>
                            <div id="preview-area" class="mt-4 flex flex-wrap gap-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
