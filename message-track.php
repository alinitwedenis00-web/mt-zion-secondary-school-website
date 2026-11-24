<?php
require_once 'includes/functions.php';
$pageTitle = 'Track a Contact Message | Mt Zion Secondary School';
$record = null;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $reference = strtoupper(trim($_POST['reference'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $query = $pdo->prepare('SELECT reference_code, subject, status, admin_reply, created_at, replied_at FROM contact_messages WHERE reference_code = ? AND email = ? LIMIT 1');
    $query->execute([$reference, $email]);
    $record = $query->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$record) $error = 'No message matched those details. Check your reference number and email address.';
}
require 'includes/header.php';
?>
<section class="page-heading"><h1><?= e(setting('message_track_heading', 'Track Your Message')) ?></h1><p>Use the reference number received after submitting the contact form.</p></section>
<section class="section narrow-section">
    <form method="post" class="form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label>Message reference number<input name="reference" required placeholder="e.g. MZ2611A1B2C3D4"></label>
        <label>Email address used when sending the message<input type="email" name="email" required></label>
        <button class="btn btn-dark" type="submit">Check message progress</button>
    </form>
    <?php if ($error): ?><div class="notice"><?= e($error) ?></div><?php endif; ?>
    <?php if ($record): ?>
        <article class="admin-form">
            <h2>Message status: <?= e($record['status']) ?></h2>
            <p><strong>Reference:</strong> <?= e($record['reference_code']) ?></p>
            <p><strong>Subject:</strong> <?= e($record['subject'] ?: 'General enquiry') ?></p>
            <p><strong>Submitted:</strong> <?= e($record['created_at']) ?></p>
            <?php if ($record['admin_reply']): ?><h3>Reply from the school</h3><p><?= nl2br(e($record['admin_reply'])) ?></p><p><small>Reply date: <?= e($record['replied_at'] ?? 'Not recorded') ?></small></p><?php else: ?><p>The school has received your message. A reply will appear here when one is recorded.</p><?php endif; ?>
        </article>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
