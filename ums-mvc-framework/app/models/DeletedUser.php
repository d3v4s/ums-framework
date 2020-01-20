<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on deleted users db table
 * @author Andrea Serra (DevAS) https://devas.info
 */
class DeletedUser {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ############# CREATE FUNCTIONS ############# */

    /* function to save new pending user */
    public function saveDeletedUser($user): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save deleted user failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.DELETED_USER_TABLE.' ('.USER_ID.', '.NAME.', '.USERNAME.', '.EMAIL.', '.ROLE_ID_FRGN.', '.REGISTRATION_DATETIME.') VALUES ';
        $sql .= "(:id, :name, :username, :email, :role_id, :datetime)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $user->{USER_ID},
            'name' => $user->{NAME},
            'username' => $user->{USERNAME},
            'email' => $user->{EMAIL},
            'role_id' => $user->{ROLE_ID_FRGN},
            'datetime' => $user->{REGISTRATION_DATETIME}
        ]);
        
        /* if sql query success, then set success result */
        if ($stmt->rowCount()) {
            $result[MESSAGE] = 'Deleted user saved successfully';
            $result[SUCCESS] = TRUE;
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        /* return result */
        return $result;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get pending user by id */
    public function getDeletedUsers(string $orderBy = USER_ID, string $orderDir = DESC, string $search = '', int $start = 0, int $nRow = 10) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.DELETED_USER_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.USER_ID.' = :searchId OR ';
            $sql .= NAME.' LIKE :search OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= EMAIL.' LIKE :search OR ';
            $sql .= ROLE.' LIKE :search ';
            $data = [
                'searchId' => $search,
                'search' => "%$search%"
            ];
        }
        $orderBy = in_array($orderBy, DELETED_USERS_ORDER_BY_LIST) ? $orderBy : USER_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        $sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);

        /* if find user return it */
        if ($stmt && ($users = $stmt->fetchAll(PDO::FETCH_OBJ))) return $users;
        /* else empty array */
        return [];
    }

    /* function to get pending user by id */
    public function getDeleteUser(int $id) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.DELETED_USER_TABLE.' WHERE '.DELETED_USER_ID.'=:id');
        $stmt->execute(['id' => $id]);

        /* if find user return it */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) return $user;
        /* else return false */
        return FALSE;
    }

    /* function to get pending user by id */
    public function getDeleteUserAndRole(int $id) {
        /* prepare sql query and execute it */
        $sql = 'SELECT * FROM '.DELETED_USER_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $sql .= ' WHERE '.USER_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        /* if success query and find user return user */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) return $user;
        /* else return false */
        return FALSE;
    }

    /* function to get pending user by id */
    public function getDeleteUserByUserId(int $id) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.DELETED_USER_TABLE.' WHERE '.USER_ID.'=:id');
        $stmt->execute(['id' => $id]);
        
        /* if find user return it */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) return $user;
        /* else return false */
        return FALSE;
    }

    /* function to count deleted users */
    public function countDeletedUsers(string $search=''): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.DELETED_USER_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        /* init sql data */
        $data = [];
        /* if search is not emapty, then append sqarch query and set data */
        if (!empty($search)) {
            $sql .= ' WHERE '.USER_ID.'=:searchId OR ';
            $sql .= NAME.' LIKE :search OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= EMAIL.' LIKE :search OR ';
            $sql .= ROLE.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' => "%$search%"
            ];
        }
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        /* return num of user */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* ############# DELETE FUNCTIONS ############# */

    public function removeDeleteUser(int $userId) {
        /* set fail result */
        $result = [
            MESSAGE => 'Remove deleted user failed',
            SUCCESS => FALSE
        ];
        
        /* disable foreign key check */
        $sql = 'SET FOREIGN_KEY_CHECKS=0;';
        /* delete query */
        $sql .= 'DELETE FROM '.DELETED_USER_TABLE.' WHERE '.USER_ID.'=:id;';
        /* enable foreign key check */
        $sql .= 'SET FOREIGN_KEY_CHECKS=1';
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $userId]);
        
        /* if success then set success result */
        if ($stmt->errorCode() == 0){
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'Deleted user remove successfully';
            /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        return $result;
    }
//     /* ##################################### */
//     /* PRIVATE FUNCTIONS */
//     /* ##################################### */

}
