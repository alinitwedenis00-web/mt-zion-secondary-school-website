<?php
require_once '_init.php';

$title = 'Edit Website Content';
$message = '';

$sections = [
    'School details and SEO' => [
        'school_name' => 'School name', 'phone' => 'Telephone', 'email' => 'Email address',
        'address' => 'Physical address', 'meta_description' => 'SEO meta description',
        'social_text' => 'Social media / contact information',
    ],
    'Home page' => [
        'home_hero_title' => 'Main slider title', 'home_hero_subtitle' => 'Main slider subtitle',
        'home_about_heading' => 'Home page about heading', 'home_about_text' => 'Home page about paragraph',
        'home_about_extra' => 'Additional home page paragraph',
        'home_achievers_uace_heading' => 'UACE achievers section heading',
        'home_achievers_uce_heading' => 'UCE achievers section heading',
        'home_news_heading' => 'Home page news heading',
        'home_gallery_heading' => 'Home page gallery heading',
        'home_stats_heading' => 'Home page statistics heading',
        'home_cta_heading' => 'Home page call-to-action heading',
        'home_cta_text' => 'Home page call-to-action text',
    ],
    'About page' => [
        'about_page_heading' => 'About page heading', 'about_page_intro' => 'About page introduction',
        'about_heading' => 'About section heading', 'about_text' => 'About section content',
        'about_page_vision' => 'Vision', 'about_page_mission' => 'Mission',
        'mission' => 'Mission (shared)', 'vision' => 'Vision (shared)',
        'about_page_values' => 'Core values (one per line: Title|Description)',
        'about_motto' => 'School motto',
        'about_beyond_heading' => 'Beyond the Classroom heading',
        'about_extracurricular_heading' => 'Extra-curricular heading',
        'about_extracurricular_text' => 'Extra-curricular information (one paragraph per line)',
    ],
    'Academics page' => [
        'academics_heading' => 'Academics page heading', 'academics_intro' => 'Academics introduction',
        'academics_olevel_compulsory' => 'O-Level compulsory subjects (one subject per line)',
        'academics_olevel_optional' => 'O-Level optional subjects (one subject per line)',
        'academics_alevel_principal' => 'A-Level principal subjects (one subject per line)',
        'academics_alevel_subsidiaries' => 'A-Level subsidiary subjects (one subject per line)',
        'admissions_text' => 'Admissions information',
        'academics_olevel_heading' => 'O-Level heading',
        'academics_alevel_heading' => 'A-Level heading',
        'academics_exam_heading' => 'Examination records heading',
        'academics_olevel_grades' => 'O-Level grades (one grade|number per line)',
        'academics_alevel_grades' => 'A-Level grades (one grade|number per line)',
    ],
    'Portfolio, gallery and contact pages' => [
        'portfolio_heading' => 'Portfolio heading', 'portfolio_intro' => 'Portfolio introduction',
        'portfolio_history_heading' => 'Portfolio history heading',
        'portfolio_history_text' => 'Portfolio history (one paragraph per line)',
        'admissions_heading' => 'Admissions form page heading',
        'admissions_intro' => 'Admissions page introduction',
        'message_track_heading' => 'Message tracking page heading',
        'gallery_heading' => 'Gallery heading', 'gallery_intro' => 'Gallery introduction',
        'contact_heading' => 'Contact page heading', 'contact_intro' => 'Contact page introduction',
        'footer_text' => 'Footer text',
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $save = $pdo->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) '
        . 'ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    foreach ($sections as $fields) {
        foreach ($fields as $key => $label) {
            $value = trim((string)($_POST[$key] ?? ''));
            $save->execute([$key, $value]);
        }
    }
    $message = 'Website content saved. The public pages read these values directly from the database.';
}

require '_layout.php';
?>
<h1>Edit Website Content</h1>
<p>Changes saved here are displayed on the corresponding public pages. Use the separate Slider, Academics, Portfolio, News and Gallery sections for their records and images.</p>
<?php if ($message): ?><div class="success"><?= e($message) ?></div><?php endif; ?>
<form method="post" class="form admin-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <?php foreach ($sections as $heading => $fields): ?>
        <section class="content-editor-section">
            <h2><?= e($heading) ?></h2>
            <?php foreach ($fields as $key => $label): ?>
                <label>
                    <?= e($label) ?>
                    <?php if (in_array($key, ['meta_description', 'social_text', 'home_about_text', 'home_about_extra', 'about_text', 'about_page_values', 'about_extracurricular_text', 'academics_olevel_compulsory', 'academics_olevel_optional', 'academics_alevel_principal', 'academics_alevel_subsidiaries', 'academics_olevel_grades', 'academics_alevel_grades', 'portfolio_history_text', 'admissions_text', 'about_page_vision', 'about_page_mission'], true)): ?>
                        <textarea name="<?= e($key) ?>" rows="5"><?= e(setting($key)) ?></textarea>
                    <?php else: ?>
                        <input name="<?= e($key) ?>" value="<?= e(setting($key)) ?>">
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
    <button class="btn btn-dark" type="submit">Save all page content</button>
</form>
<?php require '_end.php'; ?>
