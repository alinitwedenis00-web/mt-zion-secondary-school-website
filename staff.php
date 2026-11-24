<?php
require_once 'includes/functions.php';
$pageTitle = 'STAFF | MT ZION SECONDARY SCHOOL MUBENDE';
$members = $pdo->query('SELECT * FROM staff ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
require 'includes/header.php';
?>
<section class="page-heading"><h1>Our Staff</h1><p>Dedicated educators shaping the future of our students.</p></section>
<section class="section">
    <h2 class="center-title">TOP ADMINISTRATION</h2>
    <div class="people-grid staff-grid">
        <?php foreach ($members as $member): ?>
            <article class="person">
                <?php if (!empty($member['image'])): ?><img src="<?= e($member['image']) ?>" alt="<?= e($member['name']) ?>"><?php endif; ?>
                <h4><?= e($member['name']) ?></h4>
                <p><?= e($member['role']) ?></p>
                <?php if (!empty($member['bio'])): ?><p><?= nl2br(e($member['bio'])) ?></p><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
