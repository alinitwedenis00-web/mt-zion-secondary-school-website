<?php
require_once 'includes/functions.php';
$pageTitle = 'ABOUT | MT ZION SECONDARY SCHOOL MUBENDE';
require 'includes/header.php';
?>
<section class="page-heading">
    <h1><?= e(setting('about_page_heading', 'About Mt Zion Secondary School')) ?></h1>
    <p><?= e(setting('about_page_intro', 'Our story, mission, and values')) ?></p>
</section>
<section class="section about-page">
    <img class="wide-photo" src="assets/images/school/AERIAL.jpg" alt="Mt Zion Secondary School campus">
    <h2><?= e(setting('about_heading', 'Mt Zion Secondary School – Fostering Excellence Since 2010')) ?></h2>
    <p><?= nl2br(e(setting('about_text', 'Our school is committed to academic achievement, character development and community engagement.'))) ?></p>
    <h2>Our Vision &amp; Mission</h2>
    <div class="vision-grid">
        <div><h3>VISION</h3><p><?= nl2br(e(setting('about_page_vision', setting('vision', 'To be a centre of excellence nurturing responsible and successful citizens.')))) ?></p></div>
        <div><h3>MISSION</h3><p><?= nl2br(e(setting('about_page_mission', setting('mission', 'To empower students with knowledge, skills and values.')))) ?></p></div>
    </div>
    <h3>Motto: <?= e(setting('about_motto', 'Determination with Devotion for Excellence')) ?></h3>
    <h2>Core Values</h2>
    <div class="values-grid">
        <?php
        $values = preg_split('/\r\n|\r|\n/', setting('about_page_values', "Integrity|Honesty, transparency and accountability.\nExcellence|High standards in academics, sports and character.\nCommunity|A supportive environment where every student thrives."));
        foreach ($values as $value):
            $parts = explode('|', $value, 2);
            if (trim($parts[0]) === '') continue;
        ?>
            <article><h4><?= e(trim($parts[0])) ?></h4><p><?= e(trim($parts[1] ?? '')) ?></p></article>
        <?php endforeach; ?>
    </div>
    <h2><?= e(setting('about_beyond_heading', 'Beyond the Classroom')) ?></h2>
    <h3><?= e(setting('about_extracurricular_heading', 'Extra-Curricular Activities')) ?></h3>
    <ul>
        <?php foreach (preg_split('/\r\n|\r|\n/', setting('about_extracurricular_text', "We offer a wide range of sports, artistic and creative activities, and career mentorship to help develop our students' talents and interests. Our teachers have the qualifications and experience to meet high academic expectations.\nEach year, we hold a cultural day featuring music, dance, and drama inter-house competitions. Sports include football, rugby, basketball, volleyball, and more.\nOur dedicated staff, along with alumni groups, work together to give students flexibility in accessing additional programs and courses.")) as $activity): if (trim($activity) !== ''): ?>
            <li><?= e(trim($activity)) ?></li>
        <?php endif; endforeach; ?>
    </ul>
    <div class="about-photos"><img src="assets/images/school/MUSIC.jpg" alt="Music class"><img src="assets/images/school/SPORTS 1.jpg" alt="Sports team"><img src="assets/images/school/VD 1.jpg" alt="Awards"><img src="assets/images/school/AGRIC 1.jpg" alt="Agriculture"></div>
</section>
<?php require 'includes/footer.php'; ?>
