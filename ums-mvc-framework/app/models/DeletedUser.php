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
        $sql = 'INSERT INTO '.DELETED_USER_TABLE.' ('.USER_ID_FRGN.', '.NAME.', '.USERNAME.', '.EMAIL.', '.ROLE_ID_FRGN.', '.REGISTRATION_DATETIME.') VALUES ';
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
    public function getDeleteUsers(string $orderBy = USER_ID_FRGN, string $orderDir = DESC, string $search = '', int $start = 0, int $nRow = 10) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.USERS_TABLE.' JOIN ';
        if (!empty($search)) {
            $sql .= ' WHERE '.USER_ID.' = :searchId OR ';
            $sql .= NAME.' LIKE :search OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= EMAIL.'LIKE :search OR ';
            $sql .= ROLE_ID_FRGN.' LIKE :search ';
        }
        $orderBy = in_array($orderBy, ORDER_BY_LIST) ? $orderBy : USER_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        $sql .= "ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $data = empty($search)? [] : [
            'searchId' => $search,
            'search' => '%'.$search.'%'
        ];
        $stmt->execute($data);
        
        /* if find user return it */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) return $user;
        /* else return false */
        return FALSE;
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
    public function getDeleteUserByUserId(int $id) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.DELETED_USER_TABLE.' WHERE '.USER_ID_FRGN.'=:id');
        $stmt->execute(['id' => $id]);
        
        /* if find user return it */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) return $user;
        /* else return false */
        return FALSE;
    }

    public function countDeletedUsers(string $search = ''): int {
        $sql = 'SELECT COUNT(*) AS total FROM '.DELETED_USER_TABLE;
        if (!empty($search)) {
            $sql .= ' WHERE '.USER_ID_FRGN.'=:searchId OR ';
            $sql .= NAME.' LIKE :search OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= EMAIL.' LIKE :search';
            //             $sql .= 'roletype LIKE :search';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'searchId' => $search,
            'search' => "%$search%"
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

//     /* ############# UPDATE FUNCTIONS ############# */

//     /* function to remove account enabler token on pending users table */
//     public function removeAccountEnablerToken(string $token): bool {
//         /* prepare sql query and execute it */
//         $stmt = $this->conn->prepare('UPDATE '.PENDING_USERS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.ENABLER_TOKEN.'=:token');
//         $stmt->execute(['token' => $token]);
        
//         /* if sql success return true */
//         if($stmt->rowCount()) return TRUE;
//         /* else return false */
//         return FALSE;
//     }

//     /* ##################################### */
//     /* PRIVATE FUNCTIONS */
//     /* ##################################### */

}
