<?php
/**
 * Панель управління - Dashboard
 */

$total_posts = getTotalPosts();
$total_pages = getTotalPages();
$total_comments = getTotalComments();
$pending_comments = getPendingCommentsCount();
$published_posts = getTotalPosts('published');
$draft_posts = getTotalPosts('draft');

$popular_posts = getPopularPosts(5);
$recent_comments = getAllComments('pending', 10);

include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-container">
    <h1>Панель управління</h1>
    
    <?php if (Session::flash('success')): ?>
        <div class="alert alert-success"><?= e(Session::flash('success')) ?></div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-file-alt"></i>
            <div class="stat-number"><?= $total_posts ?></div>
            <div class="stat-label">Всього постів</div>
            <div class="stat-meta">
                Опубліковано: <?= $published_posts ?> | Чернеток: <?= $draft_posts ?>
            </div>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-file"></i>
            <div class="stat-number"><?= $total_pages ?></div>
            <div class="stat-label">Сторінок</div>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-comments"></i>
            <div class="stat-number"><?= $total_comments ?></div>
            <div class="stat-label">Коментарів</div>
        </div>
        
        <div class="stat-card <?= $pending_comments > 0 ? 'warning' : '' ?>">
            <i class="fas fa-clock"></i>
            <div class="stat-number"><?= $pending_comments ?></div>
            <div class="stat-label">Очікують модерації</div>
            <?php if ($pending_comments > 0): ?>
                <a href="/admin/comments?status=pending" class="stat-action">Переглянути</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2><i class="fas fa-fire"></i> Популярні пости</h2>
            
            <?php if (empty($popular_posts)): ?>
                <p class="empty-state">Поки що немає постів</p>
            <?php else: ?>
                <ul class="post-list">
                    <?php foreach ($popular_posts as $post): ?>
                        <li>
                            <div class="post-list-item">
                                <a href="<?= siteUrl($post['slug']) ?>" target="_blank" class="post-list-title">
                                    <?= e($post['title']) ?>
                                </a>
                                <div class="post-list-meta">
                                    <span><i class="fas fa-eye"></i> <?= formatViews($post['views']) ?></span>
                                    <span><i class="fas fa-calendar"></i> <?= formatDate($post['published_at']) ?></span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <a href="/admin/posts" class="section-footer-link">
                Всі пости <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="dashboard-section">
            <h2><i class="fas fa-comments"></i> Останні коментарі</h2>
            
            <?php if (empty($recent_comments)): ?>
                <p class="empty-state">Немає коментарів на модерації</p>
            <?php else: ?>
                <ul class="comment-list">
                    <?php foreach ($recent_comments as $comment): ?>
                        <li>
                            <div class="comment-list-item">
                                <div class="comment-list-header">
                                    <strong><?= e($comment['author_name']) ?></strong>
                                    <time><?= timeAgo($comment['created_at']) ?></time>
                                </div>
                                <p><?= e(truncateText($comment['content'], 100)) ?></p>
                                <div class="comment-list-meta">
                                    На пост: <a href="<?= siteUrl($comment['post_slug']) ?>" target="_blank">
                                        <?= e(truncateText($comment['post_title'], 40)) ?>
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <a href="/admin/comments" class="section-footer-link">
                Всі коментарі <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="quick-actions">
        <h2>Швидкі дії</h2>
        <div class="quick-actions-grid">
            <a href="/admin/posts?action=new" class="quick-action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Новий пост</span>
            </a>
            <a href="/admin/pages?action=new" class="quick-action-btn">
                <i class="fas fa-file-medical"></i>
                <span>Нова сторінка</span>
            </a>
            <a href="/admin/comments" class="quick-action-btn">
                <i class="fas fa-tasks"></i>
                <span>Модерація</span>
            </a>
            <a href="/admin/settings" class="quick-action-btn">
                <i class="fas fa-cog"></i>
                <span>Налаштування</span>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>