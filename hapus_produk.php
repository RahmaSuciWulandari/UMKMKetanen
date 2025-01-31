<?php
include "config.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Ambil ID produk dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = $_POST['id'];

    // Ambil data produk untuk mendapatkan ID lapak
    $sql_produk = "SELECT id_lapak FROM produk WHERE id_produk = ?";
    $stmt = $koneksi->prepare($sql_produk);
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $produk = $stmt->get_result()->fetch_assoc();

    if ($produk) {
        // Hapus data produk
        $sql_hapus = "DELETE FROM produk WHERE id_produk = ?";
        $stmt = $koneksi->prepare($sql_hapus);
        $stmt->bind_param("i", $id_produk);

        if ($stmt->execute()) {
            header("Location: cekproduk.php?id=" . $produk['id_lapak']);
            exit();
        } else {
            echo "Gagal menghapus produk.";
        }
    } else {
        echo "Produk tidak ditemukan.";
    }
} else {
    echo "Akses tidak valid.";
}
?>
