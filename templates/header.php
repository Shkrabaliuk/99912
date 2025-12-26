<?php
$site_name = $settings['site_name'] ?? 'Блог';
$site_description = $settings['site_description'] ?? '';
$theme_color = $settings['theme_color'] ?? '#2c3e50';
$page_title = isset($page_title) ? $page_title . ' - ' . $site_name : $site_name;
$meta_description = isset($meta_description) ? $meta_description : $site_description;
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($meta_description) ?>">
    <link rel="canonical" href="<?= e(getCanonicalUrl()) ?>">
    <link rel="alternate" type="application/rss+xml" title="<?= e($site_name) ?>" href="<?= siteUrl('rss') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/style.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('libs/font-awesome/css/all.min.css') ?>">
    <style>:root {--theme-color: <?= e($theme_color) ?>;}</style>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?= siteUrl() ?>">
                        <h1><?= e($site_name) ?></h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?= siteUrl() ?>">Головна</a></li>
                        <?php
                        $pages = getAllPublishedPages();
                        foreach ($pages as $nav_page):
                        ?>
                            <li><a href="<?= siteUrl($nav_page['slug']) ?>"><?= e($nav_page['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="search-toggle" aria-label="Пошук">
                        <i class="fas fa-search"></i>
                    </button>
                </nav>
                <button class="mobile-menu-toggle" aria-label="Меню">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="search-form-wrapper">
                <form class="search-form" action="<?= siteUrl('search') ?>" method="get">
                    <input type="search" name="q" placeholder="Пошук..." value="<?= e(get('q')) ?>" required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </header>
    <main class="site-content">