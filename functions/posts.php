<?php
function getPublishedPosts($limit = 10, $offset = 0) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
}

function getTotalPublishedPosts() {
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM posts WHERE status = 'published'");
    return (int)$result['count'];
}

function getPostBySlug($slug) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM posts WHERE slug = ?", [$slug]);
}

function getPostById($id) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
}

function createPost($data) {
    // Validate input
    $validator = Validator::validatePost($data);
    if ($validator->fails()) {
        throw new ValidationException($validator->getErrors());
    }
    
    $db = Database::getInstance();
    $slug = generateUniqueSlug($data['title'], 'posts');
    $published_at = $data['status'] === 'published' ? date('Y-m-d H:i:s') : null;
    
    try {
        $db->execute(
            "INSERT INTO posts (title, slug, content, excerpt, status, published_at) VALUES (?, ?, ?, ?, ?, ?)",
            [$data['title'], $slug, $data['content'], $data['excerpt'] ?? '', $data['status'], $published_at]
        );
        $post_id = $db->lastInsertId();
        
        if (!empty($data['tags'])) {
            attachTagsToPost($post_id, $data['tags']);
        }
        
        Cache::clear();
        Logger::info('Post created', ['post_id' => $post_id, 'title' => $data['title']]);
        
        return $post_id;
    } catch (DatabaseException $e) {
        Logger::error('Failed to create post', ['error' => $e->getMessage(), 'data' => $data]);
        throw $e;
    }
}

function updatePost($id, $data) {
    // Validate input
    $validator = Validator::validatePost($data);
    if ($validator->fails()) {
        throw new ValidationException($validator->getErrors());
    }
    
    $db = Database::getInstance();
    $post = getPostById($id);
    if (!$post) {
        Logger::warning('Attempted to update non-existent post', ['post_id' => $id]);
        return false;
    }
    
    $slug = $post['slug'];
    if ($data['title'] !== $post['title']) {
        $slug = generateUniqueSlug($data['title'], 'posts', $id);
    }
    $published_at = $post['published_at'];
    if ($data['status'] === 'published' && $post['status'] !== 'published') {
        $published_at = date('Y-m-d H:i:s');
    }
    
    try {
        $db->execute(
            "UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, status = ?, published_at = ?, updated_at = NOW() WHERE id = ?",
            [$data['title'], $slug, $data['content'], $data['excerpt'] ?? '', $data['status'], $published_at, $id]
        );
        
        if (isset($data['tags'])) {
            detachAllTagsFromPost($id);
            if (!empty($data['tags'])) {
                attachTagsToPost($id, $data['tags']);
            }
        }
        
        Cache::clear();
        Logger::info('Post updated', ['post_id' => $id, 'title' => $data['title']]);
        
        return true;
    } catch (DatabaseException $e) {
        Logger::error('Failed to update post', ['error' => $e->getMessage(), 'post_id' => $id]);
        throw $e;
    }
}

function deletePost($id) {
    $db = Database::getInstance();
    $db->execute("DELETE FROM posts WHERE id = ?", [$id]);
    Cache::clear();
}

function incrementPostViews($id) {
    $db = Database::getInstance();
    $db->execute("UPDATE posts SET views = views + 1 WHERE id = ?", [$id]);
}

function getAllPosts($limit = 50, $offset = 0, $status = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM posts";
    $params = [];
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    return $db->fetchAll($sql, $params);
}

function getTotalPosts($status = null) {
    $db = Database::getInstance();
    $sql = "SELECT COUNT(*) as count FROM posts";
    $params = [];
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    $result = $db->fetchOne($sql, $params);
    return (int)$result['count'];
}

function searchPosts($query, $limit = 10, $offset = 0) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM posts WHERE status = 'published' AND MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE) ORDER BY published_at DESC LIMIT ? OFFSET ?",
        [$query, $limit, $offset]
    );
}

function countSearchResults($query) {
    $db = Database::getInstance();
    $result = $db->fetchOne(
        "SELECT COUNT(*) as count FROM posts WHERE status = 'published' AND MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE)",
        [$query]
    );
    return (int)$result['count'];
}

function getPostsByTag($tag_id, $limit = 10, $offset = 0) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT p.* FROM posts p INNER JOIN post_tags pt ON p.id = pt.post_id WHERE p.status = 'published' AND pt.tag_id = ? ORDER BY p.published_at DESC LIMIT ? OFFSET ?",
        [$tag_id, $limit, $offset]
    );
}

function countPostsByTag($tag_id) {
    $db = Database::getInstance();
    $result = $db->fetchOne(
        "SELECT COUNT(*) as count FROM posts p INNER JOIN post_tags pt ON p.id = pt.post_id WHERE p.status = 'published' AND pt.tag_id = ?",
        [$tag_id]
    );
    return (int)$result['count'];
}

function getPopularPosts($limit = 5) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT ?",
        [$limit]
    );
}

function getRecentPosts($limit = 5, $exclude_id = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM posts WHERE status = 'published'";
    $params = [];
    if ($exclude_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }
    $sql .= " ORDER BY published_at DESC LIMIT ?";
    $params[] = $limit;
    return $db->fetchAll($sql, $params);
}