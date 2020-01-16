<?php
namespace app\controllers\data;

use PDO;

/**
 * Class data factory, used for generate and manage the data of response
 * @author Andrea Serra (DevAS) https://devas.info
 */
class DataFactory {
    protected $conn;
//     protected $appConfig = [];
    static protected $instance;

    /* singleton */
    static public function getInstance(PDO $conn=NULL): DataFactory {
        if (!isset(static::$instance)) static::$instance = new static($conn);
        return static::$instance;
    }

    protected function __construct(PDO $conn=NULL) {
//         $this->appConfig = $appConfig;
        $this->conn = $conn;
    }
}

