<?php
require_once __DIR__ . '/functions.php';
$siteTitle = getSetting('site_title', 'Amanuel Yohannes');
$favicon   = getSetting('favicon');
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<script>document.documentElement.setAttribute('data-theme',localStorage.getItem('theme')||'dark');</script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= e(getSetting('site_description')) ?>">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e($siteTitle) ?></title>
<?php if ($favicon): ?>
<link rel="icon" href="<?= e(assetUrl($favicon)) ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/public.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/testimonials.css">
</head>
<body>

<nav class="site-nav" id="siteNav">
    <div class="nav-inner">
        <a href="<?= BASE_URL ?>/" class="nav-logo">
            <span class="logo-name"><?= e(getSetting('site_title', 'Amanuel Yohannes')) ?><span class="logo-name-mark"></span></span>
            <span class="logo-title"><?= e(getSetting('designer_title', 'Graphic Designer')) ?></span>
        </a>
        <div class="nav-mobile-controls">
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
                <img class="toggle-img toggle-img--light" src="<?= BASE_URL ?>/side/Bright Mode-01.png" alt="">
                <img class="toggle-img toggle-img--dark"  src="<?= BASE_URL ?>/side/Dark Mode-02.png"  alt="">
            </button>
            <button class="nav-burger" id="navBurger" aria-label="Menu">
                <span></span><span></span>
            </button>
        </div>
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>/#portfolio">Projects</a></li>
            <li><a href="<?= BASE_URL ?>/#about">About</a></li>
            <li><a href="<?= BASE_URL ?>/#contact">Contact Us</a></li>
            <li><a href="<?= BASE_URL ?>/#clients">Clients</a></li>
            <li><button class="theme-toggle" id="themeToggleDesktop" aria-label="Toggle dark mode">
                <img class="toggle-img toggle-img--light" src="<?= BASE_URL ?>/side/Bright Mode-01.png" alt="">
                <img class="toggle-img toggle-img--dark"  src="<?= BASE_URL ?>/side/Dark Mode-02.png"  alt="">
            </button></li>
        </ul>
    </div>
</nav>

<!-- Mobile menu overlay -->
<div class="nav-mobile-menu" id="mobileMenu">
    <a href="<?= BASE_URL ?>/#portfolio">Projects</a>
    <a href="<?= BASE_URL ?>/#about">About</a>
    <a href="<?= BASE_URL ?>/#contact">Contact Us</a>
    <a href="<?= BASE_URL ?>/#clients">Clients</a>
</div>
