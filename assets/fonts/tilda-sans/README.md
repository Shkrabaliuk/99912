# Tilda Sans Font Setup

## Статус
Шрифт Tilda Sans не включений у базову поставку CMS.
Система автоматично використовує fallback шрифти.

## Fallback шрифти (за замовчуванням)
```css
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 
             'Helvetica Neue', Arial, sans-serif;
```

Це забезпечує якісне відображення на всіх пристроях:
- **macOS/iOS**: San Francisco (система Apple)
- **Windows**: Segoe UI
- **Android**: Roboto
- **Старіші системи**: Arial

## Якщо хочете використати Tilda Sans

### Варіант 1: Google Fonts (найпростіше)
Додайте в `templates/header.php` перед закриваючим `</head>`:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;700&display=swap" rel="stylesheet">
```

Змініть у `assets/css/style.css`:
```css
--font-main: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, sans-serif;
```

### Варіант 2: Локальні файли
1. Отримайте файли .woff2 шрифту Tilda Sans
2. Розмістіть у:
   ```
   assets/fonts/tilda-sans/
     TildaSans-Regular.woff2
     TildaSans-Medium.woff2
     TildaSans-Bold.woff2
   ```
3. Файл `assets/css/style.css` вже містить @font-face правила

## Рекомендація
Залиште як є - fallback шрифти працюють чудово і не потребують завантаження додаткових файлів.