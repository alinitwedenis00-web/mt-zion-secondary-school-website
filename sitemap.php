<?php
require_once __DIR__ . '/config/site.php';
header('Content-Type: application/xml; charset=utf-8');
$base = SITE_URL;
$pages = [
    ['/', 'weekly', '1.0'],
    ['/about.php', 'monthly', '0.8'],
    ['/board.php', 'monthly', '0.6'],
    ['/staff.php', 'monthly', '0.7'],
    ['/services.php', 'monthly', '0.8'],
    ['/admissions.php', 'weekly', '0.9'],
    ['/gallery.php', 'weekly', '0.7'],
    ['/portfolio.php', 'monthly', '0.6'],
    ['/news.php', 'weekly', '0.8'],
    ['/contact.php', 'yearly', '0.7'],
];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as [$path, $frequency, $priority]) {
    echo '  <url><loc>' . htmlspecialchars($base . $path, ENT_XML1, 'UTF-8') . '</loc><changefreq>' . $frequency . '</changefreq><priority>' . $priority . '</priority></url>' . "\n";
}
echo "</urlset>\n";
