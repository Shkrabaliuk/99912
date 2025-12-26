<?php
$page_title = 'Тег: ' . $tag['name'];
?>

<div class="container">
    <header class="archive-header">
        <h1><i class="fas fa-tag"></i> <?= e($tag['name']) ?></h1>
        <p><?= $total_posts ?> постів</p>
    </header>
    
    <div class="posts-grid">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <time><?= formatDate($post['published_at']) ?></time>
                <h2><a href="<?= siteUrl($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
                <div class="post-excerpt"><?= e(truncateText($post['content'], 200)) ?></div>
                <a href="<?= siteUrl($post['slug']) ?>" class="read-more">Читати далі</a>
            </article>
        <?php endforeach; ?>
    </div>
    
    <?= pagination($page, $total_pages, siteUrl('tag/' . $tag['slug'])) ?>
</div>