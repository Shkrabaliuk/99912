<?php
/**
 * Логін адміністратора
 */

$error = '';

if (isPost()) {
    $password = post('password');
    
    // Rate limiting
    $ip = Security::getClientIP();
    if (!Security::checkLoginAttempts($ip)) {
        Logger::warning('Login blocked due to too many attempts', ['ip' => $ip]);
        $error = 'Забагато спроб входу. Спробуйте пізніше.';
    } else {
        $settings = getSiteSettings();
        $admin_password = $settings['admin_password'] ?? '';
        
        if (Security::verifyPassword($password, $admin_password)) {
            Security::clearLoginAttempts($ip);
            Session::loginAdmin();
            Logger::info('Admin login successful', ['ip' => $ip]);
            redirect('/admin');
        } else {
            Security::recordLoginAttempt($ip);
            Logger::warning('Failed login attempt', ['ip' => $ip]);
            $error = 'Невірний пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в адмін-панель</title>
    <link rel="stylesheet" href="<?= assetUrl('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('libs/font-awesome/css/all.min.css') ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>
                <i class="fas fa-lock"></i>
                Адмін-панель
            </h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="post" class="login-form">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i> Пароль
                    </label>
                    <input type="password" id="password" name="password" required autofocus>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Увійти
                </button>
            </form>
            
            <div class="login-footer">
                <a href="<?= siteUrl() ?>">
                    <i class="fas fa-arrow-left"></i> Повернутися на сайт
                </a>
            </div>
        </div>
    </div>
</body>
</html>