<?php
namespace app\models;

use \PDO;

class Session {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ############# CREATE FUNCTIONS ############# */

    /* function to set new login session */
    public function newLoginSession(int $userId, string $ipAddress, string $expireDatetime) {
        /* set fail result */
        $result = [
            MESSAGE => 'Adding new login session failed',
            SUCCESS => FALSE
        ];

        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.SESSIONS_TABLE.' ('.USER_ID_FRGN.', '.SESSION_TOKEN.', '.IP_ADDRESS.', '.EXPIRE_DATETIME.') VALUES ';
        $sql .= "(:user_id, :token, :ip_addr, :expire_datetime)";
        $stmt = $this->conn->prepare($sql);
        $sessTkn = $this->getNewLoginSessionToken();
        $stmt->execute([
            'user_id' => $userId,
            'token' => $sessTkn,
            'ip_addr' => $ipAddress,
            'expire_datetime' => $expireDatetime
        ]);

        /* if sql query success, then return success result */
        if ($stmt->rowCount()) {
            $result[TOKEN] = $sessTkn;
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'New user saved successfully';
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        /* return result */
        return $result;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get sessions list */
    public function getSessions(string $orderBy=SESSION_ID, string $orderDir=DESC, string $search='', int $start=0, int $nRow=10) {
        /* set sql query */
        $sql = 'SELECT '.SESSION_ID.', '.USER_ID_FRGN.', '.IP_ADDRESS.', '.SESSION_TOKEN.','.EXPIRE_DATETIME.', '.USER_ID.', '.USERNAME;
        $sql .= ' FROM '.SESSIONS_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.SESSION_ID.'=:searchId OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= IP_ADDRESS.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' =>"%$search%"
            ];
        }
        /* validate order by, order direction, start and num of row */
        $orderBy = in_array($orderBy, SESSIONS_ORDER_BY_LIST) ? $orderBy : SESSION_ID;
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
    public function getUserByLoginSessionToken(string $token, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.SESSIONS_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.SESSION_TOKEN.' = :token';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['token' => $token]);

        /* if find user chech if session is expire */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* else unset password and return user */
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to count all session on table */
    public function countAllSessions(string $search): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.SESSIONS_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.SESSION_ID.'=:searchId OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= IP_ADDRESS.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' =>"%$search%"
            ];
        }
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* function to count session active */
    public function countSessions(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.SESSIONS_TABLE.' WHERE '.SESSION_TOKEN.' IS NOT NULL';
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* ############# UPDATE FUNCTIONS ############# */

    /* function to remove login session by session id */
    public function setExpireLoginSession(int $sessionId, string $expireDatetime): bool {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.SESSIONS_TABLE.' SET '.EXPIRE_DATETIME.'=:expireDatetime WHERE '.SESSION_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $sessionId,
            'expireDatetime' => $expireDatetime
        ]);
        
        /* if sql query success return true */
        if ($stmt && $stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove login session by session id */
    public function removeLoginSession(int $sessionId): bool {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.SESSIONS_TABLE.' SET '.SESSION_TOKEN.'=NULL WHERE '.SESSION_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $sessionId
        ]);

        /* if sql query success return true */
        if ($stmt && $stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove login session by token */
    public function removeLoginSessionToken(string $sessionToken): bool {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.SESSIONS_TABLE.' SET '.SESSION_TOKEN.'=NULL WHERE '.SESSION_TOKEN.'=:token';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'token' => $sessionToken
        ]);

        /* if sql query success return true */
        if ($stmt && $stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove login session by token */
    public function removeAllLoginSessionTokens(string $userId): bool {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.SESSIONS_TABLE.' SET '.SESSION_TOKEN.'=NULL WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $userId
        ]);
        
        /* if sql query success return true */
        if ($stmt && $stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get new login session token */
    private function getNewLoginSessionToken(): string {
        /* generates a random string until it's not unique */
        do $token = getSecureRandomString();
        while ($this->getUserByLoginSessionToken($token));

        /* return unique token */
        return $token;
    }
}

