<?php
include "config.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

if (isset($_GET['id_lapak']) && !empty($_GET['id_lapak'])) {
  $id_lapak = $_GET['id_lapak'];
  $_SESSION['id_lapak'] = $id_lapak; // Simpan ke session untuk penggunaan selanjutnya
} elseif (isset($_SESSION['id_lapak']) && !empty($_SESSION['id_lapak'])) {
  $id_lapak = $_SESSION['id_lapak'];
} else {
  // Redirect ke halaman lapak jika ID tidak ditemukan
  header("Location: adminlapak.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['jumlah_produk'])) {
      $jumlah_produk = intval($_POST['jumlah_produk']);
  } elseif (isset($_POST['submit_produk'])) {
      // Handle the submitted product data
      $id_lapak = trim($_POST['id_lapak']);
      $produk = $_POST['produk'];
      $files = $_FILES['produk'];

      foreach ($produk as $index => $item) {
          $nama_produk = $item['nama'];
          $kategori = $item['kategori'];
          $deskripsi = $item['deskripsi'];
          $keunggulan = json_encode($item['keunggulan']);
          $harga = $item['harga'];

          // Insert product into database and get the generated ID
          $sql = "INSERT INTO produk (id_lapak, nama_produk, kategori_produk, deskripsi, keunggulan, harga) 
                  VALUES (?, ?, ?, ?, ?, ?)";
          $stmt = $koneksi->prepare($sql);
          $stmt->bind_param("isssss", $id_lapak, $nama_produk, $kategori, $deskripsi, $keunggulan, $harga);

          if ($stmt->execute()) {
              $id_produk = $stmt->insert_id; // Get the product ID

              // Handle multiple image uploads
              if (isset($files['name'][$index]['gambar']) && is_array($files['name'][$index]['gambar'])) {
                  $upload_dir = "uploads/"; // Directory to save images
                  foreach ($files['name'][$index]['gambar'] as $file_index => $file_name) {
                      $image_name = basename($file_name); // Get the image name without path
                      $target_file = $upload_dir . $image_name; // Define the target file path

                      // Check if file already exists, and if it does, modify the file name
                      if (file_exists($target_file)) {
                          $image_name = pathinfo($file_name, PATHINFO_FILENAME) . '_' . uniqid() . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
                          $target_file = $upload_dir . $image_name; // Update the target file path with the new name
                      }

                      // Upload the file
                      if (move_uploaded_file($files['tmp_name'][$index]['gambar'][$file_index], $target_file)) {
                          // Insert image path into the database
                          $sql_image = "INSERT INTO produk_gambar (id_produk, file_path) VALUES (?, ?)";
                          $stmt_image = $koneksi->prepare($sql_image);
                          $stmt_image->bind_param("is", $id_produk, $target_file);

                          if (!$stmt_image->execute()) {
                              echo "Error saving image: " . $stmt_image->error;
                              exit();
                          }
                      } else {
                          echo "Error uploading image: $file_name";
                          exit();
                      }
                  }
              }
          } else {
              echo "Error: " . $stmt->error;
              exit();
          }
      }

      header("Location: adminlapak.php");
      exit();
  }
}

?>

<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Tambah Produk</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link rel="icon" href="logokkn.png" />
  <script>
    function generateProductForms() {
        const jumlahProduk = parseInt(document.getElementById('jumlah_produk').value);
        const formContainer = document.getElementById('produk-forms');
        formContainer.innerHTML = ''; // Clear previous forms

        for (let i = 1; i <= jumlahProduk; i++) {
            formContainer.innerHTML += `
                <div class="bg-orange-50 p-6 rounded-lg shadow-md mb-6">
                    <h2 class="text-lg font-bold mb-4">Produk ${i}</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Produk</label>
                            <input type="text" name="produk[${i}][nama]" class="w-full py-2 px-4 border border-gray-300 rounded-lg" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori UMKM</label>
                            <input type="text" name="produk[${i}][kategori]" class="w-full py-2 px-4 border border-gray-300 rounded-lg" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga Produk</label>
                            <input type="number" name="produk[${i}][harga]" class="w-full py-2 px-4 border border-gray-300 rounded-lg" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Produk</label>
                            <textarea name="produk[${i}][deskripsi]" class="w-full py-2 px-4 border border-gray-300 rounded-lg" rows="4" required></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keunggulan Produk</label>
                            <div class="flex items-center space-x-4">
                                <label><input type="checkbox" name="produk[${i}][keunggulan][]" value="Sertifikasi Halal"> Sertifikasi Halal</label>
                                <label><input type="checkbox" name="produk[${i}][keunggulan][]" value="Great Quality"> Great Quality</label>
                                <label><input type="checkbox" name="produk[${i}][keunggulan][]" value="Best Seller"> Best Seller</label>
                                <label><input type="checkbox" name="produk[${i}][keunggulan][]" value="Affordable Price"> Affordable Price</label>
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Gambar Produk </label>
                            <input type="file" name="produk[${i}][gambar][]" class="w-full py-2 px-4 border border-gray-300 rounded-lg" accept="image/*" multiple required />
                        </div>
                    </div>
                </div>
            `;
        }
    }
  </script>
</head>
<body class="bg-gray-100 font-sans">
  <div class="flex h-screen">
    <div class="w-64 bg-white shadow-md fixed h-full" style="background-image: linear-gradient(45deg, #ff512f 0%, #f09819 51%, #ff512f 100%);">
    <div class="p-6">
        <div class="text-white custom-font mr-4 text-center">
            <a href="index.php" class="text-white custom-font mr-4 text-center">
                <h1 class="font-extrabold text-3xl italic tracking-wider">UMKM</h1>
                <h3 class="font-bold text-xl italic tracking-wider">Desa Ketanen</h3>
            </a>
        </div><br>
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
    <div class="flex-1 p-6 ml-64">
      <div class="bg-white p-4 rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold mb-6">Tambah Produk</h1>
        <form method="POST" action="" enctype="multipart/form-data" class="mb-6">
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Produk</label>
              <input id="jumlah_produk" type="number" name="jumlah_produk" class="w-full py-2 px-4 border border-gray-300 rounded-lg" min="1" required />
            </div>
            <div class="flex items-end">
              <button type="button" onclick="generateProductForms()" class="bg-orange-500 text-white px-4 py-2 rounded-md">Buat Form</button>
            </div>
          </div>
        </form>
        <form method="POST" action="" enctype="multipart/form-data">
          <input type="hidden" name="id_lapak" value="<?php echo $id_lapak; ?>">
          <div id="produk-forms"></div>
          <button type="submit" name="submit_produk" class="bg-orange-500 text-white px-4 py-2 rounded-md">Simpan Produk</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>