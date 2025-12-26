<?php
function generateSitemap() {
    $db = Database::getInstance();
    $base_url = rtrim(SITE_URL, '/');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $xml .= '  <url><loc>' . $base_url . '/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n";
    $posts = $db->fetchAll("SELECT slug, updated_at FROM posts WHERE status = 'published' ORDER BY updated_at DESC");
    foreach ($posts as $post) {
        $xml .= '  <url><loc>' . $base_url . '/' . $post['slug'] . '</loc><lastmod>' . date('Y-m-d', strtotime($post['updated_at'])) . '</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>' . "\n";
    }
    $pages = $db->fetchAll("SELECT slug, updated_at FROM pages WHERE status = 'published'");
    foreach ($pages as $page) {
        $xml .= '  <url><loc>' . $base_url . '/' . $page['slug'] . '</loc><lastmod>' . date('Y-m-d', strtotime($page['updated_at'])) . '</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>' . "\n";
    }
    $tags = $db->fetchAll("SELECT slug FROM tags");
    foreach ($tags as $tag) {
        $xml .= '  <url><loc>' . $base_url . '/tag/' . $tag['slug'] . '</loc><changefreq>weekly</changefreq><priority>0.5</priority></url>' . "\n";
    }
    $xml .= '</urlset>';
    return $xml;
}

function generateRSS() {
    $db = Database::getInstance();
    $settings = getSiteSettings();
    $base_url = rtrim(SITE_URL, '/');
    $site_name = e($settings['site_name'] ?? 'Блог');
    $site_description = e($settings['site_description'] ?? '');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
    $xml .= '<channel>' . "\n";
    $xml .= '  <title>' . $site_name . '</title>' . "\n";
    $xml .= '  <link>' . $base_url . '</link>' . "\n";
    $xml .= '  <description>' . $site_description . '</description>' . "\n";
    $xml .= '  <language>uk</language>' . "\n";
    $xml .= '  <atom:link href="' . $base_url . '/rss" rel="self" type="application/rss+xml" />' . "\n";
    $posts = $db->fetchAll("SELECT * FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 20");
    foreach ($posts as $post) {
        $post_url = $base_url . '/' . $post['slug'];
        $description = !empty($post['excerpt']) ? e($post['excerpt']) : e(truncateText($post['content'], 200));
        $xml .= '  <item>' . "\n";
        $xml .= '    <title>' . e($post['title']) . '</title>' . "\n";
        $xml .= '    <link>' . $post_url . '</link>' . "\n";
        $xml .= '    <guid>' . $post_url . '</guid>' . "\n";
        $xml .= '    <description>' . $description . '</description>' . "\n";
        $xml .= '    <pubDate>' . date('r', strtotime($post['published_at'])) . '</pubDate>' . "\n";
        $tags = getPostTags($post['id']);
        foreach ($tags as $tag) {
            $xml .= '    <category>' . e($tag['name']) . '</category>' . "\n";
        }
        $xml .= '  </item>' . "\n";
    }
    $xml .= '</channel>' . "\n";
    $xml .= '</rss>';
    return $xml;
}

function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function getCanonicalUrl() {
    $url = getCurrentUrl();
    $parsed = parse_url($url);
    $canonical = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
    return rtrim($canonical, '/');
}