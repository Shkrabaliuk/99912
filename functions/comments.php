<?php
function getApprovedComments($post_id) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC",
        [$post_id]
    );
}

function getApprovedCommentsCount($post_id) {
    $db = Database::getInstance();
    $result = $db->fetchOne(
        "SELECT COUNT(*) as count FROM comments WHERE post_id = ? AND status = 'approved'",
        [$post_id]
    );
    return (int)$result['count'];
}

function addComment($post_id, $data) {
    $db = Database::getInstance();
    if (empty($data['author_name']) || empty($data['author_email']) || empty($data['content'])) {
        return false;
    }
    if (!Security::validateEmail($data['author_email'])) {
        return false;
    }
    if (!Security::checkHoneypot()) {
        return false;
    }
    if (!Security::checkFormTiming()) {
        return false;
    }
    $db->execute(
        "INSERT INTO comments (post_id, author_name, author_email, content, status, ip_address, user_agent) VALUES (?, ?, ?, ?, 'pending', ?, ?)",
        [$post_id, trim($data['author_name']), trim($data['author_email']), trim($data['content']), Security::getClientIP(), $_SERVER['HTTP_USER_AGENT'] ?? '']
    );
    Cache::clear();
    return $db->lastInsertId();
}

function approveComment($id) {
    $db = Database::getInstance();
    $db->execute("UPDATE comments SET status = 'approved', approved_at = NOW() WHERE id = ?", [$id]);
    Cache::clear();
}

function markAsSpam($id) {
    $db = Database::getInstance();
    $db->execute("UPDATE comments SET status = 'spam' WHERE id = ?", [$id]);
    Cache::clear();
}

function deleteComment($id) {
    $db = Database::getInstance();
    $db->execute("DELETE FROM comments WHERE id = ?", [$id]);
    Cache::clear();
}

function getAllComments($status = null, $limit = 50, $offset = 0) {
    $db = Database::getInstance();
    $sql = "SELECT c.*, p.title as post_title, p.slug as post_slug FROM comments c INNER JOIN posts p ON c.post_id = p.id";
    $params = [];
    if ($status) {
        $sql .= " WHERE c.status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    return $db->fetchAll($sql, $params);
}

function getTotalComments($status = null) {
    $db = Database::getInstance();
    $sql = "SELECT COUNT(*) as count FROM comments";
    $params = [];
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    $result = $db->fetchOne($sql, $params);
    return (int)$result['count'];
}

function getPendingCommentsCount() {
    return getTotalComments('pending');
}

function getCommentById($id) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM comments WHERE id = ?", [$id]);
}