<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;
    private $transactionCount = 0;

    private function __construct() {
        $config = require __DIR__ . '/../Config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            throw new \Exception("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function beginTransaction() {
        if ($this->transactionCount == 0) {
            $this->pdo->beginTransaction();
        }
        $this->transactionCount++;
        return true;
    }

    public function commit() {
        if ($this->transactionCount == 0) return false;
        
        $this->transactionCount--;
        if ($this->transactionCount == 0) {
            return $this->pdo->commit();
        }
        return true;
    }

    public function rollBack() {
        if ($this->transactionCount == 0) return false;

        $this->transactionCount = 0;
        return $this->pdo->rollBack();
    }
}
