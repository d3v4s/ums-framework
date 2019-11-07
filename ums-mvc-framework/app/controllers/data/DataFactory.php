<?php
namespace app\controllers\data;

use PDO;

class DataFactory {
    protected $conn;
    protected $appConfig = [];
    static protected $instance;

    static public function getInstance(array $appConfig, PDO $conn = NULL): DataFactory {
        if (!isset(static::$instance)) static::$instance = new static($appConfig, $conn);
        return static::$instance;
    }

    protected function __construct(array $appConfig, PDO $conn = NULL) {
        $this->appConfig = $appConfig;
        $this->conn = $conn;
    }
}

