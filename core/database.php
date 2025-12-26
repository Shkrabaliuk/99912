<?php
require_once __DIR__ . '/database-interface.php';
require_once __DIR__ . '/exceptions.php';
require_once __DIR__ . '/logger.php';

class Database implements DatabaseInterface {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            Logger::info('Database connection established');
        } catch (PDOException $e) {
            Logger::critical('Database connection failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            if (defined('DEBUG') && DEBUG) {
                throw new DatabaseException('DB Connection Error: ' . $e->getMessage(), 0, $e);
            }
            
            throw new DatabaseException('Database connection failed. Please try again later.');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error('Database query failed', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            
            if (defined('DEBUG') && DEBUG) {
                throw new DatabaseException('Query Error: ' . $e->getMessage(), 0, $e);
            }
            
            throw new DatabaseException('Database operation failed. Please try again.');
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function execute($sql, $params = []) {
        return $this->query($sql, $params);
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    private function __clone() {}
}