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

    /* ############# READ FUNCTIONS ############# */

    /* function to get user role */
    public function getRole($roleId) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.ROLES_TABLE.' WHERE '.ROLE_ID.'=:id');
        $stmt->execute(['id' => $roleId]);
        
        /* if find role return it */
        if ($stmt && ($role = $stmt->fetch(PDO::FETCH_ASSOC))) return $role;
        /* else return false */
        return FALSE;
    }

//     /* function to get role id by role name */
//     public function getRoleId($roleName) {
//         /* set fail results */
//         $result = [
//             MESSAGE => 'Invalid role',
//             SUCCESS => FALSE
//         ];
        
//         /* prepare sql query */
//         $sql = 'SELECT * FROM '.ROLES_TABLE.' WHERE '.ROLE.'=:role';
//         $stmt = $this->conn->prepare($sql);
//         /* execute sql query */
//         $stmt->execute(['role' => $roleName]);
        
//         /* check statement, get role and set success result */
//         if ($stmt && ($role = $stmt->fetch(PDO::FETCH_OBJ))) {
//             unset($result[MESSAGE]);
//             $result[SUCCESS] = TRUE;
//             $result[ROLE_ID] = $role->{ROLE_ID};
//         }
        
//         /* return result */
//         return $result;
//     }

    /* function to get all name and id roles */
    public function getNameAndIdRoles() {
        /* prepare sql query, then execute */
        $stmt =$this->conn->prepare('SELECT '.ROLE_ID.', '.ROLE.' FROM '.ROLES_TABLE);
        $stmt->execute();
        
        /* if find roles return it */
        if ($stmt && ($roles = $stmt->fetchAll(PDO::FETCH_ASSOC))) return $roles;
        /* else return false */
        return FALSE;
    }

    /* function to get role id by role name */
    public function getRoleIdList() {
        /* prepare sql query */
        $sql = 'SELECT '.ROLE_ID.' FROM '.ROLES_TABLE;
        $stmt = $this->conn->prepare($sql);
        /* execute sql query */
        $stmt->execute();

        $result = [];
        /* check statement, get role list and set result */
        if ($stmt && ($roles = $stmt->fetchAll(PDO::FETCH_ASSOC))) foreach ($roles as $role) $result[] = $role[ROLE_ID];
        
        /* return result */
        return $result;
    }
}

