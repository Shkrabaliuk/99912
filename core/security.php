<?php
class Security {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            session_start();
        }
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    public static function sanitizeHTML($html) {
        return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
    }
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    public static function checkLoginAttempts($ip, $max = 5, $window = 900) {
        $db = Database::getInstance();
        $db->execute("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? SECOND)", [$window]);
        $result = $db->fetchOne("SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)", [$ip, $window]);
        return $result['count'] < $max;
    }
    public static function recordLoginAttempt($ip) {
        Database::getInstance()->execute("INSERT INTO login_attempts (ip_address) VALUES (?)", [$ip]);
    }
    public static function clearLoginAttempts($ip) {
        Database::getInstance()->execute("DELETE FROM login_attempts WHERE ip_address = ?", [$ip]);
    }
    public static function getClientIP() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
    public static function checkHoneypot($field = 'website') {
        return empty($_POST[$field]);
    }
    public static function checkFormTiming($field = 'form_start_time', $min = 3) {
        if (!isset($_POST[$field])) return false;
        $elapsed = time() - (int)$_POST[$field];
        return $elapsed >= $min && $elapsed < 3600;
    }
    public static function createSlug($text) {
        $tr = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ye','ж'=>'zh','з'=>'z',
            'и'=>'y','і'=>'i','ї'=>'yi','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p',
            'р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch',
            'ь'=>'','ю'=>'yu','я'=>'ya'
        ];
        $text = strtr(mb_strtolower($text), $tr);
        return trim(preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    }
}