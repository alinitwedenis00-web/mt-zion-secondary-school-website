<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$limited = login_is_limited();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (login_is_limited()) {
        $error = 'Too many unsuccessful sign-in attempts. Please wait 15 minutes before trying again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $q = $pdo->prepare("SELECT id, username, password_hash, role FROM admins WHERE username=? LIMIT 1");
        $q->execute([$username]);
        $admin = $q->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            login_clear_failures();
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'] ?? 'administrator';
            header('Location: index.php');
            exit;
        }

        login_record_failure();
        $error = login_is_limited()
            ? 'Too many unsuccessful sign-in attempts. Please wait 15 minutes before trying again.'
            : 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login</title>
<link rel="icon" type="image/jpeg" href="../assets/images/school/BADGE.jpg">
<link rel="stylesheet" href="../assets/css/style.css?v=20261120-admin">
</head>
<body class="login-page">
<form method="post" class="login-box admin-login-box" autocomplete="on">
<img class="login-logo" src="../assets/images/school/BADGE.jpg" alt="Mt Zion school logo">
<p class="login-kicker">MT ZION SECONDARY SCHOOL MUBENDE</p>
<h1>Administrator Login</h1>
<p>Sign in to manage the website.</p>
<?php if ($error): ?><div class="error"><?=e($error)?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Username<input name="username" autocomplete="username" required></label>
<label>Password<input type="password" name="password" autocomplete="current-password" required></label>
<button class="btn btn-dark" type="submit">Sign in securely</button>
<a class="login-home" href="../index.php">← Back to school website</a>
</form>
</body>
</html>
