<?php
class Logger {
    private static $log_dir = __DIR__ . '/../logs/';
    
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';
    
    public static function log($message, $level = self::INFO, $context = []) {
        if (!file_exists(self::$log_dir)) {
            mkdir(self::$log_dir, 0755, true);
        }
        
        $logFile = self::$log_dir . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    public static function debug($message, $context = []) {
        self::log($message, self::DEBUG, $context);
    }
    
    public static function info($message, $context = []) {
        self::log($message, self::INFO, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log($message, self::WARNING, $context);
    }
    
    public static function error($message, $context = []) {
        self::log($message, self::ERROR, $context);
    }
    
    public static function critical($message, $context = []) {
        self::log($message, self::CRITICAL, $context);
    }
    
    public static function clear($days = 30) {
        $files = glob(self::$log_dir . '*.log');
        $cutoff = time() - ($days * 86400);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
