<?php
require_once '_init.php';

$title = 'Dashboard';
$counts = [];
foreach (['news', 'gallery', 'staff', 'contact_messages', 'admission_enquiries'] as $table) {
    $counts[$table] = (int)$pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
}
require '_layout.php';
?>
<h1>Website Dashboard</h1>
<p>Welcome, <?= e($_SESSION['admin_name']) ?>. Manage the public website using the sections below.</p>
<div class="admin-cards">
    <?php foreach ($counts as $key => $count): ?>
        <div><strong><?= $count ?></strong><span><?= e(ucwords(str_replace('_', ' ', $key))) ?></span></div>
    <?php endforeach; ?>
</div>
<h2>Website content</h2>
<div class="admin-shortcuts">
    <a class="btn btn-dark" href="content.php">Edit all page content</a>
    <a class="btn btn-dark" href="manage.php?type=slides">Manage homepage slider</a>
    <a class="btn btn-dark" href="manage.php?type=services">Manage academics</a>
    <a class="btn btn-dark" href="manage.php?type=portfolio">Manage graduates / portfolio</a>
    <a class="btn btn-dark" href="news.php">Manage news</a>
    <a class="btn btn-dark" href="gallery.php">Manage gallery</a>
</div>
<h2>Submissions</h2>
<div class="admin-shortcuts">
    <a class="btn btn-dark" href="messages.php">Review / reply to contact messages</a>
    <a class="btn btn-dark" href="admissions.php">Review admission applications</a>
    <a class="btn" href="change_credentials.php">Change login credentials</a>
</div>
<?php require '_end.php'; ?>
