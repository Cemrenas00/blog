<?php
require_once __DIR__ . '/config/db.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                        FROM posts p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.slug = ? AND p.status = 'published'");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

$relatedStmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                               FROM posts p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.id != ? AND p.status = 'published' 
                               ORDER BY p.created_at DESC LIMIT 3");
$relatedStmt->execute([$post['id']]);
$relatedPosts = $relatedStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= mb_substr(strip_tags($post['content']), 0, 160) ?>">
    <title><?= htmlspecialchars($post['title']) ?> | MyBlog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <header class="header">
        <div class="container">
            <a href="index.php" class="logo">MyBlog</a>
            <nav class="nav">
                <a href="index.php">Ana Sayfa</a>
            </nav>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Yazı ara...">
            </div>
        </div>
    </header>

    <article class="single-post">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Tüm Yazılara Dön
        </a>

        <div class="post-header">
            <div class="post-card-meta" style="margin-bottom: 20px;">
                <?php if ($post['category_name']): ?>
                    <a href="category.php?slug=<?= htmlspecialchars($post['category_slug']) ?>" class="post-category-badge">
                        <?= htmlspecialchars($post['category_name']) ?>
                    </a>
                <?php endif; ?>
            </div>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <span><i class="far fa-calendar-alt"></i> <?= date('d F Y', strtotime($post['created_at'])) ?></span>
                <span id="readingTime"><i class="far fa-clock"></i></span>
            </div>
        </div>

        <?php if ($post['image']): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
                class="post-image">
        <?php endif; ?>

        <div class="post-content">
            <?= $post['content'] ?>
        </div>
    </article>

    <?php if (!empty($relatedPosts)): ?>
        <section class="posts-section">
            <div class="container">
                <h2 class="section-title">İlgili Yazılar</h2>
                <div class="posts-grid">
                    <?php foreach ($relatedPosts as $rp): ?>
                        <a href="post.php?slug=<?= htmlspecialchars($rp['slug']) ?>" class="post-card">
                            <div class="post-card-image">
                                <?php if ($rp['image']): ?>
                                    <img src="<?= htmlspecialchars($rp['image']) ?>" alt="<?= htmlspecialchars($rp['title']) ?>">
                                <?php else: ?>
                                    <div class="post-card-placeholder">
                                        <i class="fas fa-pen-fancy"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="post-card-body">
                                <div class="post-card-meta">
                                    <?php if ($rp['category_name']): ?>
                                        <span class="post-category-badge"><?= htmlspecialchars($rp['category_name']) ?></span>
                                    <?php endif; ?>
                                    <span class="post-date"><?= date('d M Y', strtotime($rp['created_at'])) ?></span>
                                </div>
                                <h3><?= htmlspecialchars($rp['title']) ?></h3>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <footer class="footer">
        <div class="container">
            <span class="logo">MyBlog</span>
            <p>&copy; <?= date('Y') ?> MyBlog. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>