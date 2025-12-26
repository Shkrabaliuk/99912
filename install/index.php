<?php
/**
 * Installer CMS
 * Автоматичне встановлення та налаштування системи
 */

session_start();

// Перевірка чи вже встановлено
if (file_exists('../config.php')) {
    die('CMS вже встановлено. Видаліть файл config.php для перевстановлення.');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];

// Крок 1: Перевірка системи
if ($step === 1) {
    // Перевірка версії PHP
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'Потрібна версія PHP 7.4 або вища. Поточна: ' . PHP_VERSION;
    }

    // Перевірка розширень
    $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Відсутнє розширення PHP: {$ext}";
        }
    }

    // Перевірка прав на запис
    $writable_dirs = ['../uploads', '../cache'];
    foreach ($writable_dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!is_writable($dir)) {
            $errors[] = "Відсутні права на запис у директорію: {$dir}";
        }
    }
}

// Крок 2: Підключення до БД та створення таблиць
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';

    try {
        // Підключення без вибору БД
        $pdo = new PDO(
            "mysql:host={$db_host};charset=utf8mb4",
            $db_user,
            $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Створення БД
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db_name}`");

        // Створення таблиць
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $pdo->exec($sql);

        // Збереження даних у сесії
        $_SESSION['install_db'] = [
            'host' => $db_host,
            'name' => $db_name,
            'user' => $db_user,
            'pass' => $db_pass
        ];

        header('Location: ?step=3');
        exit;
    } catch (PDOException $e) {
        $errors[] = 'Помилка підключення до БД: ' . $e->getMessage();
    }
}

// Крок 3: Налаштування сайту
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? '');
    $site_description = trim($_POST['site_description'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $demo_content = isset($_POST['demo_content']);

    if (empty($site_name) || empty($admin_password)) {
        $errors[] = 'Заповніть всі обовʼязкові поля';
    } elseif (strlen($admin_password) < 8) {
        $errors[] = 'Пароль має містити мінімум 8 символів';
    } else {
        $_SESSION['install_settings'] = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'admin_password' => $admin_password,
            'demo_content' => $demo_content
        ];

        header('Location: ?step=4');
        exit;
    }
}

// Крок 4: Завершення встановлення
if ($step === 4) {
    $db = $_SESSION['install_db'];
    $settings = $_SESSION['install_settings'];

    try {
        $pdo = new PDO(
            "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
            $db['user'],
            $db['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Збереження налаштувань
        $hashed_password = password_hash($settings['admin_password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute(['site_name', $settings['site_name']]);
        $stmt->execute(['site_description', $settings['site_description']]);
        $stmt->execute(['admin_password', $hashed_password]);
        $stmt->execute(['posts_per_page', '10']);
        $stmt->execute(['theme_color', '#2c3e50']);

        // Демо-контент
        if ($settings['demo_content']) {
            require_once __DIR__ . '/demo-content.php';
            insertDemoContent($pdo);
        }

        // Визначення базового URL з урахуванням піддиректорії
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // Визначаємо базовий шлях: беремо поточний REQUEST_URI, видаляємо /install/...
        $request_uri = $_SERVER['REQUEST_URI'];
        $base_path = '';
        if (preg_match('#^(.*?)/install/#', $request_uri, $matches)) {
            $base_path = $matches[1];
        }
        
        // Валідація базового шляху для безпеки
        // Дозволяємо тільки алфавітно-цифрові символи, підкреслення, дефіси та слеші
        // Переконуємось, що шлях починається з / або є порожнім
        if ($base_path !== '' && (!preg_match('#^/[a-zA-Z0-9_/-]*$#', $base_path) || strpos($base_path, '..') !== false)) {
            $errors[] = 'Невірний шлях встановлення. Будь ласка, встановлюйте систему в стандартну директорію.';
            $base_path = '';
        }
        
        $site_url = $protocol . '://' . $host . $base_path;
        
        // Створення config.php
        $config_content = "<?php\n";
        $config_content .= "define('DB_HOST', '" . addslashes($db['host']) . "');\n";
        $config_content .= "define('DB_NAME', '" . addslashes($db['name']) . "');\n";
        $config_content .= "define('DB_USER', '" . addslashes($db['user']) . "');\n";
        $config_content .= "define('DB_PASS', '" . addslashes($db['pass']) . "');\n";
        $config_content .= "define('SITE_URL', '" . addslashes($site_url) . "');\n";
        $config_content .= "define('CACHE_ENABLED', true);\n";
        $config_content .= "define('CACHE_TTL', 3600);\n";

        file_put_contents('../config.php', $config_content);

        // Видалення installer
        $_SESSION['install_complete'] = true;
        header('Location: ?step=5');
        exit;
    } catch (Exception $e) {
        $errors[] = 'Помилка встановлення: ' . $e->getMessage();
        $step = 3;
    }
}

// Крок 5: Завершено
if ($step === 5 && isset($_SESSION['install_complete'])) {
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення CMS</title>
    <link rel="stylesheet" href="../assets/css/installer.css">
</head>
<body>
    <div class="installer-container">
        <h1>Встановлення CMS</h1>
        
        <div class="progress-bar">
            <div class="progress-step <?= $step >= 1 ? 'active' : '' ?>">1</div>
            <div class="progress-step <?= $step >= 2 ? 'active' : '' ?>">2</div>
            <div class="progress-step <?= $step >= 3 ? 'active' : '' ?>">3</div>
            <div class="progress-step <?= $step >= 4 ? 'active' : '' ?>">4</div>
            <div class="progress-step <?= $step >= 5 ? 'active' : '' ?>">5</div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <div class="step-content">
                <h2>Крок 1: Перевірка системи</h2>
                <?php if (empty($errors)): ?>
                    <p class="success">✓ Всі перевірки пройдені успішно</p>
                    <a href="?step=2" class="btn">Продовжити</a>
                <?php else: ?>
                    <p class="error">Виправте помилки перед продовженням</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 2): ?>
            <div class="step-content">
                <h2>Крок 2: Налаштування бази даних</h2>
                <form method="post">
                    <label>
                        Хост БД:
                        <input type="text" name="db_host" value="localhost" required>
                    </label>
                    <label>
                        Назва БД:
                        <input type="text" name="db_name" required>
                    </label>
                    <label>
                        Користувач БД:
                        <input type="text" name="db_user" required>
                    </label>
                    <label>
                        Пароль БД:
                        <input type="password" name="db_pass">
                    </label>
                    <button type="submit" class="btn">Створити базу даних</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($step === 3): ?>
            <div class="step-content">
                <h2>Крок 3: Налаштування сайту</h2>
                <form method="post">
                    <label>
                        Назва сайту:
                        <input type="text" name="site_name" required>
                    </label>
                    <label>
                        Опис сайту:
                        <textarea name="site_description" rows="3"></textarea>
                    </label>
                    <label>
                        Пароль адміністратора (мінімум 8 символів):
                        <input type="password" name="admin_password" required minlength="8">
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="demo_content" checked>
                        Завантажити демонстраційний контент
                    </label>
                    <button type="submit" class="btn">Завершити встановлення</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($step === 4): ?>
            <div class="step-content">
                <h2>Крок 4: Встановлення...</h2>
                <p>Зачекайте, виконується налаштування системи...</p>
            </div>
        <?php endif; ?>

        <?php if ($step === 5): ?>
            <div class="step-content">
                <h2>Встановлення завершено!</h2>
                <p class="success">✓ CMS успішно встановлено</p>
                <p>Ви можете увійти до адмін-панелі за адресою: <strong>/admin</strong></p>
                <a href="../" class="btn">Перейти на сайт</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>