<?php
include 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit();
}

$artikel_id = mysqli_real_escape_string($koneksi, $_GET['id']);
$user_id = $_SESSION['user_id'];

// Cek apakah artikel milik user yang sedang login
$cek_query = "SELECT * FROM news WHERE id = '$artikel_id' AND user_id = '$user_id'";
$cek_result = mysqli_query($koneksi, $cek_query);

if (!$cek_result || mysqli_num_rows($cek_result) == 0) {
    $_SESSION['pesan_error'] = "Artikel tidak ditemukan atau Anda tidak memiliki izin menghapusnya.";
    header("Location: profile.php");
    exit();
}

// Ambil data artikel sebelum dihapus
$artikel = mysqli_fetch_assoc($cek_result);

// Hapus artikel
$hapus_query = "DELETE FROM news WHERE id = '$artikel_id'";
$hapus_result = mysqli_query($koneksi, $hapus_query);

if ($hapus_result) {
    if (!empty($artikel['gambar']) && file_exists($artikel['gambar'])) {
        unlink($artikel['gambar']);
    }
    $_SESSION['pesan_sukses'] = "Artikel berhasil dihapus.";
} else {
    $_SESSION['pesan_error'] = "Gagal menghapus artikel.";
}

header("Location: profile.php");
exit();
