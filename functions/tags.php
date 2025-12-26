<?php
function getAllTags() {
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM tags ORDER BY name ASC");
}

function getTagBySlug($slug) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM tags WHERE slug = ?", [$slug]);
}

function getTagById($id) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM tags WHERE id = ?", [$id]);
}

function getPostTags($post_id) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT t.* FROM tags t INNER JOIN post_tags pt ON t.id = pt.tag_id WHERE pt.post_id = ? ORDER BY t.name ASC",
        [$post_id]
    );
}

function getOrCreateTag($name) {
    $db = Database::getInstance();
    $slug = Security::createSlug($name);
    $existing = $db->fetchOne("SELECT * FROM tags WHERE slug = ?", [$slug]);
    if ($existing) {
        return $existing['id'];
    }
    $db->execute("INSERT INTO tags (name, slug) VALUES (?, ?)", [$name, $slug]);
    return $db->lastInsertId();
}

function attachTagsToPost($post_id, $tag_names) {
    if (empty($tag_names)) {
        return;
    }
    $db = Database::getInstance();
    foreach ($tag_names as $tag_name) {
        $tag_name = trim($tag_name);
        if (empty($tag_name)) {
            continue;
        }
        $tag_id = getOrCreateTag($tag_name);
        $existing = $db->fetchOne(
            "SELECT * FROM post_tags WHERE post_id = ? AND tag_id = ?",
            [$post_id, $tag_id]
        );
        if (!$existing) {
            $db->execute("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)", [$post_id, $tag_id]);
        }
    }
}

function detachAllTagsFromPost($post_id) {
    $db = Database::getInstance();
    $db->execute("DELETE FROM post_tags WHERE post_id = ?", [$post_id]);
}

function getPopularTags($limit = 10) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT t.*, COUNT(pt.post_id) as post_count FROM tags t INNER JOIN post_tags pt ON t.id = pt.tag_id INNER JOIN posts p ON pt.post_id = p.id WHERE p.status = 'published' GROUP BY t.id ORDER BY post_count DESC LIMIT ?",
        [$limit]
    );
}

function deleteTag($id) {
    $db = Database::getInstance();
    $db->execute("DELETE FROM tags WHERE id = ?", [$id]);
    Cache::clear();
}