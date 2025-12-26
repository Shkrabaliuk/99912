<?php
/**
 * Обробник додавання коментарів
 * Розмістити у корені сайту
 */

require_once __DIR__ . '/index.php';

if (!isPost()) {
    redirect(siteUrl());
}

// Перевірка CSRF
if (!Security::verifyCSRFToken(post('csrf_token'))) {
    Session::flash('comment_error', 'Помилка безпеки. Спробуйте ще раз.');
    redirect($_SERVER['HTTP_REFERER'] ?? siteUrl());
}

$post_id = (int)post('post_id');
$post = getPostById($post_id);

if (!$post || $post['status'] !== 'published') {
    redirect(siteUrl());
}

$data = [
    'author_name' => post('author_name'),
    'author_email' => post('author_email'),
    'content' => post('content')
];

$result = addComment($post_id, $data);

if ($result) {
    Session::flash('comment_success', true);
} else {
    Session::flash('comment_error', 'Помилка додавання коментаря. Перевірте правильність заповнення форми.');
}

redirect(siteUrl($post['slug']) . '#comments');