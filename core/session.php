<?php
class Session {
    public static function set($k, $v) {$_SESSION[$k] = $v;}
    public static function get($k, $d = null) {return $_SESSION[$k] ?? $d;}
    public static function has($k) {return isset($_SESSION[$k]);}
    public static function delete($k) {if(isset($_SESSION[$k])) unset($_SESSION[$k]);}
    public static function flash($k, $v = null) {
        if ($v === null) {
            $f = self::get('_flash_' . $k);
            self::delete('_flash_' . $k);
            return $f;
        }
        self::set('_flash_' . $k, $v);
    }
    public static function isAdmin() {return self::get('admin_logged_in') === true;}
    public static function loginAdmin() {
        session_regenerate_id(true);
        self::set('admin_logged_in', true);
    }
    public static function logoutAdmin() {
        self::delete('admin_logged_in');
        session_regenerate_id(true);
    }
}