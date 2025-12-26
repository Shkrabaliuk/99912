<?php
/**
 * Маршрутизатор
 * Визначає який контент відображати залежно від URL
 */

class Router {
    public static function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');

        // Адмін-панель
        if (str_starts_with($uri, 'admin')) {
            self::handleAdmin($uri);
            return;
        }

        // Спеціальні маршрути
        if ($uri === 'sitemap.xml') {
            self::handleSitemap();
            return;
        }

        if ($uri === 'rss') {
            self::handleRSS();
            return;
        }

        // Головна сторінка
        if (empty($uri) || $uri === 'index.php') {
            self::handleHome();
            return;
        }

        // Пошук
        if ($uri === 'search' && isset($_GET['q'])) {
            self::handleSearch();
            return;
        }

        // Тег
        if (str_starts_with($uri, 'tag/')) {
            $slug = substr($uri, 4);
            self::handleTag($slug);
            return;
        }

        // Сторінка або пост
        self::handleSlug($uri);
    }

    private static function handleHome() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        // Перевірка кешу
        $cache_key = "home_page_{$page}";
        $cached = Cache::get($cache_key);
        
        if ($cached !== null) {
            echo $cached;
            return;
        }

        ob_start();
        
        $settings = getSiteSettings();
        $posts_per_page = (int)$settings['posts_per_page'];
        $offset = ($page - 1) * $posts_per_page;

        $posts = getPublishedPosts($posts_per_page, $offset);
        $total_posts = getTotalPublishedPosts();
        $total_pages = ceil($total_posts / $posts_per_page);

        require_once __DIR__ . '/../templates/header.php';
        require_once __DIR__ . '/../templates/home.php';
        require_once __DIR__ . '/../templates/footer.php';
        
        $output = ob_get_clean();
        Cache::set($cache_key, $output);
        echo $output;
    }

    private static function handleSlug($slug) {
        // Спочатку шукаємо сторінку
        $page = getPageBySlug($slug);
        if ($page && $page['status'] === 'published') {
            self::renderPage($page);
            return;
        }

        // Потім пост
        $post = getPostBySlug($slug);
        if ($post && $post['status'] === 'published') {
            self::renderPost($post);
            return;
        }

        // 404
        self::handle404();
    }

    private static function renderPage($page) {
        $cache_key = "page_{$page['slug']}";
        $cached = Cache::get($cache_key);
        
        if ($cached !== null) {
            echo $cached;
            return;
        }

        ob_start();
        
        $settings = getSiteSettings();
        
        require_once __DIR__ . '/../templates/header.php';
        require_once __DIR__ . '/../templates/page.php';
        require_once __DIR__ . '/../templates/footer.php';
        
        $output = ob_get_clean();
        Cache::set($cache_key, $output);
        echo $output;
    }

    private static function renderPost($post) {
        // Збільшення лічильника переглядів
        incrementPostViews($post['id']);
        
        $cache_key = "post_{$post['slug']}";
        $cached = Cache::get($cache_key);
        
        if ($cached !== null) {
            echo $cached;
            return;
        }

        ob_start();
        
        $settings = getSiteSettings();
        $tags = getPostTags($post['id']);
        $comments = getApprovedComments($post['id']);
        
        require_once __DIR__ . '/../templates/header.php';
        require_once __DIR__ . '/../templates/single.php';
        require_once __DIR__ . '/../templates/footer.php';
        
        $output = ob_get_clean();
        Cache::set($cache_key, $output);
        echo $output;
    }

    private static function handleTag($slug) {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        $tag = getTagBySlug($slug);
        if (!$tag) {
            self::handle404();
            return;
        }

        $settings = getSiteSettings();
        $posts_per_page = (int)$settings['posts_per_page'];
        $offset = ($page - 1) * $posts_per_page;

        $posts = getPostsByTag($tag['id'], $posts_per_page, $offset);
        $total_posts = countPostsByTag($tag['id']);
        $total_pages = ceil($total_posts / $posts_per_page);

        require_once __DIR__ . '/../templates/header.php';
        require_once __DIR__ . '/../templates/tag.php';
        require_once __DIR__ . '/../templates/footer.php';
    }

    private static function handleSearch() {
        $query = trim($_GET['q'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        if (empty($query)) {
            header('Location: ' . siteUrl());
            exit;
        }

        $settings = getSiteSettings();
        $posts_per_page = (int)$settings['posts_per_page'];
        $offset = ($page - 1) * $posts_per_page;

        $posts = searchPosts($query, $posts_per_page, $offset);
        $total_posts = countSearchResults($query);
        $total_pages = ceil($total_posts / $posts_per_page);

        require_once __DIR__ . '/../templates/header.php';
        require_once __DIR__ . '/../templates/search.php';
        require_once __DIR__ . '/../templates/footer.php';
    }

    private static function handleSitemap() {
        header('Content-Type: application/xml; charset=utf-8');
        echo generateSitemap();
    }

    private static function handleRSS() {
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo generateRSS();
    }

    private static function handleAdmin($uri) {
        // Перевірка авторизації
        if (!Session::isAdmin()) {
            $admin_path = str_replace('/admin', '', $uri);
            if ($admin_path !== '/login' && $admin_path !== 'login') {
                header('Location: ' . siteUrl('admin/login'));
                exit;
            }
        }

        require_once __DIR__ . '/../admin/index.php';
    }

    private static function handle404() {
        http_response_code(404);
        $settings = getSiteSettings();
        
        require_once __DIR__ . '/../templates/header.php';
        require_once __DIR__ . '/../templates/404.php';
        require_once __DIR__ . '/../templates/footer.php';
    }
}