<?php
$title = $title ?? 'Admin Dashboard';
$adminName = $_SESSION['admin_name'] ?? 'Administrator';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | Mt Zion</title>
    <link rel="icon" type="image/jpeg" href="../assets/images/school/BADGE-CIRCLE.png">
    <link rel="stylesheet" href="../assets/css/style.css?v=20261121-nav-circle">
</head>
<body class="admin-body">
<header class="admin-head">
    <a class="admin-brand" href="index.php">
        <img src="../assets/images/school/BADGE.jpg" alt="Mt Zion school logo">
        <span><strong>MT ZION</strong><small>Website Administration</small></span>
    </a>
    <div class="admin-user">
        <span>Signed in as <strong><?= e($adminName) ?></strong></span>
        <a href="../index.php" target="_blank" rel="noopener">View Website</a>
        <a class="admin-logout" href="logout.php">Log out</a>
    </div>
</header>
<nav class="admin-nav" aria-label="Administration navigation">
    <a href="index.php">Dashboard</a><a href="manage.php?type=slides">Slider</a>
    <a href="manage.php?type=achievers">Giants</a><a href="manage.php?type=staff">Staff</a>
    <a href="manage.php?type=board">Board</a><a href="manage.php?type=services">Academics</a>
    <a href="manage.php?type=portfolio">Portfolio</a><a href="manage.php?type=stats">Statistics</a>
    <a href="news.php">News</a><a href="gallery.php">Gallery</a>
    <a href="content.php">Site Content</a><a href="messages.php">Messages</a>
    <a href="admissions.php">Admissions</a><a href="change_credentials.php">Account</a>
</nav>
<main class="admin-main">
