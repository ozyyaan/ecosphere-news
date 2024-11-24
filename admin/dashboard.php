<?php
session_start();

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Sertakan file koneksi
require_once '../config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .dashboard-container {
            width: 80%;
            margin: 30px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        .nav-menu {
            display: flex;
            gap: 15px;
        }
        .nav-menu a {
            text-decoration: none;
            color: #333;
            padding: 8px 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .nav-menu a:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <div class="nav-menu">
                <a href="approve_news.php">Kelola Berita</a>
                <a href="manage_users.php">Kelola Pengguna</a>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="dashboard-content">
            <h2>Selamat Datang, Admin!</h2>
            <div class="stats">
                <div class="stat-box">
                    <h3>Jumlah Berita Pending</h3>
                    <?php
                    $query = "SELECT COUNT(*) as pending_count FROM news WHERE status = 'pending'";
                    $result = mysqli_query($koneksi, $query);

                    if ($result) {
                        $row = mysqli_fetch_assoc($result);
                        echo "<p>" . $row['pending_count'] . " Berita</p>";
                    } else {
                        echo "<p>Error: " . mysqli_error($koneksi) . "</p>";
                    }
                    ?>
                </div>
                <div class="stat-box">
                    <h3>Jumlah Pengguna</h3>
                    <?php
                    $query = "SELECT COUNT(*) as user_count FROM users";
                    $result = mysqli_query($koneksi, $query);

                    if ($result) {
                        $row = mysqli_fetch_assoc($result);
                        echo "<p>" . $row['user_count'] . " Pengguna</p>";
                    } else {
                        echo "<p>Error: " . mysqli_error($koneksi) . "</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
