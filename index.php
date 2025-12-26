<?php
session_start();
if(!file_exists(__DIR__.'/config.php')){
    if(file_exists(__DIR__.'/install/index.php')){
        // Визначення базового шляху для коректного редіректу
        $base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if($base_path === '/' || $base_path === '') $base_path = '';
        header('Location: ' . $base_path . '/install/');
        exit;
    }
    die('Error: config.php missing');
}
require_once __DIR__.'/config.php';
require_once __DIR__.'/core/database.php';
require_once __DIR__.'/core/router.php';
require_once __DIR__.'/core/security.php';
require_once __DIR__.'/core/session.php';
require_once __DIR__.'/functions/helpers.php';
require_once __DIR__.'/functions/posts.php';
require_once __DIR__.'/functions/pages.php';
require_once __DIR__.'/functions/comments.php';
require_once __DIR__.'/functions/tags.php';
require_once __DIR__.'/functions/cache.php';
require_once __DIR__.'/functions/seo.php';
Database::getInstance();
Security::init();
Router::dispatch();