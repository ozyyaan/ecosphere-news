<?php
session_start();
require_once 'config/database.php';

// Database connection error handling
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}

// Pagination setup
$items_per_page = 9;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Prepared statement for news retrieval with pagination
$query = "SELECT news.*, users.username 
          FROM news 
          JOIN users ON news.user_id = users.id 
          WHERE news.status = 'approved' 
          ORDER BY news.created_at DESC
          LIMIT ? OFFSET ?";

try {
    // Get total records for pagination
    $total_records_query = "SELECT COUNT(*) as count FROM news WHERE status = 'approved'";
    $total_result = mysqli_query($koneksi, $total_records_query);
    $total_records = mysqli_fetch_assoc($total_result)['count'];
    $total_pages = ceil($total_records / $items_per_page);

    // Prepare and execute the main query
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $items_per_page, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the news.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Beranda Berita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .add-news-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .add-news-btn:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }

        .news-card {
            height: 100%;
            transition: transform 0.2s;
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .news-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-newspaper me-2"></i>Berita Online
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i>
                                Hai, <?= htmlspecialchars($_SESSION['username']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil Saya</a></li>
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/manage_user.php"><i class="fas fa-users-cog me-2"></i>Kelola Pengguna</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="nav-item">
                            <a class="nav-link btn btn-outline-primary me-2" href="auth/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link btn btn-primary" href="auth/register.php">
                                <i class="fas fa-user-plus me-1"></i>Daftar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <h1 class="mb-4">
            <i class="fas fa-newspaper me-2"></i>Berita Terbaru
        </h1>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="row g-4">
                <?php while ($artikel = mysqli_fetch_assoc($result)): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card news-card">
                            <?php if (!empty($artikel['image'])): ?>
                                <img src="news/<?= htmlspecialchars($artikel['image']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($artikel['title']) ?>"
                                     onerror="this.src='path/to/fallback-image.jpg'">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($artikel['title']) ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($artikel['username']) ?>
                                </h6>
                                <p class="card-text">
                                    <?= htmlspecialchars(substr(strip_tags($artikel['content']), 0, 150)) ?>...
                                </p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?= date('d M Y H:i', strtotime($artikel['created_at'])) ?>
                                    </small>
                                    <a href="./news/news_detail.php?id=<?= $artikel['id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        Baca Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= ($page - 1) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= ($page + 1) ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>Belum ada berita yang dipublikasikan.
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="./news/create_news.php" class="add-news-btn" title="Tambah Berita">
            <i class="fas fa-plus"></i>
        </a>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>