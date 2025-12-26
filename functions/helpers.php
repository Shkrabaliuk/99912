<?php
require_once __DIR__ . '/../assets/libs/parsedown/Parsedown.php';

function getSiteSettings() {
    static $settings = null;
    if ($settings === null) {
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function getSetting($key, $default = '') {
    $settings = getSiteSettings();
    return $settings[$key] ?? $default;
}

function updateSetting($key, $value) {
    $db = Database::getInstance();
    $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
    if ($existing) {
        $db->execute("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
    } else {
        $db->execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
    }
    Cache::clear();
}

function parseMarkdown($text) {
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(true);
    return $parsedown->text($text);
}

function formatDate($datetime, $format = 'd.m.Y') {
    $months_uk = [
        1 => 'січня', 2 => 'лютого', 3 => 'березня', 4 => 'квітня',
        5 => 'травня', 6 => 'червня', 7 => 'липня', 8 => 'серпня',
        9 => 'вересня', 10 => 'жовтня', 11 => 'листопада', 12 => 'грудня'
    ];
    $timestamp = strtotime($datetime);
    $day = date('j', $timestamp);
    $month = $months_uk[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    return "{$day} {$month} {$year}";
}

function truncateText($text, $length = 200, $suffix = '...') {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

function generateUniqueSlug($title, $table = 'posts', $id = null) {
    $slug = Security::createSlug($title);
    $db = Database::getInstance();
    $original_slug = $slug;
    $counter = 1;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$slug];
        if ($id !== null) {
            $sql .= " AND id != ?";
            $params[] = $id;
        }
        $existing = $db->fetchOne($sql, $params);
        if (!$existing) {
            break;
        }
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    return $slug;
}

function redirect($url) {
    header("Location: {$url}");
    exit;
}

function siteUrl($path = '') {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function assetUrl($path) {
    return siteUrl('assets/' . ltrim($path, '/'));
}

function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function post($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function get($key, $default = '') {
    return $_GET[$key] ?? $default;
}

function pagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) {
        return '';
    }
    $html = '<div class="pagination">';
    if ($current_page > 1) {
        $prev_url = $base_url . ($current_page - 1 > 1 ? '?page=' . ($current_page - 1) : '');
        $html .= '<a href="' . $prev_url . '" class="pagination-link">&larr; Попередня</a>';
    }
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    for ($i = $start; $i <= $end; $i++) {
        $url = $base_url . ($i > 1 ? '?page=' . $i : '');
        $active = $i === $current_page ? ' active' : '';
        $html .= '<a href="' . $url . '" class="pagination-number' . $active . '">' . $i . '</a>';
    }
    if ($current_page < $total_pages) {
        $next_url = $base_url . '?page=' . ($current_page + 1);
        $html .= '<a href="' . $next_url . '" class="pagination-link">Наступна &rarr;</a>';
    }
    $html .= '</div>';
    return $html;
}

function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function formatViews($views) {
    if ($views >= 1000000) {
        return round($views / 1000000, 1) . 'M';
    } elseif ($views >= 1000) {
        return round($views / 1000, 1) . 'K';
    }
    return $views;
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) {
        return 'щойно';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' хв тому';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' год тому';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' дн тому';
    }
    return formatDate($datetime);
}