<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on pending users db table
 * @author Andrea Serra (DevAS) https://devas.info
 */
class PendingUser {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ############# CREATE FUNCTIONS ############# */

    /* function to save new pending user */
    public function savePendingUser(array $data): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save user failed',
            SUCCESS => FALSE
        ];
        
        /* set default password if it is not set */
//         $data[PASSWORD] = $data[PASSWORD] ?? $this->appConfig[UMS][PASS_DEFAULT];
        /* get hash of password */
        $password = password_hash($data[PASSWORD], PASSWORD_DEFAULT);
//         /* set user role type if it is not set */
//         $roletype =  ?? $this->appConfig[UMS][DEFAULT_USER_ROLE];
        $token = $this->getNewAccountEnablerToken();
        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.PENDING_USERS_TABLE.' ('.NAME.', '.USERNAME.', '.EMAIL.', '.PASSWORD.', '.ROLE_ID_FRGN.', '.ENABLER_TOKEN.') VALUES ';
        $sql .= "(:name, :username, :email, :password, :role_id, :account_enabler_token)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'name' => $data[NAME],
            'username' => $data[USERNAME],
            'email' => $data[EMAIL],
            'password' => $password,
            'role_id' => $data[ROLE_ID_FRGN],
            'account_enabler_token' => $token
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

    /* function to get pending user by id */
    public function getPendingUsers(string $orderBy = PENDING_USER_ID, string $orderDir = DESC, string $search = '', int $start = 0, int $nRow = 10) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.PENDING_USER_ID.' = :searchId OR ';
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
        if ($stmt) {
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($users as $user) unset($user->password);
            return $users;
        }
        /* else return empty array */
        return [];
    }

    /* function to get pending user by id */
    public function getPendingUser(int $id, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $stmt = $this->conn->prepare('SELECT * FROM '.PENDING_USERS_TABLE.' WHERE '.PENDING_USER_ID.'=:id');
        $stmt->execute(['id' => $id]);

        /* if find user return it */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get pending user by id where token is not null*/
    public function getPendingUserTokenNotNull(int $id, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $sql ='SELECT * FROM '.PENDING_USERS_TABLE.' WHERE '.PENDING_USER_ID.'=:id AND '.ENABLER_TOKEN.' IS NOT NULL';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        /* if find user return it */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
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
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
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
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* function to count only the valid pending users on table */
    public function countPendingUsers(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.PENDING_USERS_TABLE.' WHERE '.ENABLER_TOKEN.' IS NOT NULL';
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

//     /* function to get pending user by id */
//     public function isPendingUsername(string $username, string $minDatetime) {
//         /* prepare sql query, then execute */
//         $sql = 'SELECT * FROM '.PENDING_USERS_TABLE.' WHERE ';
//         $sql .= USERNAME.'=:username AND '.ENABLER_TOKEN.' IS NOT NULL AND '.REGISTRATION_DATETIME.'>:datetime';
//         $stmt = $this->conn->prepare($sql);
//         $stmt->execute([
//             'username' => $username,
//             'datetime' => $minDatetime 
//         ]);
        
//         /* return true if find user, false otherwise */
//         return $stmt && $stmt->fetch(PDO::FETCH_OBJ);
//     }

//     /* function to get pending user by id */
//     public function isPendingEmail(string $email, string $minDatetime) {
//         /* prepare sql query, then execute */
//         $sql = 'SELECT * FROM '.PENDING_USERS_TABLE.' WHERE ';
//         $sql .= EMAIL.'=:email AND '.ENABLER_TOKEN.' IS NOT NULL AND '.REGISTRATION_DATETIME.'>:datetime';
//         $stmt = $this->conn->prepare($sql);
//         $stmt->execute([
//             'email' => $email,
//             'datetime' => $minDatetime
//         ]);
        
//         /* return true if find user, false otherwise */
//         return $stmt && $stmt->fetch(PDO::FETCH_OBJ);
//     }

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
