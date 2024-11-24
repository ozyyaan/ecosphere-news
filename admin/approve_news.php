<?php
session_start();
include '../config/database.php';

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle approval/reject process
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $news_id = $_POST['news_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        $query = "UPDATE news SET status = 'approved' WHERE id = ?";
    } elseif ($action == 'reject') {
        $query = "UPDATE news SET status = 'rejected' WHERE id = ?";
    }

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $news_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = ($action == 'approve') ? "Berita berhasil disetujui." : "Berita berhasil ditolak.";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan dalam memproses permintaan.";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Query pending news
$query = "SELECT news.*, users.username 
          FROM news 
          JOIN users ON news.user_id = users.id 
          WHERE news.status = 'pending' 
          ORDER BY news.created_at DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Berita</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .approval-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .news-item {
            display: flex;
            border-bottom: 1px solid #ddd;
            padding: 20px 0;
            gap: 20px;
        }
        .news-image-container {
            width: 250px;
            flex-shrink: 0;
        }
        .news-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .news-content {
            flex-grow: 1;
        }
        .news-title {
            margin: 0 0 10px 0;
            color: #333;
        }
        .news-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .news-excerpt {
            color: #444;
            line-height: 1.6;
        }
        .news-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-approve:hover {
            background-color: #218838;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        .btn-reject:hover {
            background-color: #c82333;
        }
        .no-pending {
            text-align: center;
            color: #666;
            padding: 40px 0;
            font-size: 1.1em;
        }
        .image-placeholder {
            width: 100%;
            height: 180px;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 0.9em;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="approval-container">
        <h1>Approval Berita Pending</h1>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($news = mysqli_fetch_assoc($result)): ?>
                <div class="news-item">
                    <div class="news-image-container">
                        <?php if(!empty($news['image_path']) && file_exists("../news/" . $news['image_path'])): ?>
                            <img 
                                src="../news/ <?= htmlspecialchars($news['image_path']) ?>" 
                                alt="Gambar Berita" 
                                class="news-image"
                                onerror="this.onerror=null; this.src='../assets/images/no-image.jpg';"
                            >
                        <?php else: ?>
                            <div class="image-placeholder">
                                Tidak ada gambar
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="news-content">
                        <h2 class="news-title"><?= htmlspecialchars($news['title']) ?></h2>
                        <p class="news-meta">
                            Ditulis oleh <strong><?= htmlspecialchars($news['username']) ?></strong><br>
                            pada <?= date('d F Y H:i', strtotime($news['created_at'])) ?>
                        </p>
                        <p class="news-excerpt">
                            <?= 
                                substr(htmlspecialchars($news['content']), 0, 300) . 
                                (strlen($news['content']) > 300 ? '...' : '') 
                            ?>
                        </p>

                        <div class="news-actions">
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                <button 
                                    type="submit" 
                                    name="action" 
                                    value="approve" 
                                    class="btn btn-approve"
                                    onclick="return confirm('Apakah Anda yakin ingin menyetujui berita ini?');"
                                >
                                    Approve
                                </button>
                            </form>

                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                <button 
                                    type="submit" 
                                    name="action" 
                                    value="reject" 
                                    class="btn btn-reject"
                                    onclick="return confirm('Apakah Anda yakin ingin menolak berita ini?');"
                                >
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-pending">Tidak ada berita yang menunggu approval.</p>
        <?php endif; ?>
    </div>
</body>
</html>