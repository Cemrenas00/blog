<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    header('Location: giris.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $status = $_POST['status'] ?? 'draft';

    $slug = mb_strtolower($title);
    $slug = preg_replace('/[^a-z0-9\-ğüşöçıİĞÜŞÖÇ]/u', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = str_replace(
        ['ğ', 'ü', 'ş', 'ö', 'ç', 'ı', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'],
        ['g', 'u', 's', 'o', 'c', 'i', 'i', 'g', 'u', 's', 'o', 'c'],
        $slug
    );

    if (empty($title) || empty($content)) {
        $error = 'Başlık ve içerik alanları zorunludur.';
    } else {
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('post_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = 'uploads/' . $filename;
            }
        }

        $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = ?");
        $checkSlug->execute([$slug]);
        if ($checkSlug->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        $stmt = $pdo->prepare("INSERT INTO posts (category_id, title, slug, content, image, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id ?: null, $title, $slug, $content, $imagePath, $status]);

        header('Location: index.php?eklendi=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Yazı Ekle | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-cog"></i> MyBlog Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-file-alt"></i> Yazılar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="ekle.php"><i class="fas fa-plus-circle"></i> Yeni Yazı</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kategoriler.php"><i class="fas fa-folder"></i> Kategoriler</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i>
                            Siteyi Gör</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="cikis.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle"></i> Yeni Yazı Ekle</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Yazılara Dön
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-pen"></i> Yazı Bilgileri</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Başlık</label>
                        <input type="text" class="form-control" id="title" name="title"
                            placeholder="Yazı başlığını girin..." value="<?= htmlspecialchars($title ?? '') ?>"
                            required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="0">Kategori seçin...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($category_id ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Durum</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published">Yayında</option>
                                <option value="draft">Taslak</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">Kapak Görseli</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold">İçerik</label>
                        <textarea class="form-control" id="content" name="content" rows="10"
                            placeholder="Yazı içeriğini girin... HTML etiketleri kullanabilirsiniz."><?= htmlspecialchars($content ?? '') ?></textarea>
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Yazıyı Kaydet
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>