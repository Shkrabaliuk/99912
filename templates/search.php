<?php
$page_title = 'Пошук: ' . $query;
?>

<div class="container">
    <header class="search-header">
        <h1>Результати пошуку</h1>
        <p>За запитом: <strong><?= e($query) ?></strong></p>
        <p>Знайдено: <?= $total_posts ?> постів</p>
    </header>
    
    <?php if (empty($posts)): ?>
        <div class="no-results">
            <p>Нічого не знайдено. Спробуйте інший запит.</p>
        </div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <article class="search-result">
                    <h2><a href="<?= siteUrl($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
                    <p><?= e(truncateText($post['content'], 250)) ?></p>
                    <time><?= formatDate($post['published_at']) ?></time>
                </article>
            <?php endforeach; ?>
        </div>
        
        <?= pagination($page, $total_pages, siteUrl('search?q=' . urlencode($query))) ?>
    <?php endif; ?>
</div>