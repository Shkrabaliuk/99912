# Font Awesome Setup

## Варіант 1: CDN (Рекомендовано для швидкого старту)

Замініть у файлах шаблонів:

```php
<!-- Замість -->
<link rel="stylesheet" href="<?= assetUrl('libs/font-awesome/css/all.min.css') ?>">

<!-- Використовуйте CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

**Файли для зміни:**
1. `templates/header.php`
2. `admin/includes/admin-header.php`
3. `admin/login.php`
4. `install/index.php`

## Варіант 2: Локальна установка

1. Завантажте Font Awesome Free з: https://fontawesome.com/download
2. Розпакуйте архів
3. Скопіюйте:
   - `css/all.min.css` → `assets/libs/font-awesome/css/all.min.css`
   - `webfonts/` → `assets/libs/font-awesome/webfonts/`

Структура має бути:
```
assets/
  libs/
    font-awesome/
      css/
        all.min.css
      webfonts/
        fa-solid-900.woff2
        fa-brands-400.woff2
        ...
```

## Швидке виправлення (CDN)

Створіть файл `assets/libs/font-awesome/css/all.min.css`:

```css
/* Font Awesome CDN redirect */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
```

Це перенаправить всі запити на CDN автоматично.