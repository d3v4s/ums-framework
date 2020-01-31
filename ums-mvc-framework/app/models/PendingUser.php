<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on pending users db table
 * @author Andrea Serra (DevAS) https://devas.info
 */
class PendingUser extends DbModel {

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to get coloumn list */
    public function getColList(): array {
        return [
            PENDING_USER_ID,
            USER_ID_FRGN,
            NAME,
            USERNAME,
            EMAIL,
            ROLE_ID_FRGN,
            REGISTRATION_DATETIME,
            EXPIRE_DATETIME
        ];
    }

    /* ############# CREATE FUNCTIONS ############# */

    /* function to save new pending user */
    public function savePendingUser(array $data): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save user failed',
            SUCCESS => FALSE
        ];
        
        /* get hash of password */
        $password = password_hash($data[PASSWORD], PASSWORD_DEFAULT);
        $token = $this->getNewAccountEnablerToken();
        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.PENDING_USERS_TABLE.' ('.NAME.', '.USERNAME.', '.EMAIL.', '.PASSWORD.', '.ROLE_ID_FRGN.', '.ENABLER_TOKEN.', '.EXPIRE_DATETIME.') VALUES ';
        $sql .= "(:name, :username, :email, :password, :role_id, :account_enabler_token, :datetime)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'name' => $data[NAME],
            'username' => $data[USERNAME],
            'email' => $data[EMAIL],
            'password' => $password,
            'role_id' => $data[ROLE_ID_FRGN],
            'account_enabler_token' => $token,
            'datetime' => $data[EXPIRE_DATETIME]
        ]);
        
        /* if sql query success, then set success result */
        if ($stmt->rowCount()) {
            $result[USER_ID] = $this->conn->lastInsertId();
            $result[MESSAGE] = 'New user saved successfully';
            $result[SUCCESS] = TRUE;
            $result[TOKEN] = $token;
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        /* return result */
        return $result;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get pending user list*/
    public function getPendingUsers(string $orderBy=PENDING_USER_ID, string $orderDir=DESC, string $search='', int $start=0, int $nRow=10) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.PENDING_USER_ID.'=:searchId OR ';
            $sql .= NAME.' LIKE :search OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= EMAIL.' LIKE :search OR ';
            $sql .= ROLE.' LIKE :search ';
            $data = [
                'searchId' => $search,
                'search' => "%$search%"
            ];
        }
        $orderBy = in_array($orderBy, PENDING_USERS_ORDER_BY_LIST) ? $orderBy : PENDING_USER_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        $sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);

        /* if success, then return users list */
        if ($stmt->errorCode() == 0) {
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($users as $user) unset($user->password);
            return $users;
        }
        /* else return empty array */
        return [];
    }

    /* function get pending users for advance search */
    public function getPendingUsersAdvanceSearch(string $orderBy=PENDING_USER_ID, string $orderDir=DESC, int $start=0, int $nRow=10, array $searchData=[]): array {
        /* create sql query */
        $searchData = filterNullVal($searchData);
        $sql = 'SELECT * FROM '.PENDING_USERS_TABLE.' WHERE ';
        /* append query search */
        if (isset($searchData[PENDING_USER_ID])) {
            $sql .= PENDING_USER_ID.'=:'.PENDING_USER_ID;
            $searchData = [
                PENDING_USER_ID => $searchData[PENDING_USER_ID]
            ];
        } else if (isset($searchData[USER_ID_FRGN])) {
            $sql .= USER_ID_FRGN.'=:'.USER_ID_FRGN;
            $searchData = [
                USER_ID_FRGN => $searchData[USER_ID_FRGN]
            ];
        } else {
            $and = count($searchData)-1;
            foreach ($searchData as $key => $val) {
                if (!in_array($key, $this->getColList())) continue;
                $searchData[$key] = "%$val%";
                $sql .= "$key LIKE :$key";
                if ($and-- > 0) $sql .= ' AND ';
            }
        }
        /* validate order by, order direction, start and num of row */
        $orderBy = in_array($orderBy, $this->getColList()) ? $orderBy : PENDING_USER_ID;
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

    /* function to get pending user by id */
    public function getPendingUser(int $id, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.PENDING_USERS_TABLE.' WHERE '.PENDING_USER_ID.'=:id');
        $stmt->execute(['id' => $id]);

        /* if find user return it */
        if ($stmt->errorCode() == 0 && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get pending user by id with role */
    public function getPendingUserAndRole(int $id, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID.'='.ROLE_ID_FRGN;
        $sql .= ' WHERE '.PENDING_USER_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        /* if find user return it */
        if ($stmt->errorCode() == 0 && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get pending user by id where token is not null*/
    public function getValidPendingUser(int $pendUserId, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $sql ='SELECT * FROM '.PENDING_USERS_TABLE.' WHERE '.PENDING_USER_ID.'=:id AND '.ENABLER_TOKEN.' IS NOT NULL';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $pendUserId]);
        
        /* if find user return it */
        if ($stmt->errorCode() == 0 && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get pending user by account enabler token */
    public function getUserByAccountEnablerToken(string $token, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.PENDING_USERS_TABLE.' WHERE '.ENABLER_TOKEN.'=:token');
        $stmt->execute(['token' => $token]);
        
        /* if find user return it */
        if ($stmt->errorCode() == 0 && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to count the all pending users on table */
    public function countAllPendingUsers($search=''): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.PENDING_USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.PENDING_USER_ID.'=:searchId OR ';
            $sql .= NAME.' LIKE :search OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= EMAIL.' LIKE :search OR ';
            $sql .= ROLE.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' =>"%$search%"
            ];
        }
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        /* return total users */
        return $stmt->errorCode() == 0 ? $stmt->fetch(PDO::FETCH_ASSOC)['total'] : 0;
    }

    /* function to count only the valid pending users on table */
    public function countValidPendingUsers(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.PENDING_USERS_TABLE;
        $sql .= ' WHERE '.ENABLER_TOKEN.' IS NOT NULL AND ';
        $sql .= EXPIRE_DATETIME.' > CURRENT_TIMESTAMP()';

        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->errorCode() == 0 ? $stmt->fetch(PDO::FETCH_ASSOC)['total'] : 0;
    }

    /* function count pending users for advance search */
    public function countAdvanceSearchPendingUsers(array $searchData=[]): int {
        /* create sql query */
        $searchData = filterNullVal($searchData);
        $sql = 'SELECT COUNT(*) AS total FROM '.PENDING_USERS_TABLE.' WHERE ';
        /* append query search */
        if (isset($searchData[PENDING_USER_ID])) {
            $sql .= PENDING_USER_ID.'=:'.PENDING_USER_ID;
            $searchData = [
                PENDING_USER_ID => $searchData[PENDING_USER_ID]
            ];
        } else if (isset($searchData[USER_ID_FRGN])) {
            $sql .= USER_ID_FRGN.'=:'.USER_ID_FRGN;
            $searchData = [
                USER_ID_FRGN => $searchData[USER_ID_FRGN]
            ];
        } else {
            $and = count($searchData)-1;
            foreach ($searchData as $key => $val) {
                if (!in_array($key, $this->getColList())) continue;
                $searchData[$key] = "%$val%";
                $sql .= "$key LIKE :$key";
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

    /* function to remove account enabler token on pending users table */
    public function removeAccountEnablerToken(string $token): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_USERS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.ENABLER_TOKEN.'=:token');
        $stmt->execute(['token' => $token]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove account enabler token on pending users table */
    public function removeAccountEnablerTokenById(int $id): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_USERS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.PENDING_USER_ID.'=:id');
        $stmt->execute(['id' => $id]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove account enabler token on pending users table */
    public function setUserIdAndRemoveToken(string $token, int $userId): bool {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.PENDING_USERS_TABLE;
        $sql .= ' SET '.ENABLER_TOKEN.'=NULL, '.USER_ID_FRGN.'=:id WHERE '.ENABLER_TOKEN.'=:token';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $userId,
            'token' => $token
        ]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to update expire datetime */
    public function updateExpireDatetime(string $token, string $datetime): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_USERS_TABLE.' SET '.EXPIRE_DATETIME.'=:datetime WHERE '.ENABLER_TOKEN.'=:token');
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

    /* function to get unique account enabler token */
    private function getNewAccountEnablerToken(): string {
        /* generates a random string until it's not unique */
        do $token = getSecureRandomString();
        while ($this->getUserByAccountEnablerToken($token));
        /* return unique token */
        return $token;
    }
}
