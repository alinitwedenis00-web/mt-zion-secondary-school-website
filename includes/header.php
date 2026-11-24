<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/site.php';

$siteName = setting('school_name', 'MT ZION SECONDARY SCHOOL MUBENDE');
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalPath = preg_replace('#/index\.php$#', '/', $currentPath);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? $siteName) ?></title>
    <meta name="description" content="<?= e(setting('meta_description', 'Mt Zion Secondary School Mubende')) ?>">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?= e(SITE_URL . $canonicalPath) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle ?? $siteName) ?>">
    <meta property="og:description" content="<?= e(setting('meta_description', 'Mt Zion Secondary School Mubende')) ?>">
    <meta property="og:url" content="<?= e(SITE_URL . $canonicalPath) ?>">
    <link rel="icon" type="image/jpeg" href="assets/images/school/BADGE-CIRCLE.png">
    <link rel="stylesheet" href="assets/css/style.css?v=20261121-nav-circle">
</head>
<body id="page-top">
<header class="site-header">
    <a class="brand" href="index.php"><img src="assets/images/school/BADGE.jpg" alt="Mt Zion badge"><span><?= e($siteName) ?></span></a>
    <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
    <nav aria-label="Main navigation">
        <a class="<?= active_page('index.php') ?>" href="index.php">Home</a>
        <a class="<?= active_page('about.php') ?>" href="about.php">About Us</a>
        <a class="<?= active_page('staff.php') ?>" href="staff.php">Our Staff</a>
        <a class="<?= active_page('board.php') ?>" href="board.php">Board of Governors</a>
        <a class="<?= active_page('services.php') ?>" href="services.php">Academics</a>
        <a class="<?= active_page('admissions.php') ?>" href="admissions.php">Admissions</a>
        <a class="<?= active_page('admission-track.php') ?>" href="admission-track.php">Track Admission</a>
        <a class="<?= active_page('gallery.php') ?>" href="gallery.php">Gallery</a>
        <a class="<?= active_page('news.php') ?>" href="news.php">News</a>
        <a class="<?= active_page('portfolio.php') ?>" href="portfolio.php">Portfolio</a>
        <a class="<?= active_page('contact.php') ?>" href="contact.php">Contact Us</a>
        <a class="<?= active_page('message-track.php') ?>" href="message-track.php">Track Message</a>
        <a class="apply-now" href="admissions.php">APPLY NOW</a>
    </nav>
</header>
<main>
