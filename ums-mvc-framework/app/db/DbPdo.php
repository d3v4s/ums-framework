<?php
namespace app\db;
use \PDO;

/**
 * Class to create pdo connection
 * @author Andrea Serra (DevAS) https://devas.info
 */
class DbPdo {
    protected $conn;
    static protected $instance;

    static public function getInstance(array $options): DbPdo {
        if (!isset(static::$instance)) static::$instance = new static($options);
        return static::$instance;
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

