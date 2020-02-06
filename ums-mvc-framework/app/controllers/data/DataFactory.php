<?php
namespace app\controllers\data;

use PDO;

/**
 * Class data factory, used for generate and manage the data of response
 * @author Andrea Serra (DevAS) https://devas.info
 */
class DataFactory {
    protected $conn;
    protected $langData;
    static protected $instance;

    /* singleton */
    static public function getInstance(array $langData=[],PDO $conn=NULL): DataFactory {
        if (!isset(static::$instance)) static::$instance = new static($langData, $conn);
        return static::$instance;
    }

    protected function __construct(array $langData=[], PDO $conn=NULL) {
        $this->langData = $langData;
        $this->conn = $conn;
    }
}

