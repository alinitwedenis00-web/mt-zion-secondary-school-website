<?php
require_once 'includes/functions.php';
$pageTitle = 'GALLERY | MT ZION SECONDARY SCHOOL MUBENDE';
$photos = $pdo->query('SELECT * FROM gallery ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
require 'includes/header.php';
?>
<section class="page-heading">
    <h1><?= e(setting('gallery_heading', 'Gallery')) ?></h1>
    <p><?= e(setting('gallery_intro', 'Browse some of our campus photos.')) ?></p>
</section>
<section class="section">
    <div class="filters">
        <button class="selected" data-filter="all">All</button>
        <button data-filter="students">Students</button>
        <button data-filter="agriculture">Agriculture</button>
        <button data-filter="sports">Sports</button>
    </div>
    <div class="gallery-page-grid">
        <?php foreach ($photos as $photo): ?>
            <?php $category = in_array($photo['category'] ?? '', ['students', 'agriculture', 'sports'], true) ? $photo['category'] : 'students'; ?>
            <a class="gallery-item" data-category="<?= e($category) ?>" href="<?= e($photo['image']) ?>">
                <img src="<?= e($photo['image']) ?>" alt="<?= e($photo['caption']) ?>">
                <span><?= e($photo['caption']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<div class="lightbox" id="lightbox"><button type="button" aria-label="Close image">×</button><img src="" alt=""></div>
<?php require 'includes/footer.php'; ?>
