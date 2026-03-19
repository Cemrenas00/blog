<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    header('Location: giris.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if ($post && $post['image'] && file_exists(__DIR__ . '/../' . $post['image'])) {
        unlink(__DIR__ . '/../' . $post['image']);
    }

    $deleteStmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $deleteStmt->execute([$id]);
}

header('Location: index.php?silindi=1');
exit;
