<?php
require_once 'includes/functions.php';
$pageTitle = 'ACADEMICS | MT ZION SECONDARY SCHOOL MUBENDE';
$academicServices = $pdo->query('SELECT * FROM services ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
$olevelCompulsory = preg_split('/\r\n|\r|\n/', setting('academics_olevel_compulsory', 'Biology\nChemistry\nEnglish\nGeography\nHistory\nMathematics\nPhysics'));
$olevelOptional = preg_split('/\r\n|\r|\n/', setting('academics_olevel_optional', 'Agriculture\nChinese\nChristian Religious Education\nEntrepreneurship\nFrench\nICT\nIPS\nKiswahili\nLiterature\nLugbarati\nPerforming Arts\nTechnical Drawing'));
$alevelPrincipal = preg_split('/\r\n|\r|\n/', setting('academics_alevel_principal', 'Biology\nChemistry\nEnglish\nGeography\nHistory\nMathematics\nPhysics\nAgriculture\nDivinity\nEntrepreneurship\nIPS\nKiswahili\nLiterature\nLuganda\nEconomics'));
$alevelSubsidiaries = preg_split('/\r\n|\r|\n/', setting('academics_alevel_subsidiaries', 'General Paper\nSubsidiary ICT\nSubsidiary Mathematics'));
require 'includes/header.php';
?>
<section class="page-heading"><h1><?= e(setting('academics_heading', 'Our Services (Academics)')) ?></h1><p><?= e(setting('academics_intro', 'Explore our academic programmes.')) ?></p></section>
<section class="section">
    <img class="wide-photo" src="assets/images/school/O BRIEFING.jpg" alt="Students studying">
    <?php if ($academicServices): ?>
        <h2>Academic Programmes and Services</h2>
        <div class="cards">
            <?php foreach ($academicServices as $service): ?>
<article class="service-card">
<h3>
<?= e($service['title']) ?>
</h3>
<p>
<?= nl2br(e($service['description'])) ?>
</p>
</article>
<?php endforeach; ?>
        </div>
    <?php endif; ?>
    <h2>Subjects Offered</h2>
    <div class="subject-grid">
        <div>
            <h3><?= e(setting('academics_olevel_heading', 'O-Level')) ?></h3>
            <h4>Compulsory</h4><ul><?php foreach ($olevelCompulsory as $subject): if (trim($subject) !== ''): ?><li><?= e(trim($subject)) ?></li><?php endif; endforeach; ?></ul>
            <h4>Optional</h4><ul><?php foreach ($olevelOptional as $subject): if (trim($subject) !== ''): ?><li><?= e(trim($subject)) ?></li><?php endif; endforeach; ?></ul>
        </div>
        <div>
            <h3><?= e(setting('academics_alevel_heading', 'A-Level')) ?></h3>
            <h4>Principal Subjects</h4><ul><?php foreach ($alevelPrincipal as $subject): if (trim($subject) !== ''): ?><li><?= e(trim($subject)) ?></li><?php endif; endforeach; ?></ul>
            <h4>Subsidiaries</h4><ul><?php foreach ($alevelSubsidiaries as $subject): if (trim($subject) !== ''): ?><li><?= e(trim($subject)) ?></li><?php endif; endforeach; ?></ul>
        </div>
    </div>
    <h2><?= e(setting('academics_exam_heading', 'Exam Records (2025)')) ?></h2>
    <div class="grade-grid" id="olevel">
<h3>
<?= e(setting('academics_olevel_heading', 'O-Level')) ?> Grade Distribution</h3>
<?php foreach (preg_split('/\r\n|\r|\n/', setting('academics_olevel_grades', "A|98\nB|95\nC|76\nD|89\nE|15")) as $row): $parts = explode('|', $row, 2); if (count($parts) === 2): ?>
<div>
<strong>
<?= e(trim($parts[1])) ?>
</strong>
<span>
<?= e(trim($parts[0])) ?>
</span>
</div>
<?php endif; endforeach; ?>
</div>
    <div class="grade-grid" id="alevel">
<h3>
<?= e(setting('academics_alevel_heading', 'A-Level')) ?> Grade Distribution</h3>
<?php foreach (preg_split('/\r\n|\r|\n/', setting('academics_alevel_grades', "A|26\nB|78\nC|45\nD|23\nE|11")) as $row): $parts = explode('|', $row, 2); if (count($parts) === 2): ?>
<div>
<strong>
<?= e(trim($parts[1])) ?>
</strong>
<span>
<?= e(trim($parts[0])) ?>
</span>
</div>
<?php endif; endforeach; ?>
</div>
    <img class="wide-photo lower-photo" src="assets/images/school/S6 3.png" alt="Students studying">
</section>
<?php require 'includes/footer.php'; ?>
