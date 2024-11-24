<?php
include 'config/database.php';
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID artikel dari URL
$artikel_id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : 0;

// Query ambil detail artikel
$query = "SELECT * FROM news WHERE id = '$artikel_id' AND user_id = '".$_SESSION['user_id']."'";
$result = mysqli_query($koneksi, $query);

// Cek apakah artikel ditemukan
if (mysqli_num_rows($result) == 0) {
    $_SESSION['pesan_error'] = "Artikel tidak ditemukan atau Anda tidak memiliki akses.";
    header("Location: profil.php");
    exit();
}

$artikel = mysqli_fetch_assoc($result);

// Proses update artikel
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tangkap data dari form
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $isi = mysqli_real_escape_string($koneksi, $_POST['isi']);

    // Proses upload gambar (opsional)
    $gambar_lama = $artikel['image'];
    $gambar_baru = $gambar_lama;

    // Cek apakah ada file gambar yang diupload
    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "uploads/";
        $filename = uniqid() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $filename;
        
        // Upload file
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $gambar_baru = $target_file;
            
            // Hapus gambar lama jika berbeda
            if ($gambar_lama != $gambar_baru && file_exists($gambar_lama)) {
                unlink($gambar_lama);
            }
        }
    }

    // Query update artikel
    $update_query = "UPDATE news SET 
                        title = '$judul', 
                        kategori = '$kategori', 
                        content = '$isi', 
                        image = '$gambar_baru', 
                        created_at = NOW() 
                     WHERE id = '$artikel_id'";
    
    if (mysqli_query($koneksi, $update_query)) {
        $_SESSION['pesan_sukses'] = "Artikel berhasil diupdate.";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['pesan_error'] = "Gagal mengupdate artikel: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Artikel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tambahkan CKEditor -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Artikel</h2>
        
        <?php if(isset($_SESSION['pesan_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['pesan_error'] ?>
                <?php unset($_SESSION['pesan_error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Judul Artikel</label>
                <input type="text" class="form-control" name="judul" 
                       value="<?= htmlspecialchars($artikel['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="teknologi" <?= $artikel['kategori'] == 'teknologi' ? 'selected' : '' ?>>Teknologi</option>
                    <option value="olahraga" <?= $artikel['kategori'] == 'olahraga' ? 'selected' : '' ?>>Olahraga</option>
                    <option value="politik" <?= $artikel['kategori'] == 'politik' ? 'selected' : '' ?>>Politik</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Isi Artikel</label>
                <textarea name="isi" id="editor" class="form-control" rows="10" required>
                    <?= htmlspecialchars($artikel['content']) ?>
                </textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar Artikel</label>
                <input type="file" name="gambar" class="form-control" accept="image/*">
                
                <?php if(!empty($artikel['gambar'])): ?>
                    <div class="mt-2">
                        <img src="<?= $artikel['gambar'] ?>" style="max-width: 300px;" class="img-thumbnail">
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between">
                <a href="profil.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update Artikel</button>
            </div>
        </form>
    </div>

    <script>
        // Inisialisasi CKEditor
        CKEDITOR.replace('editor');
    </script>
</body>
</html>