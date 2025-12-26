<?php
function getPageBySlug($slug) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM pages WHERE slug = ?", [$slug]);
}

function getPageById($id) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM pages WHERE id = ?", [$id]);
}

function getAllPublishedPages() {
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM pages WHERE status = 'published' ORDER BY title ASC");
}

function getAllPages() {
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM pages ORDER BY created_at DESC");
}

function createPage($data) {
    $db = Database::getInstance();
    $slug = generateUniqueSlug($data['title'], 'pages');
    $db->execute(
        "INSERT INTO pages (title, slug, content, meta_description, status) VALUES (?, ?, ?, ?, ?)",
        [$data['title'], $slug, $data['content'], $data['meta_description'] ?? '', $data['status'] ?? 'published']
    );
    Cache::clear();
    return $db->lastInsertId();
}

function updatePage($id, $data) {
    $db = Database::getInstance();
    $page = getPageById($id);
    if (!$page) return false;
    $slug = $page['slug'];
    if ($data['title'] !== $page['title']) {
        $slug = generateUniqueSlug($data['title'], 'pages', $id);
    }
    $db->execute(
        "UPDATE pages SET title = ?, slug = ?, content = ?, meta_description = ?, status = ?, updated_at = NOW() WHERE id = ?",
        [$data['title'], $slug, $data['content'], $data['meta_description'] ?? '', $data['status'] ?? 'published', $id]
    );
    Cache::clear();
    return true;
}

function deletePage($id) {
    $db = Database::getInstance();
    $db->execute("DELETE FROM pages WHERE id = ?", [$id]);
    Cache::clear();
}

function getTotalPages() {
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM pages");
    return (int)$result['count'];
}