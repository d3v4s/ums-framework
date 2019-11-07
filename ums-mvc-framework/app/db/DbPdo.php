<?php
namespace app\db;
use \PDO;

class DbPdo {
    protected $conn;
    static protected $instance;

    static public function getInstance(array $options): DbPdo {
        if (!isset(static::$instance)) static::$instance = new static($options);
        return static::$instance; // = (static::$instance) ? static::$instance : new static($options);
    }

    protected function __construct(array $options) {
        $this->conn = new PDO($options['dsn'], $options['user'], $options['password']);
        if (array_key_exists('pdo', $options)) {
            foreach ($options['pdo'] as $key => $value)
                $this->conn->setAttribute($key, $value);
        }
    }

    public function getConn(): PDO {
        return $this->conn;
    }
}

