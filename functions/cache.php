<?php
class Cache {
    private static $cache_dir = __DIR__ . '/../cache/';
    
    public static function get($key) {
        if (!CACHE_ENABLED) {
            return null;
        }
        $filename = self::getFilename($key);
        if (!file_exists($filename)) {
            return null;
        }
        $data = unserialize(file_get_contents($filename));
        if ($data['expires'] < time()) {
            self::delete($key);
            return null;
        }
        return $data['content'];
    }
    
    public static function set($key, $content, $ttl = null) {
        if (!CACHE_ENABLED) {
            return false;
        }
        if ($ttl === null) {
            $ttl = CACHE_TTL;
        }
        $filename = self::getFilename($key);
        $data = [
            'content' => $content,
            'expires' => time() + $ttl
        ];
        return file_put_contents($filename, serialize($data)) !== false;
    }
    
    public static function delete($key) {
        $filename = self::getFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }
    
    public static function clear() {
        $files = glob(self::$cache_dir . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
    
    private static function getFilename($key) {
        $hash = md5($key);
        return self::$cache_dir . $hash . '.cache';
    }
}