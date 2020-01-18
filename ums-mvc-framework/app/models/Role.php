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
    
    /* ############# READ FUNCTIONS ############# */

    /* function to get roles list */
    public function getRoles(string $orderBy=ROLE_ID, string $orderDir=DESC, int $start=0, int $nRow=10) {
        $sql ='SELECT * FROM '.ROLES_TABLE;
        /* validate order by, order direction, start and num of row */
        $orderBy = in_array($orderBy, ROLES_ORDER_BY_LIST) ? $orderBy : ROLE_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        /* prepare and execute sql query */
        $sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        /* if sql query success then return roles list */
        if ($stmt && $stmt->errorCode() == 0) return $stmt->fetchAll(PDO::FETCH_OBJ);

        /* return empty data */
        return [];
    }

    /* function to get user role */
    public function getRole($roleId) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.ROLES_TABLE.' WHERE '.ROLE_ID.'=:id');
        $stmt->execute(['id' => $roleId]);
        
        /* if find role return it */
        if ($stmt && $stmt->errorCode() == 0) return $stmt->fetch(PDO::FETCH_ASSOC);
        /* else return false */
        return FALSE;
    }

    /* function to get all name and id roles */
    public function getNameAndIdRoles() {
        /* prepare sql query, then execute */
        $stmt =$this->conn->prepare('SELECT '.ROLE_ID.', '.ROLE.' FROM '.ROLES_TABLE);
        $stmt->execute();
        
        /* if find roles return it */
        if ($stmt && $stmt->errorCode() == 0) return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        if ($stmt && $stmt->errorCode() == 0 && ($roles = $stmt->fetchAll(PDO::FETCH_ASSOC))) foreach ($roles as $role) $result[] = $role[ROLE_ID];
        
        /* return result */
        return $result;
    }

    /* function to count the roles on table */
    public function countRoles(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.ROLES_TABLE;
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
