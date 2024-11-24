<?php
session_start();
include '../config/database.php';

// Cek apakah user sudah login dan memiliki akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$success = $error = '';

// Proses hapus user
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if ($user_id === false) {
        $error = "ID user tidak valid";
    } else if ($user_id === 1) {
        $error = "Tidak dapat menghapus akun admin utama";
    } else {
        // Gunakan prepared statement untuk delete
        $stmt = $koneksi->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = "User berhasil dihapus";
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=" . urlencode($success));
            exit();
        } else {
            $error = "Gagal menghapus user: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil daftar user
$query = "SELECT * FROM users ORDER BY id";
$result = $koneksi->query($query);

// Ambil success message dari URL jika ada
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .role-admin {
            color: #dc3545;
            font-weight: bold;
        }
        .role-user {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manajemen User</h1>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="<?= $user['role'] === 'admin' ? 'role-admin' : 'role-user' ?>">
                                <?= htmlspecialchars(strtoupper($user['role'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ((int)$user['id'] !== 1): ?>
                                <a href="?action=delete&id=<?= htmlspecialchars($user['id']) ?>" 
                                   class="action-btn btn-danger"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                    Hapus
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: center;">
            <a href="../index.php" class="action-btn btn-success">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>