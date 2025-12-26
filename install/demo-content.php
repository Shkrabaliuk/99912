<?php
/**
 * Вставка демонстраційного контенту
 */

function insertDemoContent($pdo) {
    // Теги
    $tags = [
        ['name' => 'Технології', 'slug' => 'tekhnolohii'],
        ['name' => 'Веб-розробка', 'slug' => 'veb-rozrobka'],
        ['name' => 'PHP', 'slug' => 'php'],
        ['name' => 'Дизайн', 'slug' => 'dyzain'],
        ['name' => 'SEO', 'slug' => 'seo']
    ];

    $tag_ids = [];
    foreach ($tags as $tag) {
        $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $stmt->execute([$tag['name'], $tag['slug']]);
        $tag_ids[$tag['slug']] = $pdo->lastInsertId();
    }

    // Пости
    $posts = [
        [
            'title' => 'Ласкаво просимо до нашого блогу',
            'slug' => 'laskavo-prosymo',
            'content' => "# Вітаємо на новому блозі!\n\nЦе **перший пост** у вашому новому блозі на власній CMS.\n\n## Можливості системи\n\n- Підтримка **Markdown** для форматування\n- Швидкий та зручний редактор\n- Система тегів\n- Коментарі з модерацією\n- SEO-оптимізація\n\n### Що далі?\n\nПочніть писати свої статті через адмін-панель. Налаштуйте дизайн, додайте логотип та favicon.\n\n> Це цитата. Markdown робить оформлення тексту простим і зручним.\n\nУспіхів у блогінгу!",
            'excerpt' => 'Перший пост у вашому новому блозі. Дізнайтеся про можливості системи.',
            'tags' => ['tekhnolohii']
        ],
        [
            'title' => 'Основи веб-розробки для початківців',
            'slug' => 'osnovy-veb-rozrobky',
            'content' => "# Основи веб-розробки\n\nВеб-розробка — це захоплююча сфера, яка поєднує креативність та технічні навички.\n\n## HTML, CSS, JavaScript\n\nЦі три технології — основа будь-якого веб-сайту:\n\n1. **HTML** — структура сторінки\n2. **CSS** — стилізація та дизайн\n3. **JavaScript** — інтерактивність\n\n```javascript\nconst greeting = 'Привіт, світ!';\nconsole.log(greeting);\n```\n\n## Backend-розробка\n\nДля створення динамічних сайтів потрібен серверний код. Популярні мови:\n\n- PHP\n- Python\n- Node.js\n- Ruby\n\nПочніть з основ і поступово розширюйте свої знання!",
            'excerpt' => 'Введення у світ веб-розробки. HTML, CSS, JavaScript та backend.',
            'tags' => ['veb-rozrobka', 'tekhnolohii']
        ],
        [
            'title' => 'Чому PHP досі актуальний у 2025 році',
            'slug' => 'chomu-php-dosi-aktualnyi',
            'content' => "# PHP у сучасному світі\n\nНезважаючи на появу нових технологій, PHP залишається одним з найпопулярніших мов для веб-розробки.\n\n## Статистика\n\nБільше **75% сайтів** в інтернеті використовують PHP на серверній частині.\n\n## Переваги PHP\n\n- Велика екосистема та спільнота\n- Безліч фреймворків (Laravel, Symfony)\n- Проста інтеграція з базами даних\n- Невисокий поріг входу\n\n### Сучасний PHP\n\nPHP 8+ приніс багато покращень:\n\n- JIT-компіляція\n- Типізація\n- Attributes\n- Match expressions\n\n**Висновок**: PHP еволюціонує і залишається потужним інструментом для веб-розробки.",
            'excerpt' => 'Чому PHP залишається актуальним та які його переваги у 2025 році.',
            'tags' => ['php', 'veb-rozrobka']
        ],
        [
            'title' => 'Принципи хорошого веб-дизайну',
            'slug' => 'pryntsypy-khoroshoho-dyzainu',
            'content' => "# Принципи веб-дизайну\n\nХороший дизайн — це не лише естетика, а й зручність використання.\n\n## Основні принципи\n\n### 1. Простота\n\nНе перевантажуйте інтерфейс. Користувач має легко знаходити потрібну інформацію.\n\n### 2. Послідовність\n\nВикористовуйте єдиний стиль на всіх сторінках сайту.\n\n### 3. Контраст\n\nТекст має бути добре читабельним на будь-якому тлі.\n\n### 4. Адаптивність\n\nСайт має коректно відображатися на всіх пристроях.\n\n## Типографіка\n\nОберіть **2-3 шрифти** максимум. Великі тексти повинні бути комфортними для читання.\n\n## Кольорова палітра\n\nОберіть основний колір та додайте 2-3 додаткових відтінків.",
            'excerpt' => 'Основні принципи створення зручного та красивого веб-дизайну.',
            'tags' => ['dyzain', 'veb-rozrobka']
        ],
        [
            'title' => 'SEO-оптимізація: з чого почати',
            'slug' => 'seo-optymizatsiia',
            'content' => "# Основи SEO\n\nПошукова оптимізація — ключ до успіху вашого сайту.\n\n## Технічне SEO\n\n- Швидкість завантаження\n- Адаптивність\n- Валідна структура HTML\n- HTTPS-протокол\n\n## Контент\n\nЯкісний контент — основа хорошого ранжування:\n\n1. Пишіть для людей, не для роботів\n2. Використовуйте ключові слова природно\n3. Структуруйте текст заголовками\n4. Додавайте зображення з alt-текстом\n\n## Meta-теги\n\n```html\n<title>Заголовок сторінки</title>\n<meta name=\"description\" content=\"Опис сторінки\">\n```\n\n## Внутрішня перелінковка\n\nЗвʼязуйте статті між собою для кращої навігації.\n\n**Важливо**: SEO — це довгострокова стратегія. Результати приходять з часом.",
            'excerpt' => 'Базові принципи SEO-оптимізації для початківців.',
            'tags' => ['seo', 'veb-rozrobka']
        ]
    ];

    foreach ($posts as $index => $post) {
        $published_at = date('Y-m-d H:i:s', strtotime("-{$index} days"));
        
        $stmt = $pdo->prepare(
            "INSERT INTO posts (title, slug, content, excerpt, status, published_at) 
             VALUES (?, ?, ?, ?, 'published', ?)"
        );
        $stmt->execute([
            $post['title'],
            $post['slug'],
            $post['content'],
            $post['excerpt'],
            $published_at
        ]);
        
        $post_id = $pdo->lastInsertId();
        
        // Додавання тегів до поста
        foreach ($post['tags'] as $tag_slug) {
            $stmt = $pdo->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $tag_ids[$tag_slug]]);
        }
    }

    // Сторінки
    $pages = [
        [
            'title' => 'Про мене',
            'slug' => 'pro-mene',
            'content' => "# Про автора блогу\n\nВітаю! Мене звати **Автор** і це мій особистий блог.\n\n## Чим я займаюся\n\nЯ пишу про веб-розробку, технології та дизайн. Ділюся своїм досвідом та корисними порадами.\n\n## Зв'яжіться зі мною\n\nЗалишайте коментарі під постами або пишіть на сторінці контактів.",
            'meta_description' => 'Інформація про автора блогу та його діяльність'
        ],
        [
            'title' => 'Контакти',
            'slug' => 'kontakty',
            'content' => "# Зв'язатися зі мною\n\nЯкщо у вас є питання або пропозиції, буду радий відповісти!\n\n## Email\n\nemail@example.com\n\n## Соціальні мережі\n\nВи можете знайти мене у соціальних мережах (посилання додайте самостійно).\n\n---\n\n*Зазвичай відповідаю протягом 24 годин.*",
            'meta_description' => 'Контактна інформація для звʼязку з автором блогу'
        ]
    ];

    foreach ($pages as $page) {
        $stmt = $pdo->prepare(
            "INSERT INTO pages (title, slug, content, meta_description, status) 
             VALUES (?, ?, ?, ?, 'published')"
        );
        $stmt->execute([
            $page['title'],
            $page['slug'],
            $page['content'],
            $page['meta_description']
        ]);
    }

    // Коментарі
    $comments = [
        [
            'post_slug' => 'laskavo-prosymo',
            'author_name' => 'Марія',
            'author_email' => 'maria@example.com',
            'content' => 'Чудовий пост! Дякую за інформацію.',
            'status' => 'approved'
        ],
        [
            'post_slug' => 'osnovy-veb-rozrobky',
            'author_name' => 'Олександр',
            'author_email' => 'oleksandr@example.com',
            'content' => 'Дуже корисна стаття для початківців. Продовжуйте у тому ж дусі!',
            'status' => 'approved'
        ],
        [
            'post_slug' => 'chomu-php-dosi-aktualnyi',
            'author_name' => 'Іван',
            'author_email' => 'ivan@example.com',
            'content' => 'Згоден! PHP 8 дійсно приніс багато покращень.',
            'status' => 'approved'
        ]
    ];

    foreach ($comments as $comment) {
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
        $stmt->execute([$comment['post_slug']]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            $stmt = $pdo->prepare(
                "INSERT INTO comments (post_id, author_name, author_email, content, status, approved_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $post['id'],
                $comment['author_name'],
                $comment['author_email'],
                $comment['content'],
                $comment['status']
            ]);
        }
    }
}