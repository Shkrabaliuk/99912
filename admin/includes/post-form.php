<?php
/**
 * Форма створення/редагування поста
 */

$post_tags = $post ? getPostTags($post['id']) : [];
$tags_string = implode(', ', array_column($post_tags, 'name'));

include __DIR__ . '/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>
            <i class="fas fa-<?= $post ? 'edit' : 'plus' ?>"></i>
            <?= $post ? 'Редагування поста' : 'Новий пост' ?>
        </h1>
        <a href="<?= siteUrl('admin/posts') ?>" class="btn">
            <i class="fas fa-arrow-left"></i> Назад до списку
        </a>
    </div>
    
    <?php if (Session::flash('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= e(Session::flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <form method="post" class="post-form" id="postForm">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        
        <div class="form-row">
            <div class="form-main">
                <div class="form-group">
                    <label for="title">
                        Заголовок поста *
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="<?= $post ? e($post['title']) : '' ?>" 
                        required 
                        autofocus
                        placeholder="Введіть заголовок поста">
                </div>
                
                <div class="form-group">
                    <label for="content">
                        Контент (Markdown) *
                        <span class="label-help">
                            <a href="#" id="markdownHelp">
                                <i class="fas fa-question-circle"></i> Довідка Markdown
                            </a>
                        </span>
                    </label>
                    <textarea 
                        id="content" 
                        name="content" 
                        rows="20" 
                        required
                        placeholder="Пишіть тут, використовуючи Markdown..."><?= $post ? e($post['content']) : '' ?></textarea>
                    <small>Підтримується Markdown: **жирний**, *курсив*, [посилання](url), # Заголовок, тощо</small>
                </div>
                
                <div class="form-group">
                    <label for="excerpt">
                        Короткий опис (excerpt)
                    </label>
                    <textarea 
                        id="excerpt" 
                        name="excerpt" 
                        rows="3"
                        placeholder="Короткий опис для картки поста на головній сторінці"><?= $post ? e($post['excerpt']) : '' ?></textarea>
                    <small>Якщо не вказано, буде використано перші 200 символів контенту</small>
                </div>
            </div>
            
            <div class="form-sidebar">
                <div class="sidebar-section">
                    <h3><i class="fas fa-cog"></i> Публікація</h3>
                    
                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft" <?= (!$post || $post['status'] === 'draft') ? 'selected' : '' ?>>
                                Чернетка
                            </option>
                            <option value="published" <?= ($post && $post['status'] === 'published') ? 'selected' : '' ?>>
                                Опубліковано
                            </option>
                        </select>
                    </div>
                    
                    <?php if ($post): ?>
                        <div class="post-meta-info">
                            <p>
                                <strong>Створено:</strong><br>
                                <?= formatDate($post['created_at']) ?>
                            </p>
                            <?php if ($post['published_at']): ?>
                                <p>
                                    <strong>Опубліковано:</strong><br>
                                    <?= formatDate($post['published_at']) ?>
                                </p>
                            <?php endif; ?>
                            <p>
                                <strong>Переглядів:</strong> <?= $post['views'] ?>
                            </p>
                            <p>
                                <strong>Slug:</strong><br>
                                <code><?= e($post['slug']) ?></code>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i>
                        <?= $post ? 'Оновити пост' : 'Створити пост' ?>
                    </button>
                    
                    <?php if ($post && $post['status'] === 'published'): ?>
                        <a href="<?= siteUrl($post['slug']) ?>" target="_blank" class="btn btn-block" style="margin-top: 0.5rem;">
                            <i class="fas fa-external-link-alt"></i> Переглянути на сайті
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3><i class="fas fa-tags"></i> Теги</h3>
                    
                    <div class="form-group">
                        <label for="tags">Теги (через кому)</label>
                        <input 
                            type="text" 
                            id="tags" 
                            name="tags" 
                            value="<?= e($tags_string) ?>"
                            placeholder="Наприклад: PHP, Веб-розробка, Дизайн">
                        <small>Розділяйте теги комами</small>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3><i class="fas fa-info-circle"></i> Підказка</h3>
                    <div class="help-box">
                        <p><strong>Швидкі клавіші:</strong></p>
                        <ul class="help-list">
                            <li><kbd>Ctrl</kbd> + <kbd>S</kbd> - Зберегти</li>
                            <li><kbd>Ctrl</kbd> + <kbd>B</kbd> - Жирний</li>
                            <li><kbd>Ctrl</kbd> + <kbd>I</kbd> - Курсив</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="markdownModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Довідка Markdown</h2>
        <div class="markdown-help">
            <h3>Заголовки</h3>
            <pre># Заголовок 1
## Заголовок 2
### Заголовок 3</pre>
            
            <h3>Форматування тексту</h3>
            <pre>**Жирний текст**
*Курсив*
***Жирний курсив***</pre>
            
            <h3>Списки</h3>
            <pre>- Елемент списку
- Ще один елемент

1. Нумерований список
2. Другий пункт</pre>
            
            <h3>Посилання та зображення</h3>
            <pre>[Текст посилання](https://example.com)
![Alt текст](url-зображення.jpg)</pre>
            
            <h3>Цитати</h3>
            <pre>> Це цитата
> Може бути багаторядковою</pre>
            
            <h3>Код</h3>
            <pre>Inline `код` у тексті

```javascript
// Блок коду
const hello = "world";
```</pre>
        </div>
    </div>
</div>

<script>
// Markdown modal
document.getElementById('markdownHelp')?.addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('markdownModal').style.display = 'block';
});

document.querySelector('.modal-close')?.addEventListener('click', () => {
    document.getElementById('markdownModal').style.display = 'none';
});

// Ctrl+S для збереження
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('postForm').submit();
    }
});
</script>

<?php include __DIR__ . '/admin-footer.php'; ?>