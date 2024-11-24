<?php
session_start();
include '../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);
    $kategori = isset($_POST['kategori']) ? mysqli_real_escape_string($koneksi, $_POST['kategori']) : ''; // Check if kategori is set
    $user_id = $_SESSION['user_id'];

    // Validasi input
    if (empty($title) || empty($content)) {
        $error = 'Judul dan konten harus diisi';
    } elseif (empty($kategori)) {
        $error = 'Kategori harus dipilih';
    } else {
        // Proses upload gambar
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/news/";

            // Buat direktori jika belum ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Validasi tipe file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                $error = 'Hanya file gambar (JPG, PNG, GIF) yang diizinkan.';
            } else {
                // Validasi ukuran file (max 5MB)
                $max_file_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['image']['size'] > $max_file_size) {
                    $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
                } else {
                    // Generate nama file unik
                    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;

                    // Upload file
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file;
                    } else {
                        $error = 'Gagal mengupload gambar.';
                    }
                }
            }
        }

        // Jika tidak ada error, simpan berita
        if (empty($error)) {
            // Periksa apakah user_id valid
            $user_check_query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
            $user_check_result = mysqli_query($koneksi, $user_check_query);

            if (mysqli_num_rows($user_check_result) == 0) {
                $error = 'User tidak valid';
            } else {
                // Simpan berita dengan status pending dan path gambar
                $query = "INSERT INTO news (user_id, title, content, image, status, kategori, created_at) 
                          VALUES ($user_id, '$title', '$content', " . 
                          ($image_path ? "'$image_path'" : "NULL") . 
                          ", 'pending', '$kategori', NOW())";
                
                if (mysqli_query($koneksi, $query)) {
                    // Redirect ke halaman index dengan pesan sukses
                    $_SESSION['success_message'] = 'Berita berhasil disimpan dan menunggu persetujuan admin';
                    header('Location: ../index.php');
                    exit();
                } else {
                    $error = 'Gagal menyimpan berita: ' . mysqli_error($koneksi);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Berita Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
        }
        #image-preview {
            max-width: 100%;
            max-height: 300px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Buat Berita Baru</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Berita</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="title" 
                            name="title" 
                            placeholder="Masukkan judul berita"
                            required
                            value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Gambar Berita (opsional)</label>
                        <input 
                            type="file" 
                            class="form-control" 
                            id="image" 
                            name="image" 
                            accept="image/jpeg,image/png,image/gif"
                        >
                        <img id="image-preview" src="#" alt="Preview Gambar">
                    </div>
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori Berita</label>
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Technology" <?= isset($_POST['kategori']) && $_POST['kategori'] == 'Technology' ? 'selected' : '' ?>>Technology</option>
                            <option value="Health" <?= isset($_POST['kategori']) && $_POST['kategori'] == 'Health' ? 'selected' : '' ?>>Health</option>
                            <option value="Politics" <?= isset($_POST['kategori']) && $_POST['kategori'] == 'Politics' ? 'selected' : '' ?>>Politics</option>
                            <option value="Sports" <?= isset($_POST['kategori']) && $_POST['kategori'] == 'Sports' ? 'selected' : '' ?>>Sports</option>
                            <option value="Entertainment" <?= isset($_POST['kategori']) && $_POST['kategori'] == 'Entertainment' ? 'selected' : '' ?>>Entertainment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Konten Berita</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-control" 
                            required
                        ><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="../index.php" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Berita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            // Summernote
            $('#content').summernote({
                placeholder: 'Tulis konten berita di sini',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                ]
            });

            // Image preview
            $('#image').change(function(e) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(this.files[0]);
            });
        });
    </script>
</body>
</html>
