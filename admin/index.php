<?php
/**
 * Адмін-панель - роутинг
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/admin', '', $uri);
$uri = trim($uri, '/');

// Логін
if ($uri === 'login' || (!isset($_SESSION['admin_logged_in']) && $uri !== 'login')) {
    require_once __DIR__ . '/login.php';
    exit;
}

// Логаут
if ($uri === 'logout') {
    Session::logoutAdmin();
    redirect(siteUrl('admin/login'));
}

// Захист від CSRF для всіх POST-запитів
if (isPost() && !Security::verifyCSRFToken(post('csrf_token'))) {
    die('CSRF токен невалідний');
}

// Маршрути
switch ($uri) {
    case '':
    case 'dashboard':
        require_once __DIR__ . '/dashboard.php';
        break;
        
    case 'posts':
    case str_starts_with($uri, 'posts'):
        require_once __DIR__ . '/posts.php';
        break;
        
    case 'pages':
    case str_starts_with($uri, 'pages'):
        require_once __DIR__ . '/pages.php';
        break;
        
    case 'comments':
        require_once __DIR__ . '/comments.php';
        break;
        
    case 'settings':
        require_once __DIR__ . '/settings.php';
        break;
        
    case 'media':
        require_once __DIR__ . '/media.php';
        break;
        
    default:
        http_response_code(404);
        echo 'Сторінку не знайдено';
        break;
}