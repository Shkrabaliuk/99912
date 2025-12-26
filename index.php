<?php
session_start();
if(!file_exists(__DIR__.'/config.php')){
    if(file_exists(__DIR__.'/install/index.php')){header('Location: /install/');exit;}
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