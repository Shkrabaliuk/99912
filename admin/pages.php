<?php
/**
 * Управління сторінками
 */

$action = $_GET['action'] ?? 'list';
$page_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Видалення сторінки
if ($action === 'delete' && $page_id) {
    deletePage($page_id);
    Session::flash('success', 'Сторінку видалено');
    redirect('/admin/pages');
}

// Редагування або створення
if ($action === 'edit' || $action === 'new') {
    $page = $page_id ? getPageById($page_id) : null;
    
    if (isPost()) {
        $data = [
            'title' => post('title'),
            'content' => post('content'),
            'meta_description' => post('meta_description'),
            'status' => post('status', 'published')
        ];
        
        try {
            if ($page) {
                updatePage($page_id, $data);
                Session::flash('success', 'Сторінку оновлено');
                redirect('/admin/pages?action=edit&id=' . $page_id);
            } else {
                $new_id = createPage($data);
                Session::flash('success', 'Сторінку створено');
                redirect('/admin/pages?action=edit&id=' . $new_id);
            }
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $errorMessage = '';
            foreach ($errors as $field => $fieldErrors) {
                $errorMessage .= implode(', ', $fieldErrors) . '; ';
            }
            Session::flash('error', trim($errorMessage, '; '));
        } catch (DatabaseException $e) {
            Session::flash('error', 'Помилка бази даних. Спробуйте ще раз.');
        }
    }
    
    include __DIR__ . '/includes/admin-header.php';
    ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>
                <i class="fas fa-<?= $page ? 'edit' : 'plus' ?>"></i>
                <?= $page ? 'Редагування сторінки' : 'Нова сторінка' ?>
            </h1>
            <a href="/admin/pages" class="btn">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
        
        <?php if (Session::flash('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= e(Session::flash('success')) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="post-form">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            
            <div class="form-row">
                <div class="form-main">
                    <div class="form-group">
                        <label for="title">Назва сторінки *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="<?= $page ? e($page['title']) : '' ?>" 
                            required 
                            autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Контент (Markdown) *</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="20" 
                            required><?= $page ? e($page['content']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description">Meta опис (для SEO)</label>
                        <textarea 
                            id="meta_description" 
                            name="meta_description" 
                            rows="2"><?= $page ? e($page['meta_description']) : '' ?></textarea>
                        <small>Рекомендовано: 150-160 символів</small>
                    </div>
                </div>
                
                <div class="form-sidebar">
                    <div class="sidebar-section">
                        <h3><i class="fas fa-cog"></i> Публікація</h3>
                        
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select id="status" name="status">
                                <option value="published" <?= (!$page || $page['status'] === 'published') ? 'selected' : '' ?>>
                                    Опубліковано
                                </option>
                                <option value="draft" <?= ($page && $page['status'] === 'draft') ? 'selected' : '' ?>>
                                    Чернетка
                                </option>
                            </select>
                        </div>
                        
                        <?php if ($page): ?>
                            <div class="post-meta-info">
                                <p>
                                    <strong>Створено:</strong><br>
                                    <?= formatDate($page['created_at']) ?>
                                </p>
                                <p>
                                    <strong>Slug:</strong><br>
                                    <code><?= e($page['slug']) ?></code>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i>
                            <?= $page ? 'Оновити' : 'Створити' ?>
                        </button>
                        
                        <?php if ($page && $page['status'] === 'published'): ?>
                            <a href="<?= siteUrl($page['slug']) ?>" target="_blank" class="btn btn-block" style="margin-top: 0.5rem;">
                                <i class="fas fa-external-link-alt"></i> Переглянути
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <?php
    include __DIR__ . '/includes/admin-footer.php';
    exit;
}

// Список сторінок
$pages = getAllPages();

include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-file"></i> Сторінки</h1>
        <a href="/admin/pages?action=new" class="btn btn-primary">
            <i class="fas fa-plus"></i> Нова сторінка
        </a>
    </div>
    
    <?php if (Session::flash('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= e(Session::flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($pages)): ?>
        <div class="empty-state">
            <i class="fas fa-file"></i>
            <h3>Немає сторінок</h3>
            <p>Створіть першу статичну сторінку (Про мене, Контакти, тощо)</p>
            <a href="/admin/pages?action=new" class="btn btn-primary">
                <i class="fas fa-plus"></i> Створити сторінку
            </a>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Назва</th>
                    <th>Slug</th>
                    <th>Статус</th>
                    <th>Дата створення</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <tr>
                        <td>
                            <strong class="table-title"><?= e($page['title']) ?></strong>
                        </td>
                        <td>
                            <code><?= e($page['slug']) ?></code>
                        </td>
                        <td>
                            <span class="badge badge-<?= $page['status'] ?>">
                                <?= $page['status'] === 'published' ? 'Опубліковано' : 'Чернетка' ?>
                            </span>
                        </td>
                        <td><?= formatDate($page['created_at']) ?></td>
                        <td class="table-actions">
                            <a href="/admin/pages?action=edit&id=<?= $page['id'] ?>" class="action-link">
                                <i class="fas fa-edit"></i> Редагувати
                            </a>
                            <?php if ($page['status'] === 'published'): ?>
                                <a href="<?= siteUrl($page['slug']) ?>" target="_blank" class="action-link">
                                    <i class="fas fa-external-link-alt"></i> Переглянути
                                </a>
                            <?php endif; ?>
                            <a href="/admin/pages?action=delete&id=<?= $page['id'] ?>" 
                               class="action-link action-danger" 
                               onclick="return confirm('Видалити цю сторінку?')">
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