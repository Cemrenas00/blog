<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    header('Location: giris.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $status = $_POST['status'] ?? 'draft';

    if (empty($title) || empty($content)) {
        $error = 'Başlık ve içerik alanları zorunludur.';
    } else {
        $imagePath = $post['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('post_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                if ($post['image'] && file_exists(__DIR__ . '/../' . $post['image'])) {
                    unlink(__DIR__ . '/../' . $post['image']);
                }
                $imagePath = 'uploads/' . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, category_id = ?, image = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $content, $category_id ?: null, $imagePath, $status, $id]);

        header('Location: index.php?guncellendi=1');
        exit;
    }
} else {
    $title = $post['title'];
    $content = $post['content'];
    $category_id = $post['category_id'];
    $status = $post['status'];
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yazı Düzenle | Admin Panel</title>
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
                        <a class="nav-link active" href="index.php"><i class="fas fa-file-alt"></i> Yazılar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ekle.php"><i class="fas fa-plus-circle"></i> Yeni Yazı</a>
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
            <h2><i class="fas fa-edit"></i> Yazı Düzenle</h2>
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
                <h5 class="mb-0"><i class="fas fa-edit"></i> "
                    <?= htmlspecialchars($post['title']) ?>" yazısını düzenliyorsunuz
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Başlık</label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="<?= htmlspecialchars($title) ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="0">Kategori seçin...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Durum</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Yayında</option>
                                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Taslak</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">
                            Kapak Görseli
                            <?= $post['image'] ? '<span class="text-success">(Mevcut görsel var)</span>' : '' ?>
                        </label>
                        <?php if ($post['image']): ?>
                            <p class="text-muted small mb-1">Mevcut:
                                <?= htmlspecialchars($post['image']) ?>
                            </p>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold">İçerik</label>
                        <textarea class="form-control" id="content" name="content"
                            rows="10"><?= htmlspecialchars($content) ?></textarea>
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Değişiklikleri Kaydet
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