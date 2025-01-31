<?php
include "config.php";
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Cek apakah ID lapak diberikan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Hapus data lapak berdasarkan ID
    $delete_sql = "DELETE FROM lapak WHERE id_lapak = ?";
    $delete_stmt = $koneksi->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        header("Location: adminlapak.php");
        exit();
    } else {
        echo "Error: " . $koneksi->error;
    }
} else {
    header("Location: adminlapak.php");
    exit();
}
?>
