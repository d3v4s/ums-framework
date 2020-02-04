<?php
namespace app\models;

use \PDO;

class Session extends DbModel {

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to get coloumn list */
    public function getColList(): array {
        return [
            SESSION_ID,
            USER_ID_FRGN,
            IP_ADDRESS,
            EXPIRE_DATETIME
        ];
    }
    
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
            $result[MESSAGE] = 'New session saved successfully';
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

    /* function get sessions for advance search */
    public function getSessionsAdvanceSearch(string $orderBy=SESSION_ID, string $orderDir=DESC, int $start=0, int $nRow=10, array $searchData=[]): array {
        /* create sql query */
        $searchData = filterNullVal($searchData);
        $sql = 'SELECT * FROM '.SESSIONS_TABLE.' WHERE ';
        /* append query search */
        if (isset($searchData[SESSION_ID])) {
            $sql .= SESSION_ID.'=:'.SESSION_ID;
            $searchData = [
                SESSION_ID => $searchData[SESSION_ID]
            ];
        } else {
            $and = count($searchData)-1;
            foreach ($searchData as $key => $val) {
                if (!in_array($key, $this->getColList())) continue;
                switch ($key) {
                    case USER_ID_FRGN:
                        $sql .= "$key=:$key";
                        break;
                    default:
                        $searchData[$key] = "%$val%";
                        $sql .= "$key LIKE :$key";
                        break;
                }
                if ($and-- > 0) $sql .= ' AND ';
            }
        }
        /* validate order by, order direction, start and num of row */
        $orderBy = in_array($orderBy, $this->getColList()) ? $orderBy : SESSION_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : DEFAULT_ROWS_FOR_PAGE;
        
        /* prepare and execute sql query */
        $sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($searchData);
        /* if success, then return users list */
        if ($stmt->errorCode() == 0) {
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $users;
        }
        /* else return empty array*/
        return [];
    }

    /* function to get session and user by session id */
    public function getSessionAndUser(int $sessionId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.SESSIONS_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.SESSION_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $sessionId]);
        
        /* if find user chech if session is expire */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* else unset password and return user */
            unset($user->password);
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get session and user by session id */
    public function getSessionLeftUser(int $sessionId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.SESSIONS_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.SESSION_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $sessionId]);
        
        /* if find user chech if session is expire */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* else unset password and return user */
            unset($user->password);
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get user by login session token */
    public function getUserByLoginSessionToken(string $token, bool $unsetPassword=TRUE) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.SESSIONS_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.SESSION_TOKEN.'=:token';
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

    /* function to get valid session id */
    public function getValidSession(int $sessionId) {
        /* create sql query */
        $sql = 'SELECT * FROM '.SESSIONS_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.SESSION_ID.'=:id AND ';
        $sql .= SESSION_TOKEN.' IS NOT NULL AND '.EXPIRE_DATETIME.' > CURRENT_TIMESTAMP()';
        /* execute query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $sessionId
        ]);
        
        /* if not return error and find result, then return it */
        if ($stmt->errorCode() == 0 && ($res = $stmt->fetch(PDO::FETCH_OBJ))) return $res;
        /* else return empty array */
        return FALSE;
    }

    /* function to get list of valid session by user id */
    public function getValidSessionsByUserId(int $userId): array {
        /* create sql query */
        $sql = 'SELECT * FROM '.SESSIONS_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.USER_ID_FRGN.'=:id AND ';
        $sql .= SESSION_TOKEN.' IS NOT NULL AND '.EXPIRE_DATETIME.' > CURRENT_TIMESTAMP()';
        /* execute query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $userId
        ]);

        /* if not return error and find result, then return it */
        if ($stmt->errorCode() == 0 & ($res = $stmt->fetchAll(PDO::FETCH_OBJ))) return $res;
        /* else return empty array */
        return [];
    }

    /* function to count all session on table */
    public function countAllSessions(string $search=''): int {
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
    public function countValidSessions(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.SESSIONS_TABLE;
        $sql .= ' WHERE '.SESSION_TOKEN.' IS NOT NULL AND ';
        $sql .= EXPIRE_DATETIME.' > CURRENT_TIMESTAMP()';
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* function count sessions for advance search */
    public function countAdvanceSearchSessions(array $searchData=[]): int {
        /* create sql query */
        $searchData = filterNullVal($searchData);
        $sql = 'SELECT COUNT(*) AS total FROM '.SESSIONS_TABLE.' WHERE ';
        /* append query search */
        if (isset($searchData[SESSION_ID])) {
            $sql .= SESSION_ID.'=:'.SESSION_ID;
            $searchData = [
                SESSION_ID => $searchData[SESSION_ID]
            ];
        } else {
            $and = count($searchData)-1;
            foreach ($searchData as $key => $val) {
                if (!in_array($key, $this->getColList())) continue;
                switch ($key) {
                    case USER_ID_FRGN:
                        $sql .= "$key=:$key";
                        break;
                    default:
                        $searchData[$key] = "%$val%";
                        $sql .= "$key LIKE :$key";
                        break;
                }
                if ($and-- > 0) $sql .= ' AND ';
            }
        }

        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($searchData);
        /* return total users */
        return $stmt->errorCode() == 0 ? $stmt->fetch(PDO::FETCH_ASSOC)['total'] : 0;
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

