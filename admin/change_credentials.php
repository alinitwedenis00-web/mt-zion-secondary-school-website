<?php
require_once '_init.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check();

    $username = trim($_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get current administrator
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($current_password, $admin['password_hash'])) {

        $message = "Current password is incorrect.";

    } elseif (strlen($new_password) < 10) {

        $message = "New password must have at least 10 characters.";

    } elseif ($new_password !== $confirm_password) {

        $message = "New passwords do not match.";

    } elseif ($username === '') {

        $message = "Username cannot be empty.";

    } else {

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        try {

            $stmt = $pdo->prepare(
                "UPDATE admins SET username = ?, password_hash = ? WHERE id = ?"
            );

            $stmt->execute([
                $username,
                $new_hash,
                $_SESSION['admin_id']
            ]);

            $_SESSION['admin_name'] = $username;

            $message = "Username and password changed successfully!";

        } catch (PDOException $e) {

            $message = "That username may already be taken.";

        }
    }
}

$title = 'Change Login Credentials';

require '_layout.php';
?>

<h1>Change Admin Credentials</h1>

<p><?= htmlspecialchars($message) ?></p>

<form method="POST" class="form admin-form">

    <input type="hidden" name="csrf"
           value="<?= htmlspecialchars(csrf_token()) ?>">

    <label>New Username</label>
    <input type="text" name="username"
           value="<?= htmlspecialchars($_SESSION['admin_name']) ?>"
           required>

    <label>Current Password</label>
    <input type="password" name="current_password" required>

    <label>New Password</label>
    <input type="password" name="new_password" minlength="10" required>

    <label>Confirm New Password</label>
    <input type="password" name="confirm_password" minlength="10" required>

    <button type="submit" class="btn btn-dark">
        Update Credentials
    </button>

</form>

<?php require '_end.php'; ?>