<?php
/**
 * Управління постами
 */

$action = $_GET['action'] ?? 'list';
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Видалення поста
if ($action === 'delete' && $post_id) {
    deletePost($post_id);
    Session::flash('success', 'Пост успішно видалено');
    redirect(siteUrl('admin/posts'));
}

// Редагування або створення
if ($action === 'edit' || $action === 'new') {
    $post = $post_id ? getPostById($post_id) : null;
    
    if (isPost()) {
        $tags_string = post('tags', '');
        $tags_array = array_filter(array_map('trim', explode(',', $tags_string)));
        
        $data = [
            'title' => post('title'),
            'content' => post('content'),
            'excerpt' => post('excerpt'),
            'status' => post('status'),
            'tags' => $tags_array
        ];
        
        if ($post) {
            updatePost($post_id, $data);
            Session::flash('success', 'Пост оновлено');
            redirect(siteUrl('admin/posts?action=edit&id=' . $post_id));
        } else {
            $new_id = createPost($data);
            Session::flash('success', 'Пост створено');
            redirect(siteUrl('admin/posts?action=edit&id=' . $new_id));
        }
    }
    
    include __DIR__ . '/includes/post-form.php';
    exit;
}

// Список постів
$status_filter = $_GET['status'] ?? null;
$posts = getAllPosts(100, 0, $status_filter);

include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-file-alt"></i> Пости</h1>
        <a href="<?= siteUrl('admin/posts?action=new') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Новий пост
        </a>
    </div>
    
    <?php if (Session::flash('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= e(Session::flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <div class="filter-tabs">
        <a href="<?= siteUrl('admin/posts') ?>" class="<?= !$status_filter ? 'active' : '' ?>">
            Всі (<?= getTotalPosts() ?>)
        </a>
        <a href="<?= siteUrl('admin/posts?status=published') ?>" class="<?= $status_filter === 'published' ? 'active' : '' ?>">
            Опубліковано (<?= getTotalPosts('published') ?>)
        </a>
        <a href="<?= siteUrl('admin/posts?status=draft') ?>" class="<?= $status_filter === 'draft' ? 'active' : '' ?>">
            Чернетки (<?= getTotalPosts('draft') ?>)
        </a>
    </div>
    
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Немає постів</h3>
            <p>Створіть свій перший пост, щоб розпочати блогінг</p>
            <a href="<?= siteUrl('admin/posts?action=new') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Створити пост
            </a>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Заголовок</th>
                    <th>Статус</th>
                    <th>Дата створення</th>
                    <th>Перегляди</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <strong class="table-title"><?= e($post['title']) ?></strong>
                            <?php
                            $tags = getPostTags($post['id']);
                            if (!empty($tags)):
                            ?>
                                <div class="table-tags">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="tag-mini"><?= e($tag['name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $post['status'] ?>">
                                <?= $post['status'] === 'published' ? 'Опубліковано' : 'Чернетка' ?>
                            </span>
                        </td>
                        <td>
                            <?= formatDate($post['created_at']) ?>
                            <?php if ($post['status'] === 'published' && $post['published_at']): ?>
                                <br><small class="text-muted">
                                    Опубліковано: <?= formatDate($post['published_at']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="views-count">
                                <i class="fas fa-eye"></i> <?= formatViews($post['views']) ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="<?= siteUrl('admin/posts?action=edit&id=' . $post['id']) ?>" class="action-link">
                                <i class="fas fa-edit"></i> Редагувати
                            </a>
                            <?php if ($post['status'] === 'published'): ?>
                                <a href="<?= siteUrl($post['slug']) ?>" target="_blank" class="action-link">
                                    <i class="fas fa-external-link-alt"></i> Переглянути
                                </a>
                            <?php endif; ?>
                            <a href="<?= siteUrl('admin/posts?action=delete&id=' . $post['id']) ?>" 
                               class="action-link action-danger" 
                               onclick="return confirm('Ви впевнені, що хочете видалити цей пост?')">
                                <i class="fas fa-trash"></i> Видалити
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>