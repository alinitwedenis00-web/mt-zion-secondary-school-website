<?php
// Central application helpers and safer session defaults.
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
require_once __DIR__ . '/../config/database.php';

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function setting(string $key, string $default=''): string {
    global $pdo;
    $q = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1");
    $q->execute([$key]);
    $v = $q->fetchColumn();
    return $v === false || trim((string)$v) === '' ? $default : (string)$v;
}
function active_page(string $page): string {
    return basename($_SERVER['PHP_SELF'] ?? '') === $page ? 'active' : '';
}
function admin_required(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Invalid form token. Refresh the page and try again.');
    }
}
function upload_image(string $field): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    if (!isset($_FILES[$field]['error']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    if (!is_uploaded_file($_FILES[$field]['tmp_name'])) {
        throw new RuntimeException('Invalid uploaded file.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$field]['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp', 'image/gif'=>'gif'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
    if ((int)$_FILES[$field]['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be 5MB or smaller.');
    }
    $directory = __DIR__ . '/../uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Upload directory is not available.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('Could not save image.');
    }
    return 'uploads/' . $name;
}
function login_ip_hash(): string {
    return hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}
function login_is_limited(): bool {
    global $pdo;
    $q = $pdo->prepare("SELECT COUNT(*) FROM admin_login_attempts WHERE ip_hash=? AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)");
    $q->execute([login_ip_hash()]);
    return (int)$q->fetchColumn() >= 5;
}
function login_record_failure(): void {
    global $pdo;
    $q = $pdo->prepare("INSERT INTO admin_login_attempts(ip_hash, attempted_at) VALUES(?, NOW())");
    $q->execute([login_ip_hash()]);
}
function login_clear_failures(): void {
    global $pdo;
    $q = $pdo->prepare("DELETE FROM admin_login_attempts WHERE ip_hash=?");
    $q->execute([login_ip_hash()]);
}

require_once __DIR__ . '/admission_notifications.php';
