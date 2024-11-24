<?php
// Include konfigurasi database
include '../config/database.php';
session_start();

// Ambil ID artikel dari URL
$news_id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : 0;

// Query ambil detail artikel lengkap
$query = "SELECT n.*, u.username AS penulis, u.email AS email_penulis 
          FROM news n
          JOIN users u ON n.user_id = u.id 
          WHERE n.id = '$news_id'";

$result = mysqli_query($koneksi, $query);

// Cek apakah artikel ditemukan
if (mysqli_num_rows($result) == 0) {
    $_SESSION['pesan_error'] = "Artikel tidak ditemukan.";
    header("Location: ../index.php");
    exit();
}

$artikel = mysqli_fetch_assoc($result);

// Proses komentar (opsional)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar']);
    $user_id = $_SESSION['user_id'];

    $insert_komentar = "INSERT INTO komentar (artikel_id, user_id, isi_komentar, created_at) 
                        VALUES ('$news_id', '$user_id', '$komentar', NOW())";
    mysqli_query($koneksi, $insert_komentar);
}

// Ambil komentar
$query_komentar = "SELECT k.*, u.username AS nama 
                   FROM komentar k 
                   JOIN users u ON k.user_id = u.id 
                   WHERE k.artikel_id = '$news_id' 
                   ORDER BY k.created_at DESC";
$result_komentar = mysqli_query($koneksi, $query_komentar);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artikel['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <article>
                    <h1 class="mb-4"><?= htmlspecialchars($artikel['title']) ?></h1>
                    
                    <!-- Informasi Penulis -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>Oleh: <?= htmlspecialchars($artikel['penulis']) ?></strong>
                            <br>
                            <small class="text-muted">
                                Dipublikasikan: <?= date('d M Y H:i', strtotime($artikel['created_at'])) ?>
                                | Kategori: <?= ucfirst(htmlspecialchars($artikel['kategori'])) ?>
                            </small>
                        </div>
                        
                        <!-- Tombol Share Sosial Media -->
                        <div class="social-share">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank" class="btn btn-primary btn-sm">
                                <i class="fab fa-facebook"></i> Share
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($artikel['title']) ?>&url=<?= urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank" class="btn btn-info btn-sm">
                                <i class="fab fa-twitter"></i> Tweet
                            </a>
                        </div>
                    </div>

                    <!-- Gambar Artikel -->
                    <?php if (!empty($artikel['image'])): ?>
                        <div class="text-center mb-4">
                            <img src="<?= htmlspecialchars($artikel['image']) ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?= htmlspecialchars($artikel['title']) ?>">
                        </div>
                    <?php endif; ?>

                    <!-- Isi Artikel -->
                    <div class="artikel-content">
    <?= nl2br(strip_tags($artikel['content'])) ?>
</div>

                    <!-- Section Komentar -->
                    <div class="komentar-section mt-5">
                        <h3>Komentar (<?= mysqli_num_rows($result_komentar) ?>)</h3>

                        <!-- Form Komentar -->
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form method="POST" class="mb-4">
                                <div class="form-group">
                                    <textarea name="komentar" class="form-control" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">Kirim Komentar</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Silakan <a href="../login.php">login</a> untuk menulis komentar.
                            </div>
                        <?php endif; ?>

                        <!-- Daftar Komentar -->
                        <div class="list-komentar">
                            <?php while($komentar = mysqli_fetch_assoc($result_komentar)): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?= htmlspecialchars($komentar['nama']) ?>
                                            <small class="text-muted float-end">
                                                <?= date('d M Y H:i', strtotime($komentar['created_at'])) ?>
                                            </small>
                                        </h5>
                                        <p class="card-text"><?= htmlspecialchars($komentar['isi_komentar']) ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Artikel Terkait -->
                    <div class="artikel-terkait mt-5">
                        <h3>Artikel Terkait</h3>
                        <div class="row">
                            <?php
                            $kategori = $artikel['kategori'];
                            $artikel_terkait_query = "SELECT * FROM news 
                                                      WHERE kategori = '$kategori' 
                                                      AND id != '{$artikel['id']}' 
                                                      LIMIT 3";
                            $artikel_terkait_result = mysqli_query($koneksi, $artikel_terkait_query);

                            while ($artikel_terkait = mysqli_fetch_assoc($artikel_terkait_result)) :
                            ?>
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <?php if (!empty($artikel_terkait['image'])) : ?>
                                            <img src="<?= htmlspecialchars($artikel_terkait['image']) ?>" 
                                                 class="card-img-top" 
                                                 alt="<?= htmlspecialchars($artikel_terkait['title']) ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="news_detail.php?id=<?= $artikel_terkait['id'] ?>">
                                                    <?= htmlspecialchars($artikel_terkait['title']) ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">&copy; 2023 Portal Berita. All rights reserved.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
