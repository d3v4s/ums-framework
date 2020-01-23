<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on pending password reset request db table
 * @author Andrea Serra (DevAS) https://devas.info
 */
class PasswordResetRequest {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ############# CREATE FUNCTIONS ############# */

    /* function to add new password reset request */
    public function newPasswordResetReq(int $userId, string $ipAddress, string $expireDatetime) {
        /* set fail result */
        $result = [
            MESSAGE => 'Create a new password reset request failed',
            SUCCESS => FALSE
        ];

        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.PASSWORD_RESET_REQ_TABLE.' ('.USER_ID_FRGN.', '.PASSWORD_RESET_TOKEN.', '.IP_ADDRESS.', '.EXPIRE_DATETIME.') VALUES ';
        $sql .= "(:user_id, :token, :ip_addr, :expire_datetime)";
        $stmt = $this->conn->prepare($sql);
        $token = $this->getNewPassResetToken();
        $stmt->execute([
            'user_id' => $userId,
            'token' => $token,
            'ip_addr' => $ipAddress,
            'expire_datetime' => $expireDatetime
        ]);

        /* if sql query success, then return success result */
        if ($stmt && $stmt->rowCount()) {
            $result[TOKEN] = $token;
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'New password reset request created successfully';
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        /* return result */
        return $result;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get password reset requests list */
    public function getPassResetRequests(string $orderBy=PASSWORD_RESET_REQ_ID, string $orderDir=DESC, string $search='', int $start=0, int $nRow=10) {
        /* set sql query */
        $sql = 'SELECT '.PASSWORD_RESET_REQ_ID.', '.USER_ID_FRGN.', '.IP_ADDRESS.', '.PASSWORD_RESET_TOKEN.', '.EXPIRE_DATETIME.', '.USER_ID.', '.USERNAME;
        $sql .= ' FROM '.PASSWORD_RESET_REQ_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.PASSWORD_RESET_REQ_ID.'=:searchId OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= IP_ADDRESS.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' =>"%$search%"
            ];
        }
        /* validate order by, order direction, start and num of row */
        $orderBy = in_array($orderBy, PASS_RESET_REQ_ORDER_BY_LIST) ? $orderBy : SESSION_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        /* prepare and execute sql query */
        $sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        /* if success, then return users list */
        if ($stmt) return $stmt->fetchAll(PDO::FETCH_OBJ);
        /* else return empty array */
        return [];
    }

    /* function to get user by login session token */
    public function getUserByResetPasswordToken(string $token, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PASSWORD_RESET_REQ_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.PASSWORD_RESET_TOKEN.' = :token';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['token' => $token]);

        /* if find user chech if session is expire */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password and return user */
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get password reset request and user by password reset request id */
    public function getPassResReqLeftUser(int $passResReqId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PASSWORD_RESET_REQ_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.PASSWORD_RESET_REQ_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $passResReqId]);
        
        /* if find password reset request return it */
        if ($stmt && ($passResReq = $stmt->fetch(PDO::FETCH_OBJ))) return $passResReq;
        /* else return false */
        return FALSE;
    }

    /* function to get pending mail and user by pending email id */
    public function getValidPassResReqAndUser(int $passResReqId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PASSWORD_RESET_REQ_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.PASSWORD_RESET_REQ_ID.'=:id AND '.PASSWORD_RESET_TOKEN.' IS NOT NULL';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $passResReqId]);

        /* if find password reset request return it */
        if ($stmt && ($passResReq = $stmt->fetch(PDO::FETCH_OBJ))) return $passResReq;
        /* else return false */
        return FALSE;
    }

    /* function to count all password reset requests on table */
    public function countValidPasswordResetReq(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.PASSWORD_RESET_REQ_TABLE;
        $sql .= ' WHERE '.PASSWORD_RESET_TOKEN.' IS NOT NULL AND ';
        $sql .= EXPIRE_DATETIME.' > CURRENT_TIMESTAMP';

        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* function to count all password reset requests on table */
    public function countAllPasswordResetReq(string $search): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.PASSWORD_RESET_REQ_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.PASSWORD_RESET_REQ_ID.'=:searchId OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= IP_ADDRESS.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' => "%$search%"
            ];
        }
        
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* ############# UPDATE FUNCTIONS ############# */

    /* function to remove password reset request for user id */
    public function removePasswordResetReqForUser(int $userId): bool {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.PASSWORD_RESET_REQ_TABLE.' SET '.PASSWORD_RESET_TOKEN.'=NULL WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $userId]);

        /* if sql query success return true */
        if ($stmt && $stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove token of password reset request */
    public function removePasswordResetReqTokenById(int $passResId) {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.PASSWORD_RESET_REQ_TABLE.' SET '.PASSWORD_RESET_TOKEN.'=NULL WHERE '.PASSWORD_RESET_REQ_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $passResId]);
        
        /* if sql query success return true */
        if ($stmt && $stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to update expire datetime */
    public function updateExpireDatetime(string $token, string $datetime): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PASSWORD_RESET_REQ_TABLE.' SET '.EXPIRE_DATETIME.'=:datetime WHERE '.PASSWORD_RESET_TOKEN.'=:token');
        $stmt->execute([
            'datetime' => $datetime,
            'token' => $token
        ]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get new login session token */
    private function getNewPassResetToken(): string {
        /* generates a random string until it's not unique */
        do $token = getSecureRandomString();
        while ($this->getUserByResetPasswordToken($token));

        /* return unique token */
        return $token;
    }
}
