<?php
/**
 * Модерація коментарів
 */

$action = $_GET['action'] ?? 'list';
$comment_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Схвалення коментаря
if ($action === 'approve' && $comment_id) {
    approveComment($comment_id);
    Session::flash('success', 'Коментар схвалено');
    redirect(siteUrl('admin/comments' . (isset($_GET['status']) ? '?status=' . $_GET['status'] : '')));
}

// Позначити як спам
if ($action === 'spam' && $comment_id) {
    markAsSpam($comment_id);
    Session::flash('success', 'Коментар позначено як спам');
    redirect(siteUrl('admin/comments' . (isset($_GET['status']) ? '?status=' . $_GET['status'] : '')));
}

// Видалення коментаря
if ($action === 'delete' && $comment_id) {
    deleteComment($comment_id);
    Session::flash('success', 'Коментар видалено');
    redirect(siteUrl('admin/comments' . (isset($_GET['status']) ? '?status=' . $_GET['status'] : '')));
}

// Список коментарів
$status_filter = $_GET['status'] ?? null;
$comments = getAllComments($status_filter, 100);

include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-container">
    <h1><i class="fas fa-comments"></i> Коментарі</h1>
    
    <?php if (Session::flash('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= e(Session::flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <div class="filter-tabs">
        <a href="<?= siteUrl('admin/comments') ?>" class="<?= !$status_filter ? 'active' : '' ?>">
            Всі (<?= getTotalComments() ?>)
        </a>
        <a href="<?= siteUrl('admin/comments?status=pending') ?>" class="<?= $status_filter === 'pending' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i> Очікують (<?= getTotalComments('pending') ?>)
        </a>
        <a href="<?= siteUrl('admin/comments?status=approved') ?>" class="<?= $status_filter === 'approved' ? 'active' : '' ?>">
            <i class="fas fa-check"></i> Схвалені (<?= getTotalComments('approved') ?>)
        </a>
        <a href="<?= siteUrl('admin/comments?status=spam') ?>" class="<?= $status_filter === 'spam' ? 'active' : '' ?>">
            <i class="fas fa-ban"></i> Спам (<?= getTotalComments('spam') ?>)
        </a>
    </div>
    
    <?php if (empty($comments)): ?>
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h3>Немає коментарів</h3>
            <p>
                <?php if ($status_filter === 'pending'): ?>
                    Всі коментарі оброблено. Чудова робота!
                <?php elseif ($status_filter): ?>
                    У цій категорії немає коментарів
                <?php else: ?>
                    Поки що ніхто не залишив коментарі під вашими постами
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="comments-moderation">
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item <?= 'status-' . $comment['status'] ?>">
                    <div class="comment-header">
                        <div class="comment-author-info">
                            <strong class="comment-author">
                                <i class="fas fa-user"></i>
                                <?= e($comment['author_name']) ?>
                            </strong>
                            <span class="comment-email"><?= e($comment['author_email']) ?></span>
                            <span class="badge badge-<?= $comment['status'] ?>">
                                <?php
                                $status_labels = [
                                    'pending' => 'Очікує',
                                    'approved' => 'Схвалено',
                                    'spam' => 'Спам'
                                ];
                                echo $status_labels[$comment['status']];
                                ?>
                            </span>
                        </div>
                        <time class="comment-date">
                            <i class="fas fa-clock"></i>
                            <?= timeAgo($comment['created_at']) ?>
                        </time>
                    </div>
                    
                    <div class="comment-content">
                        <?= nl2br(e($comment['content'])) ?>
                    </div>
                    
                    <div class="comment-meta">
                        <i class="fas fa-file-alt"></i>
                        На пост: 
                        <a href="<?= siteUrl($comment['post_slug']) ?>" target="_blank">
                            <?= e($comment['post_title']) ?>
                        </a>
                    </div>
                    
                    <div class="comment-meta">
                        <i class="fas fa-globe"></i>
                        IP: <?= e($comment['ip_address']) ?>
                    </div>
                    
                    <div class="comment-actions">
                        <?php if ($comment['status'] === 'pending'): ?>
                            <a href="<?= siteUrl('admin/comments?action=approve&id=' . $comment['id'] . ($status_filter ? '&status=' . $status_filter : '')) ?>" 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Схвалити
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($comment['status'] !== 'spam'): ?>
                            <a href="<?= siteUrl('admin/comments?action=spam&id=' . $comment['id'] . ($status_filter ? '&status=' . $status_filter : '')) ?>" 
                               class="btn btn-sm">
                                <i class="fas fa-ban"></i> Спам
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= siteUrl('admin/comments?action=delete&id=' . $comment['id'] . ($status_filter ? '&status=' . $status_filter : '')) ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Видалити цей коментар назавжди?')">
                            <i class="fas fa-trash"></i> Видалити
                        </a>
                        
                        <a href="<?= siteUrl($comment['post_slug']) ?>#comment-<?= $comment['id'] ?>" 
                           target="_blank" 
                           class="btn btn-sm">
                            <i class="fas fa-external-link-alt"></i> Переглянути на сайті
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>