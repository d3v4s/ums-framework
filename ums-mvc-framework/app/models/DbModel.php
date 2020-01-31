<?php

namespace app\models;

use \PDO;

/**
 * Abstract class for the db model
 * @author Andrea Serra (DevAS) https://devas.info
 */
abstract class DbModel {
    protected $conn;
    

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    abstract public function getColList(): array;
}

