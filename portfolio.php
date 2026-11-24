<?php
require_once 'includes/functions.php';

$pageTitle = 'PORTFOLIO | MT ZION SECONDARY SCHOOL MUBENDE';
$graduates = $pdo->query('SELECT * FROM portfolio ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
$historyText = setting(
    'portfolio_history_text',
    'Mt Zion Secondary School Mubende started in 2010 as a community-based school offering affordable education to the people of Kalonga and surrounding communities.\n'
    . 'The school is committed to academic achievement, science education, discipline and talent development.\n'
    . 'Operating under the motto “Determination with Devotion for Excellence,” it fosters academic, musical and sporting talents.'
);

require 'includes/header.php';
?>
<section class="page-heading">
    <h1><?= e(setting('portfolio_heading', 'Our Portfolio')) ?></h1>
    <p><?= e(setting('portfolio_intro', 'Celebrating the achievements and journeys of our graduates.')) ?></p>
</section>
<section class="section">
    <h2><?= e(setting('portfolio_history_heading', 'Brief History about Mt Zion')) ?></h2>
    <h3>Since 2010</h3>
    <div class="portfolio-history-text"><?= nl2br(e($historyText)) ?></div>
    <div class="history-images">
        <img src="assets/images/school/SPORTS 1.jpg" alt="Sports">
        <img src="assets/images/school/SOAP MAKING 1.jpg" alt="Soap making">
        <img src="assets/images/school/S4z.jpg" alt="S4 students">
        <img src="assets/images/school/S6 3.png" alt="S6 students">
    </div>
    <h2>Our Graduates</h2>
    <div class="graduate-grid">
        <?php foreach ($graduates as $graduate): ?>
            <article>
                <?php if (!empty($graduate['image'])): ?>
                    <img src="<?= e($graduate['image']) ?>" alt="<?= e($graduate['title']) ?>">
                <?php endif; ?>
                <span><?= e($graduate['title']) ?></span>
                <?php if (!empty($graduate['description'])): ?>
                    <p class="graduate-description"><?= nl2br(e($graduate['description'])) ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
