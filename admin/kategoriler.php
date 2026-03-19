<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    header('Location: giris.php');
    exit;
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count 
                            FROM categories c ORDER BY c.name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $error = 'Kategori adı zorunludur.';
        } else {
            $slug = mb_strtolower($name);
            $slug = preg_replace('/[^a-z0-9\-ğüşöçıİĞÜŞÖÇ]/u', '-', $slug);
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            $slug = str_replace(
                ['ğ', 'ü', 'ş', 'ö', 'ç', 'ı', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'],
                ['g', 'u', 's', 'o', 'c', 'i', 'i', 'g', 'u', 's', 'o', 'c'],
                $slug
            );

            $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetchColumn() > 0) {
                $slug .= '-' . time();
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            header('Location: kategoriler.php?eklendi=1');
            exit;
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE posts SET category_id = NULL WHERE category_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            header('Location: kategoriler.php?silindi=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoriler | Admin Panel</title>
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
                        <a class="nav-link" href="ekle.php"><i class="fas fa-plus-circle"></i> Yeni Yazı</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kategoriler.php"><i class="fas fa-folder"></i> Kategoriler</a>
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
        <h2 class="mb-4"><i class="fas fa-folder"></i> Kategoriler</h2>

        <?php if (isset($_GET['eklendi'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Kategori başarıyla eklendi.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['silindi'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Kategori başarıyla silindi.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Yeni Kategori</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Kategori Adı</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Kategori adını girin..." required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i> Ekle
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Mevcut Kategoriler (
                            <?= count($categories) ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ad</th>
                                    <th>Slug</th>
                                    <th>Yazı Sayısı</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Henüz kategori yok.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $i => $cat): ?>
                                        <tr>
                                            <td>
                                                <?= $i + 1 ?>
                                            </td>
                                            <td class="fw-semibold">
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </td>
                                            <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                                            <td><span class="badge bg-secondary">
                                                    <?= $cat['post_count'] ?>
                                                </span></td>
                                            <td>
                                                <form method="POST" style="display:inline;"
                                                    onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Sil
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>