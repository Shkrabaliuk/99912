<?php
/**
 * Налаштування сайту
 */

if (isPost()) {
    try {
        // Валідація даних
        $data = [
            'site_name' => post('site_name'),
            'posts_per_page' => post('posts_per_page'),
            'new_password' => post('new_password')
        ];
        
        $validator = Validator::validateSettings($data);
        if ($validator->fails()) {
            throw new ValidationException($validator->getErrors());
        }
        
        // Оновлення основних налаштувань
        updateSetting('site_name', post('site_name'));
        updateSetting('site_description', post('site_description'));
        updateSetting('posts_per_page', post('posts_per_page'));
        updateSetting('theme_color', post('theme_color'));
        updateSetting('google_analytics', post('google_analytics'));
        
        // Зміна пароля
        $new_password = post('new_password');
        if (!empty($new_password)) {
            $hashed = Security::hashPassword($new_password);
            updateSetting('admin_password', $hashed);
            Session::flash('password_changed', true);
            Logger::info('Admin password changed');
        }
        
        Session::flash('success', 'Налаштування успішно збережено');
        Logger::info('Settings updated');
        
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
    
    redirect('/admin/settings');
}

$settings = getSiteSettings();

// Доступні кольори
$colors = [
    '#2c3e50' => 'Темно-синій (за замовчуванням)',
    '#27ae60' => 'Зелений',
    '#e74c3c' => 'Червоний',
    '#3498db' => 'Блакитний',
    '#9b59b6' => 'Фіолетовий',
    '#f39c12' => 'Помаранчевий',
    '#34495e' => 'Сірий'
];

include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-container">
    <h1><i class="fas fa-cog"></i> Налаштування сайту</h1>
    
    <?php if (Session::flash('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= e(Session::flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <?php if (Session::flash('error')): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= e(Session::flash('error')) ?>
        </div>
    <?php endif; ?>
    
    <?php if (Session::flash('password_changed')): ?>
        <div class="alert alert-success">
            <i class="fas fa-lock"></i>
            Пароль адміністратора успішно змінено!
        </div>
    <?php endif; ?>
    
    <form method="post" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        
        <div class="settings-section">
            <h2><i class="fas fa-info-circle"></i> Основні налаштування</h2>
            
            <div class="form-group">
                <label for="site_name">Назва сайту *</label>
                <input 
                    type="text" 
                    id="site_name" 
                    name="site_name" 
                    value="<?= e($settings['site_name'] ?? '') ?>" 
                    required>
                <small>Відображається в заголовку та логотипі</small>
            </div>
            
            <div class="form-group">
                <label for="site_description">Опис сайту</label>
                <textarea 
                    id="site_description" 
                    name="site_description" 
                    rows="3"><?= e($settings['site_description'] ?? '') ?></textarea>
                <small>Використовується в meta-тегах та на головній сторінці</small>
            </div>
            
            <div class="form-group">
                <label for="posts_per_page">Кількість постів на сторінці</label>
                <input 
                    type="number" 
                    id="posts_per_page" 
                    name="posts_per_page" 
                    value="<?= e($settings['posts_per_page'] ?? 10) ?>" 
                    min="1" 
                    max="50">
                <small>Від 1 до 50 постів</small>
            </div>
        </div>
        
        <div class="settings-section">
            <h2><i class="fas fa-palette"></i> Дизайн</h2>
            
            <div class="form-group">
                <label for="theme_color">Основний колір теми</label>
                <select id="theme_color" name="theme_color" class="color-select">
                    <?php foreach ($colors as $color => $name): ?>
                        <option 
                            value="<?= $color ?>" 
                            <?= ($settings['theme_color'] ?? '#2c3e50') === $color ? 'selected' : '' ?>
                            data-color="<?= $color ?>">
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="color-preview" style="background-color: <?= e($settings['theme_color'] ?? '#2c3e50') ?>"></div>
            </div>
        </div>
        
        <div class="settings-section">
            <h2><i class="fas fa-chart-line"></i> SEO та аналітика</h2>
            
            <div class="form-group">
                <label for="google_analytics">Google Analytics ID</label>
                <input 
                    type="text" 
                    id="google_analytics" 
                    name="google_analytics" 
                    value="<?= e($settings['google_analytics'] ?? '') ?>" 
                    placeholder="G-XXXXXXXXXX">
                <small>Наприклад: G-XXXXXXXXXX (необовʼязково)</small>
            </div>
            
            <div class="info-box">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>SEO автоматично налаштовано</strong>
                    <p>Ваш сайт підтримує:</p>
                    <ul>
                        <li>Автоматичний sitemap.xml: <a href="<?= siteUrl('sitemap.xml') ?>" target="_blank">переглянути</a></li>
                        <li>RSS feed: <a href="<?= siteUrl('rss') ?>" target="_blank">переглянути</a></li>
                        <li>ЧПУ (людинозрозумілі URL)</li>
                        <li>Meta-теги та Open Graph</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="settings-section">
            <h2><i class="fas fa-lock"></i> Безпека</h2>
            
            <div class="form-group">
                <label for="new_password">Новий пароль адміністратора</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    minlength="8"
                    placeholder="Залиште порожнім, щоб не змінювати">
                <small>Мінімум 8 символів. Пароль буде зашифрований.</small>
            </div>
            
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Важливо!</strong>
                    <p>Зберігайте новий пароль у безпечному місці. Відновити його буде можливо лише через базу даних.</p>
                </div>
            </div>
        </div>
        
        <div class="settings-section">
            <h2><i class="fas fa-database"></i> Системна інформація</h2>
            
            <div class="system-info">
                <div class="info-row">
                    <span class="info-label">Версія PHP:</span>
                    <span class="info-value"><?= PHP_VERSION ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">База даних:</span>
                    <span class="info-value">MySQL</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Кешування:</span>
                    <span class="info-value">
                        <?= CACHE_ENABLED ? 'Увімкнено (TTL: ' . (CACHE_TTL / 60) . ' хв)' : 'Вимкнено' ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Розмір кешу:</span>
                    <span class="info-value">
                        <?php
                        $cache_files = glob(__DIR__ . '/../../cache/*.cache');
                        $cache_size = 0;
                        foreach ($cache_files as $file) {
                            $cache_size += filesize($file);
                        }
                        echo $cache_size > 0 ? round($cache_size / 1024, 2) . ' KB' : '0 KB';
                        ?>
                    </span>
                </div>
            </div>
            
            <button type="button" onclick="clearCache()" class="btn" style="margin-top: 1rem;">
                <i class="fas fa-broom"></i> Очистити кеш
            </button>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-save"></i> Зберегти всі налаштування
            </button>
        </div>
    </form>
</div>

<script>
// Preview кольору теми
document.getElementById('theme_color')?.addEventListener('change', function() {
    const preview = document.querySelector('.color-preview');
    if (preview) {
        preview.style.backgroundColor = this.value;
    }
});

// Очищення кешу
function clearCache() {
    if (confirm('Очистити весь кеш? Це може тимчасово уповільнити сайт.')) {
        fetch('/admin/clear-cache', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Кеш успішно очищено!');
                    location.reload();
                }
            })
            .catch(error => {
                alert('Помилка очищення кешу');
            });
    }
}
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>