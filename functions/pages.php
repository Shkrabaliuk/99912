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
    // Validate input
    $validator = Validator::validatePage($data);
    if ($validator->fails()) {
        throw new ValidationException($validator->getErrors());
    }
    
    $db = Database::getInstance();
    $slug = generateUniqueSlug($data['title'], 'pages');
    
    try {
        $db->execute(
            "INSERT INTO pages (title, slug, content, meta_description, status) VALUES (?, ?, ?, ?, ?)",
            [$data['title'], $slug, $data['content'], $data['meta_description'] ?? '', $data['status'] ?? 'published']
        );
        
        $page_id = $db->lastInsertId();
        Cache::clear();
        Logger::info('Page created', ['page_id' => $page_id, 'title' => $data['title']]);
        
        return $page_id;
    } catch (DatabaseException $e) {
        Logger::error('Failed to create page', ['error' => $e->getMessage(), 'data' => $data]);
        throw $e;
    }
}

function updatePage($id, $data) {
    // Validate input
    $validator = Validator::validatePage($data);
    if ($validator->fails()) {
        throw new ValidationException($validator->getErrors());
    }
    
    $db = Database::getInstance();
    $page = getPageById($id);
    if (!$page) {
        Logger::warning('Attempted to update non-existent page', ['page_id' => $id]);
        return false;
    }
    
    $slug = $page['slug'];
    if ($data['title'] !== $page['title']) {
        $slug = generateUniqueSlug($data['title'], 'pages', $id);
    }
    
    try {
        $db->execute(
            "UPDATE pages SET title = ?, slug = ?, content = ?, meta_description = ?, status = ?, updated_at = NOW() WHERE id = ?",
            [$data['title'], $slug, $data['content'], $data['meta_description'] ?? '', $data['status'] ?? 'published', $id]
        );
        
        Cache::clear();
        Logger::info('Page updated', ['page_id' => $id, 'title' => $data['title']]);
        
        return true;
    } catch (DatabaseException $e) {
        Logger::error('Failed to update page', ['error' => $e->getMessage(), 'page_id' => $id]);
        throw $e;
    }
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