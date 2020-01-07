<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on role db table
 * @author Andrea Serra (DevAS) https://devas.info
 */
class Role {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ############# CREATE FUNCTIONS ############# */

    /* function to get user role */
    public function getRole($roleId) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.ROLES_TABLE.' WHERE '.ROLE_ID.' = :roleId');
        $stmt->execute(compact('roleId'));
        
        /* if find role return it */
        if ($stmt && ($role = $stmt->fetch(PDO::FETCH_OBJ))) {
            return $role;
        }
        /* else return false */
        return FALSE;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get role id by role name */
    public function getRoleId($role_name) {
        /* set fail results */
        $result = [
            MESSAGE => 'Invalid role',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query */
        $sql = 'SELECT * FROM '.ROLES_TABLE.' WHERE '.ROLE.'=:role';
        $stmt = $this->conn->prepare($sql);
        /* execute sql query */
        $stmt->execute([
            'role' => $role_name
        ]);
        
        /* check statement, get role and return success result */
        if ($stmt && ($role = $stmt->fetch(PDO::FETCH_OBJ))) {
            unset($result[MESSAGE]);
            $result[SUCCESS] = TRUE;
            $result[ROLE_ID] = $role->ROLE_ID;
            return $result;
        }
        
        /* return fail result */
        return $result;
    }
}

