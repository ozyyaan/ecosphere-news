<?php
$host = 'localhost';
$username = 'root';  // Sesuaikan dengan username database Anda
$password = '';      // Sesuaikan dengan password database Anda
$database = 'news_portal';  // Nama database yang Anda buat

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Atur karakter set ke utf8
mysqli_set_charset($koneksi, 'utf8');