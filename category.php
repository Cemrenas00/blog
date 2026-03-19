<?php
require_once __DIR__ . '/config/db.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

$catStmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$catStmt->execute([$slug]);
$category = $catStmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit;
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ? AND status = 'published'");
$countStmt->execute([$category['id']]);
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $perPage);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                        FROM posts p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.category_id = ? AND p.status = 'published' 
                        ORDER BY p.created_at DESC 
                        LIMIT ? OFFSET ?");
$stmt->execute([$category['id'], $perPage, $offset]);
$posts = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($category['name']) ?> kategorisindeki yazılar">
    <title><?= htmlspecialchars($category['name']) ?> | MyBlog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <header class="header">
        <div class="container">
            <a href="index.php" class="logo">MyBlog</a>
            <nav class="nav">
                <a href="index.php">Ana Sayfa</a>
                <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
                    <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"
                        class="<?= $cat['id'] === $category['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Yazı ara...">
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1><?= htmlspecialchars($category['name']) ?></h1>
            <p>Bu kategoride toplam <?= $totalPosts ?> yazı bulunmaktadır</p>
        </div>
    </section>

    <section class="posts-section">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($category['name']) ?> Yazıları</h2>

            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <h3>Henüz yazı bulunamadı</h3>
                    <p>Bu kategoride henüz yazı yok.</p>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="post-card">
                            <div class="post-card-image">
                                <?php if ($post['image']): ?>
                                    <img src="<?= htmlspecialchars($post['image']) ?>"
                                        alt="<?= htmlspecialchars($post['title']) ?>">
                                <?php else: ?>
                                    <div class="post-card-placeholder">
                                        <i class="fas fa-pen-fancy"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="post-card-body">
                                <div class="post-card-meta">
                                    <span class="post-category-badge"><?= htmlspecialchars($post['category_name']) ?></span>
                                    <span class="post-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('d M Y', strtotime($post['created_at'])) ?>
                                    </span>
                                </div>
                                <h3><?= htmlspecialchars($post['title']) ?></h3>
                                <p class="post-card-excerpt"><?= mb_substr(strip_tags($post['content']), 0, 150) ?>...</p>
                                <span class="read-more">Devamını Oku <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $page - 1 ?>">
                                <i class="fas fa-chevron-left"></i> Önceki
                            </a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $page + 1 ?>">
                                Sonraki <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <span class="logo">MyBlog</span>
            <p>&copy; <?= date('Y') ?> MyBlog. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>