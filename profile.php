<?php
include 'config/database.php';
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user
try {
    $query_user = "SELECT * FROM users WHERE id = ?";
    $stmt_user = mysqli_prepare($koneksi, $query_user);
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    
    if (!$result_user) {
        throw new Exception("Gagal mengambil data user: " . mysqli_error($koneksi));
    }
    
    $user = mysqli_fetch_assoc($result_user);
    
    // Query ambil artikel milik user dengan prepared statement
    $query_artikel = "SELECT * FROM news WHERE user_id = ? ORDER BY created_at DESC";
    
    $stmt_artikel = mysqli_prepare($koneksi, $query_artikel);
    mysqli_stmt_bind_param($stmt_artikel, "i", $user_id);
    mysqli_stmt_execute($stmt_artikel);
    
    $result_artikel = mysqli_stmt_get_result($stmt_artikel);
    
    if (!$result_artikel) {
        throw new Exception("Gagal mengambil artikel: " . mysqli_error($koneksi));
    }
    
    // Hitung jumlah artikel
    $artikel_count = mysqli_num_rows($result_artikel);

} catch (Exception $e) {
    // Tangani error
    $_SESSION['pesan_error'] = $e->getMessage();
    $artikel_count = 0;
    $result_artikel = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Tampilkan pesan sukses atau error -->
        <?php if (isset($_SESSION['pesan_sukses'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['pesan_sukses'] ?>
                <?php unset($_SESSION['pesan_sukses']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['pesan_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['pesan_error'] ?>
                <?php unset($_SESSION['pesan_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Profil Header -->
        <?php if (isset($user)): ?>
            <div class="row profile-header">
                <div class="col-12">
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                    <p>
                        <?= htmlspecialchars($user['email']) ?><br>
                        Bergabung sejak <?= date('d M Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Artikel</h5>
                        <p class="card-text display-6"><?= $artikel_count ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Artikel Saya -->
        <div class="card">
            <div class="card-header">
                <h4>Artikel Saya</h4>
            </div>
            <div class="card-body">
                <?php if ($artikel_count > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($artikel = mysqli_fetch_assoc($result_artikel)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($artikel['title']) ?></td>
                                    <td><?= date('d M Y', strtotime($artikel['created_at'])) ?></td>
                                    <td>
                                        <a href="edit_artikel.php?id=<?= $artikel['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <button 
                                            class="btn btn-danger btn-sm btn-hapus" 
                                            data-id="<?= $artikel['id'] ?>" 
                                            data-judul="<?= htmlspecialchars($artikel['title']) ?>"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        Anda belum memiliki artikel.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Artikel -->
    <div class="modal fade" id="modalHapus" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus Artikel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Anda yakin ingin menghapus artikel: <strong id="judulArtikel"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="linkHapus" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handler untuk tombol hapus artikel
        $('.btn-hapus').click(function() {
            var artikelId = $(this).data('id');
            var judulArtikel = $(this).data('judul');
            
            // Set judul artikel di modal
            $('#judulArtikel').text(judulArtikel);
            
            // Set link hapus
            $('#linkHapus').attr('href', 'proses_hapus_artikel.php?id=' + artikelId);
            
            // Tampilkan modal
            var modalHapus = new bootstrap.Modal(document.getElementById('modalHapus'));
            modalHapus.show();
        });
    });
    </script>
</body>
</html>