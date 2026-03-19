<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    header('Location: giris.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                        FROM posts p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | MyBlog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #f8f9fa;
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
            <h2><i class="fas fa-file-alt"></i> Tüm Yazılar</h2>
            <a href="ekle.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Yeni Yazı Ekle
            </a>
        </div>

        <?php if (isset($_GET['silindi'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Yazı başarıyla silindi.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['eklendi'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Yazı başarıyla eklendi.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['guncellendi'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Yazı başarıyla güncellendi.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Başlık</th>
                            <th>Kategori</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th width="180">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Henüz yazı bulunmamaktadır.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $i => $post): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($post['title']) ?></td>
                                    <td><?= htmlspecialchars($post['category_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($post['status'] === 'published'): ?>
                                            <span class="badge bg-success">Yayında</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Taslak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                                    <td>
                                        <a href="duzenle.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </a>
                                        <a href="sil.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bu yazıyı silmek istediğinize emin misiniz?')">
                                            <i class="fas fa-trash"></i> Sil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-muted mt-3">Toplam <?= count($posts) ?> yazı listeleniyor.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>