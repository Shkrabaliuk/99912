# templates/page.php
```php
<?php
$page_title = $page['title'];
$meta_description = $page['meta_description'] ?? '';
?>

<div class="container">
    <article class="single-page">
        <header class="page-header">
            <h1><?= e($page['title']) ?></h1>
        </header>
        
        <div class="page-content">
            <?= parseMarkdown($page['content']) ?>
        </div>
    </article>
</div>
```

# templates/tag.php
```php
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
```

# templates/search.php
```php
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
```

# templates/404.php
```php
<?php $page_title = 'Сторінку не знайдено'; ?>

<div class="container">
    <div class="error-404">
        <h1>404</h1>
        <h2>Сторінку не знайдено</h2>
        <p>На жаль, запитана сторінка не існує.</p>
        <a href="<?= siteUrl() ?>" class="btn">Повернутися на головну</a>
    </div>
</div>
```