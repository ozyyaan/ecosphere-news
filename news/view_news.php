<?php
session_start();
include '../config/database.php';

// Query untuk mendapatkan berita yang sudah diapprove
$query = "SELECT news.*, users.username 
          FROM news 
          JOIN users ON news.user_id = users.id 
          WHERE news.status = 'approved' 
          ORDER BY news.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Berita</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .news-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .news-item {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
            display: flex;
            align-items: start;
        }
        .news-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 8px;
        }
        .news-content {
            flex-grow: 1;
        }
        .news-title {
            margin-top: 0;
            color: #333;
        }
        .news-meta {
            color: #777;
            font-size: 0.8em;
            margin-bottom: 10px;
        }
        .news-excerpt {
            color: #555;
        }
        .create-news-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="news-container">
        <h1>Berita Terbaru</h1>
        
        <?php 
        // Cek apakah user sudah login
        if(isset($_SESSION['user_id'])): ?>
            <a href="create_news.php" class="create-news-btn">Buat Berita Baru</a>
        <?php endif; ?>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($news = mysqli_fetch_assoc($result)): ?>
                <div class="news-item">
                    <?php if(!empty($news['image_path'])): ?>
                        <img 
                            src="../uploads/<?= htmlspecialchars($news['image_path']) ?>" 
                            alt="Gambar Berita" 
                            class="news-image"
                        >
                    <?php endif; ?>
                    
                    <div class="news-content">
                        <h2 class="news-title"><?= htmlspecialchars($news['title']) ?></h2>
                        <div class="news-meta">
                            Ditulis oleh <?= htmlspecialchars($news['username']) ?> 
                            pada <?= date('d F Y H:i', strtotime($news['created_at'])) ?>
                        </div>
                        <p class="news-excerpt">
                            <?= 
                                // Potong konten menjadi 200 karakter
                                substr(htmlspecialchars($news['content']), 0, 200) . 
                                (strlen($news['content']) > 200 ? '...' : '') 
                            ?>
                        </p>
                        <a href="news_detail.php?id=<?= $news['id'] ?>">Baca Selengkapnya</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Belum ada berita yang tersedia.</p>
        <?php endif; ?>
    </div>
</body>
</html>