<?php
/**
 * XML Sitemap
 * Mt Zion Secondary School, Mubende
 */

// Prevent PHP warnings from appearing inside the XML output.
ini_set('display_errors', '0');
error_reporting(0);

// Set XML content type.
header('Content-Type: application/xml; charset=UTF-8');

// Website domain.
$domain = 'https://mtzionssmubende.noscamcoin.org';

// Public website pages.
$pages = [
    [
        'url' => '/',
        'changefreq' => 'weekly',
        'priority' => '1.0'
    ],
    [
        'url' => '/about.php',
        'changefreq' => 'monthly',
        'priority' => '0.8'
    ],
    [
        'url' => '/board.php',
        'changefreq' => 'monthly',
        'priority' => '0.6'
    ],
    [
        'url' => '/staff.php',
        'changefreq' => 'monthly',
        'priority' => '0.7'
    ],
    [
        'url' => '/admissions.php',
        'changefreq' => 'weekly',
        'priority' => '0.9'
    ],
    [
        'url' => '/gallery.php',
        'changefreq' => 'weekly',
        'priority' => '0.7'
    ],
    [
        'url' => '/portfolio.php',
        'changefreq' => 'monthly',
        'priority' => '0.6'
    ],
    [
        'url' => '/news.php',
        'changefreq' => 'weekly',
        'priority' => '0.8'
    ],
    [
        'url' => '/contact.php',
        'changefreq' => 'yearly',
        'priority' => '0.7'
    ]
];

// Start XML output.
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Generate sitemap entries.
foreach ($pages as $page) {

    $url = $domain . $page['url'];

    echo "    <url>\n";

    echo "        <loc>" .
        htmlspecialchars($url, ENT_XML1, 'UTF-8') .
        "</loc>\n";

    echo "        <changefreq>" .
        $page['changefreq'] .
        "</changefreq>\n";

    echo "        <priority>" .
        $page['priority'] .
        "</priority>\n";

    echo "    </url>\n";
}

// Close XML.
echo '</urlset>';
