<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Geçersiz kullanıcı adı veya şifre.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi | MyBlog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(25, 25, 50, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            max-width: 420px;
            width: 100%;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e8e8f0;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            color: #e8e8f0;
            border-color: #6c63ff;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.25);
        }

        .form-label {
            color: #9999b0;
        }

        .btn-primary {
            background: #6c63ff;
            border: none;
        }

        .btn-primary:hover {
            background: #8b83ff;
        }
    </style>
</head>

<body>
    <div class="login-card p-5 shadow-lg">
        <div class="text-center mb-4">
            <div style="font-size:3rem;">🔐</div>
            <h2 class="text-white mt-2">Admin Paneli</h2>
            <p class="text-secondary">Devam etmek için giriş yapın</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label"><i class="fas fa-user"></i> Kullanıcı Adı</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="admin"
                    value="<?= htmlspecialchars($username ?? '') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fas fa-lock"></i> Şifre</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••"
                    required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>

        <p class="text-center mt-4 mb-0">
            <a href="../index.php" class="text-secondary text-decoration-none">
                <i class="fas fa-arrow-left"></i> Siteye Dön
            </a>
        </p>
    </div>
</body>

</html>