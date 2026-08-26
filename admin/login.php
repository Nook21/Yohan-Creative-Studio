<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict']);
}
require_once __DIR__ . '/../includes/functions.php';

// Already logged in
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $st = db()->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $user['id'];
            $_SESSION['admin_name']     = $user['name'];
            $_SESSION['last_activity']  = time();
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        }
    }
    $error = 'Invalid email or password.';
}

$timeout = isset($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login — Amanuel Yohannes</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  body { background: #0f0f0f; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', system-ui, sans-serif; }
  .login-card { background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 12px; padding: 2.5rem; width: 100%; max-width: 400px; }
  .login-logo { font-size: 1.1rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: #fff; margin-bottom: 0.25rem; }
  .login-sub  { font-size: 0.78rem; color: #666; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 2rem; }
  .form-control { background: #111; border: 1px solid #333; color: #fff; border-radius: 8px; padding: 0.75rem 1rem; }
  .form-control:focus { background: #111; border-color: #555; color: #fff; box-shadow: none; }
  .form-label { color: #aaa; font-size: 0.82rem; letter-spacing: 0.05em; text-transform: uppercase; }
  .btn-login { background: #fff; color: #000; border: none; border-radius: 8px; padding: 0.75rem; font-weight: 600; letter-spacing: 0.05em; width: 100%; transition: background 0.2s; }
  .btn-login:hover { background: #e0e0e0; }
  .alert-danger { background: #2a1010; border-color: #5a2020; color: #f88; border-radius: 8px; font-size: 0.88rem; }
  .alert-warning { background: #2a2010; border-color: #5a4020; color: #fc8; border-radius: 8px; font-size: 0.88rem; }
</style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">Amanuel Yohannes</div>
    <div class="login-sub">Portfolio Admin</div>

    <?php if ($timeout): ?>
    <div class="alert alert-warning mb-3"><i class="bi bi-clock me-2"></i>Session expired. Please log in again.</div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-circle me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn-login">Sign In <i class="bi bi-arrow-right ms-1"></i></button>
    </form>
</div>
</body>
</html>
