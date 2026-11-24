<?php
require_once 'includes/functions.php';
$pageTitle = 'HOME | MT ZION SECONDARY SCHOOL MUBENDE';
$slides = $pdo->query('SELECT * FROM slides WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
$giants = $pdo->query("SELECT * FROM achievers ORDER BY FIELD(category, 'UACE', 'UCE'), sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$news = $pdo->query('SELECT * FROM news WHERE is_published = 1 ORDER BY published_at DESC, id DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
$photos = $pdo->query('SELECT * FROM gallery ORDER BY id LIMIT 4')->fetchAll(PDO::FETCH_ASSOC);
$stats = $pdo->query('SELECT * FROM student_statistics ORDER BY sort_order')->fetchAll(PDO::FETCH_ASSOC);
require 'includes/header.php';
?>
<section class="hero" id="top">
    <div class="hero-slides">
        <?php if ($slides): ?>
            <?php foreach ($slides as $index => $slide): ?>
                <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= e($slide['image'] ?: 'assets/images/school/AERIAL.jpg') ?>" alt="<?= e($slide['title']) ?>">
                    <div class="hero-text">
                        <h1><?= e($slide['title']) ?></h1>
                        <p><?= e($slide['subtitle']) ?></p>
                        <?php if (!empty($slide['button_text'])): ?><a class="btn" href="<?= e($slide['button_link'] ?: 'about.php') ?>"><?= e($slide['button_text']) ?></a><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="hero-slide active">
                <img src="assets/images/school/AERIAL.jpg" alt="Mt Zion Secondary School">
                <div class="hero-text"><h1><?= e(setting('home_hero_title', 'Welcome to Mt Zion Secondary School Mubende')) ?></h1><p><?= e(setting('home_hero_subtitle', 'Inspiring futures since 2010.')) ?></p><a class="btn" href="about.php">Learn More</a></div>
            </div>
        <?php endif; ?>
    </div>
    <?php if (count($slides) > 1): ?>
        <button class="hero-prev" type="button" aria-label="Previous slide">❮</button>
        <button class="hero-next" type="button" aria-label="Next slide">❯</button>
        <div class="hero-dots"><?php foreach ($slides as $index => $slide): ?><button type="button" class="<?= $index === 0 ? 'active' : '' ?>" aria-label="Go to slide <?= $index + 1 ?>"></button><?php endforeach; ?></div>
    <?php endif; ?>
</section>

<section class="about-home section">
    <div class="about-copy">
        <span class="eyebrow">ABOUT MT ZION</span>
        <h2><?= e(setting('home_about_heading', 'Mt Zion Secondary School – Fostering Excellence Since 2010')) ?></h2>
        <p><?= nl2br(e(setting('home_about_text', 'Nestled in the heart of Kalonga, our school is dedicated to cultivating young leaders through academic rigour, character development, and community engagement.'))) ?></p>
        <p><?= nl2br(e(setting('home_about_extra', 'We offer O-Level and A-Level curricula that prepare students for success in national examinations and life beyond the classroom.'))) ?></p>
        <a class="btn" href="about.php">Read More</a>
    </div>
    <div class="about-photos">
        <img src="assets/images/school/S4z.jpg" alt="Form four candidates">
        <img src="assets/images/school/CULTURE PARTY.jpg" alt="Culture day">
        <img src="assets/images/school/SCH CHOIR 3.jpg" alt="School choir">
        <img src="assets/images/school/TEAM PRAYING.jpg" alt="Students together">
    </div>
</section>

<?php foreach (['UACE' => setting('home_achievers_uace_heading', '2025 UACE ACADEMIC GIANTS'), 'UCE' => setting('home_achievers_uce_heading', '2025 UCE ACADEMIC GIANTS')] as $category => $heading): ?>
<section class="section achievers-section">
    <h2><?= e($heading) ?></h2>
    <div class="achiever-grid">
        <?php foreach ($giants as $giant): ?>
            <?php if ($giant['category'] === $category): ?>
                <article class="achiever"><img src="<?= e($giant['image']) ?>" alt="<?= e($giant['name']) ?>"><h3><?= e($giant['name']) ?></h3><p><?= e($giant['achievement']) ?></p></article>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<section class="section announcements">
    <h2><?= e(setting('home_news_heading', 'NEWS AND ANNOUNCEMENTS')) ?></h2>
    <div class="announcement-grid">
        <?php foreach ($news as $item): ?>
            <article><img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>"><div><h3><?= e($item['title']) ?></h3><p><?= e($item['summary']) ?></p><a class="text-link" href="news.php?id=<?= (int)$item['id'] ?>">Read full story</a></div></article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section gallery-highlights">
    <h2><?= e(setting('home_gallery_heading', 'GALLERY HIGHLIGHTS')) ?></h2>
    <div class="highlight-grid">
        <?php foreach ($photos as $photo): ?><a href="gallery.php"><img src="<?= e($photo['image']) ?>" alt="<?= e($photo['caption']) ?>"><span><?= e($photo['caption']) ?></span></a><?php endforeach; ?>
    </div>
    <a class="btn" href="gallery.php">View Full Gallery</a>
</section>

<section class="section statistics">
    <h2><?= e(setting('home_stats_heading', 'STUDENT STATISTICS')) ?></h2>
    <div class="table-wrap"><table><thead><tr><th>Class</th><th>Boys</th><th>Girls</th><th>Total</th></tr></thead><tbody>
    <?php $boys = $girls = $total = 0; foreach ($stats as $stat): $boys += (int)$stat['boys']; $girls += (int)$stat['girls']; $total += (int)$stat['total']; ?>
        <tr><td><?= e($stat['class_name']) ?></td><td><?= (int)$stat['boys'] ?></td><td><?= (int)$stat['girls'] ?></td><td><?= (int)$stat['total'] ?></td></tr>
    <?php endforeach; ?>
        <tr><th>TOTAL</th><th><?= $boys ?></th><th><?= $girls ?></th><th><?= $total ?></th></tr>
    </tbody></table></div>
</section>
<section class="join-cta"><h2><?= e(setting('home_cta_heading', 'Ready to Join Mt Zion?')) ?></h2><p><?= e(setting('home_cta_text', 'Become part of our vibrant community and unlock your potential.')) ?></p><a class="btn" href="admissions.php">Apply Now</a></section>
<?php require 'includes/footer.php'; ?>
