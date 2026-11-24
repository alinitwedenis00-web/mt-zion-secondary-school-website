<?php
require_once 'includes/functions.php';
render_page_override('contact-track', 'Contact-Track | MT ZION SECONDARY SCHOOL MUBENDE');
$pageTitle = 'Track Contact Message';
$record = null;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $code = strtoupper(trim($_POST['tracking_code'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $q = $pdo->prepare('SELECT * FROM contact_messages WHERE tracking_code=? AND email=? LIMIT 1');
    $q->execute([$code, $email]);
    $record = $q->fetch();
    if (!$record) $error = 'No message matched that tracking code and email address.';
}
require 'includes/header.php';
?>
<section class="page-heading"><h1>Track Your Message</h1><p>Enter the tracking code shown after submitting your contact form and the same email address.</p></section>
<section class="section"><form method="post" class="form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Tracking code<input name="tracking_code" required></label><label>Email address<input type="email" name="email" required></label><button class="btn">Check message</button></form>
<?php if ($error): ?><div class="success"><?=e($error)?></div><?php endif; ?>
<?php if ($record): ?><article class="article"><h2>Message <?=e($record['tracking_code'])?></h2><p><strong>Status:</strong> <?=e($record['status'])?></p><p><strong>Submitted:</strong> <?=e($record['created_at'])?></p><h3>Your message</h3><p><?=nl2br(e($record['message']))?></p><h3>School response</h3><?php if (!empty($record['reply'])): ?><p><?=nl2br(e($record['reply']))?></p><small>Last updated: <?=e($record['replied_at'] ?? '')?></small><?php else: ?><p>The school has not replied yet. Please check again later.</p><?php endif; ?></article><?php endif; ?></section>
<?php require 'includes/footer.php'; ?>