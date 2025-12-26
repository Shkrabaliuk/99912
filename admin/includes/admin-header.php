<?php
/**
 * Шапка адмін-панелі
 */

$current_page = $_SERVER['REQUEST_URI'];
$pending_count = getPendingCommentsCount();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель - <?= e(getSetting('site_name', 'CMS')) ?></title>
    <link rel="stylesheet" href="<?= assetUrl('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('libs/font-awesome/css/all.min.css') ?>">
    <script>
        // Глобальна конфігурація для JavaScript
        window.CMS_CONFIG = {
            siteUrl: <?= json_encode(SITE_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };
    </script>
</head>
<body class="admin-body">
    <nav class="admin-sidebar">
        <div class="admin-logo">
            <i class="fas fa-rocket"></i>
            <h2>CMS Admin</h2>
        </div>
        
        <ul class="admin-menu">
            <li>
                <a href="<?= siteUrl('admin') ?>" class="<?= str_contains($current_page, '/admin') && !str_contains($current_page, '/admin/posts') && !str_contains($current_page, '/admin/pages') && !str_contains($current_page, '/admin/comments') && !str_contains($current_page, '/admin/settings') ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Панель управління</span>
                </a>
            </li>
            
            <li>
                <a href="<?= siteUrl('admin/posts') ?>" class="<?= str_contains($current_page, '/admin/posts') ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Пости</span>
                </a>
            </li>
            
            <li>
                <a href="<?= siteUrl('admin/pages') ?>" class="<?= str_contains($current_page, '/admin/pages') ? 'active' : '' ?>">
                    <i class="fas fa-file"></i>
                    <span>Сторінки</span>
                </a>
            </li>
            
            <li>
                <a href="<?= siteUrl('admin/comments') ?>" class="<?= str_contains($current_page, '/admin/comments') ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i>
                    <span>Коментарі</span>
                    <?php if ($pending_count > 0): ?>
                        <span class="badge-count"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="menu-separator"></li>
            
            <li>
                <a href="<?= siteUrl('admin/settings') ?>" class="<?= str_contains($current_page, '/admin/settings') ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>Налаштування</span>
                </a>
            </li>
            
            <li>
                <a href="<?= siteUrl() ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Переглянути сайт</span>
                </a>
            </li>
            
            <li>
                <a href="<?= siteUrl('admin/logout') ?>" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Вийти</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <main class="admin-main">