<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on ums db tables
 * @author Andrea Serra (DevAS) https://devas.info
 */
class User {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
//         $this->appConfig = $appConfig ?? getConfig();
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ############# CREATE FUNCTIONS ############# */

    /* function to save new user */
    public function saveUser(array $data, bool $cryptPass=TRUE): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save user failed',
            SUCCESS => FALSE
        ];
        
//         $data[PASSWORD] = $data[PASSWORD]; // ?? $this->appConfig[UMS][PASS_DEFAULT];
        /* create hash of password */
        $password = $cryptPass ? password_hash($data[PASSWORD], PASSWORD_DEFAULT) : $data[PASSWORD];
//         $roleId = $data[ROLE] ?? $this->appConfig[DEFAULT_USER_ROLE];
        
        /* create sql query */
        $sql = 'INSERT INTO '.USERS_TABLE.' ('.NAME.', '.USERNAME.', '.EMAIL.', '.PASSWORD.', '.ROLE_ID_FRGN.', '.ENABLED.') VALUES ';
        $sql .= '(:name, :username, :email, :password, :role_id, :enabled)';
        $stmt = $this->conn->prepare($sql);
        /* execute sql query */
        $sqlData = [
            'name' => $data[NAME],
            'username' => $data[USERNAME],
            'email' => $data[EMAIL],
            'password' => $password,
            'role_id' => $data[ROLE_ID_FRGN],
            'enabled' => (int) $data[ENABLED]
        ];
        $stmt->execute($sqlData);
        
        /* if sql query success, then set success result */
        if ($stmt && $stmt->rowCount()) {
            $result[USER_ID] = $this->conn->lastInsertId();
            $result[MESSAGE] = 'New user saved successfully';
            $result[SUCCESS] = TRUE;
        /* else set error info result */
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        /* return result */
        return $result;
    }

    /* function to save new user */
    public function saveUserSetRegistrationDatetime(array $data, bool $cryptPass=TRUE): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save user failed',
            SUCCESS => FALSE
        ];

        $password = $cryptPass ? password_hash($data[PASSWORD], PASSWORD_DEFAULT) : $data[PASSWORD];

        /* create sql query */
        $sql = 'INSERT INTO '.USERS_TABLE.' ('.NAME.', '.USERNAME.', '.EMAIL.', '.PASSWORD.', '.ROLE_ID_FRGN.', '.ENABLED.', '.REGISTRATION_DATETIME.') VALUES ';
        $sql .= '(:name, :username, :email, :password, :role_id, :enabled, :reg_datetime)';
        $stmt = $this->conn->prepare($sql);
        /* execute sql query */
        $sqlData = [
            'name' => $data[NAME],
            'username' => $data[USERNAME],
            'email' => $data[EMAIL],
            'password' => $password,
            'role_id' => $data[ROLE_ID_FRGN],
            'enabled' => (int) $data[ENABLED],
            'reg_datetime' => $data[REGISTRATION_DATETIME]
        ];
        $stmt->execute($sqlData);
        
        /* if sql query success, then set success result */
        if ($stmt && $stmt->rowCount()) {
            $result[USER_ID] = $this->conn->lastInsertId();
            $result[MESSAGE] = 'New user saved successfully';
            $result[SUCCESS] = TRUE;
            /* else set error info result */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        /* return result */
        return $result;
    }

    /* fucntion to create locks for user */
    public function createUserLock($id) {
        $sql = 'INSERT INTO '.USER_LOCK_TABLE.' ('.USER_ID_FRGN.') VALUES (:id)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);
        
        /* if sql query success, then return user lock */
        if ($stmt->rowCount()) return $this->getUserLock($id);
        
        /* return result */
        return FALSE;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get user with roles */
    public function getUsers(string $orderBy=USER_ID, string $orderDir=DESC, string $search='', int $start=0, int $nRow=10) {
        /* set sql query */ 
        $sql = 'SELECT * FROM '.USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $data = [];
        /* if it is set, append search query */
        if (!empty($search)) {
            $sql .= ' WHERE '.USER_ID.'=:searchId OR ';
            $sql .= NAME.' LIKE :search OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= EMAIL.' LIKE :search OR ';
            $sql .= ROLE.' LIKE :search ';
            $data = [
                'searchId' => $search,
                'search' => "%$search%"
            ];
        }
        /* validate order by, order direction, start and num of row */
        $orderBy = in_array($orderBy, USERS_ORDER_BY_LIST) ? $orderBy : USER_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;

        /* prepare and execute sql query */
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

    /* function to get user by id */
    public function getUser(int $id, bool $unsetPassword = TRUE) {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('SELECT * FROM '.USERS_TABLE.' WHERE '.USER_ID.'=:id');
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        /* if success query and find user return user */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get user and role by id */
    public function getUserAndRole(int $id, bool $unsetPassword = TRUE) {
        /* prepare sql query and execute it */
        $sql = 'SELECT * FROM '.USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $sql .= ' WHERE '.USER_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        /* if success query and find user return user */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get user by email */
    public function getUserByEmail(string $email, bool $unsetPassword = TRUE) {
        /* validate email */
        if (!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) return FALSE;
        
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('SELECT * FROM '.USERS_TABLE.' WHERE '.EMAIL.'=:email');
        $stmt->execute(['email' => $email]);
        
        /* if sql success and find user, return it */
        if ($stmt && $user = $stmt->fetch(PDO::FETCH_OBJ)) {
            /* unset password if require */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get user by username */
    public function getUserByUsername(string $username, bool $unsetPassword = TRUE) {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('SELECT * FROM '.USERS_TABLE.' WHERE '.USERNAME.'=:username');
        $stmt->execute(['username' => $username]);

        /* if find user return it */
        if ($stmt && $user = $stmt->fetch(PDO::FETCH_OBJ)) {
            /* unset password if require */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get user and role by username */
    public function getUserAndRoleByUsername(string $username, bool $unsetPassword=TRUE) {
        /* prepare sql query and execute it */
        /* prepare sql query and execute it */
        $sql = 'SELECT * FROM '.USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $sql .= ' WHERE '.USERNAME.'=:username';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        
        /* if find user return it */
        if ($stmt && $user = $stmt->fetch(PDO::FETCH_OBJ)) {
            /* unset password if require */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get user lock property */
    public function getUserLock(int $id) {
        /* prepare sql query */
        $sql = 'SELECT * FROM '.USER_LOCK_TABLE.' WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        /* execute sql query */
        $stmt->execute([
            'id' => $id
        ]);
        
        /* check statement, get lock and return it */
        if ($stmt && ($userLock = $stmt->fetch(PDO::FETCH_ASSOC))) return $userLock;
        
        /* return fail result */
        return FALSE;
    }

    /* function to get user and locks property */
    public function getUserAndLock(int $id) {
        /* prepare sql query */
        $sql = 'SELECT * FROM '.USERS_TABLE.' JOIN ';
        $sql .= USER_LOCK_TABLE.' ON '.USER_ID.'='.USER_ID_FRGN;
        $sql .= ' WHERE '.USER_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        /* execute sql query */
        $stmt->execute([
            'id' => $id
        ]);
        
        /* check statement, get lock and return it */
        if ($stmt && ($userLock = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* unset password and return user */
            unset($userLock->{PASSWORD});
            return $userLock;
        }
        
        /* return fail result */
        return FALSE;
    }

    
    /* function to count the users on table */
    public function countAllUsers(string $search=''): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID;
        $data = [];
        /* if it is set, append search query */
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
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* function to count the enabled users on table */
    public function countEnabledUsers(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.USERS_TABLE;
        $sql .= ' WHERE '.ENABLED.' = 1 AND ('.EXPIRE_LOCK.' IS NULL OR '.EXPIRE_LOCK.' < CURRENT_TIMESTAMP())';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* function count users for advance search */
    public function countAdvanceSearchUsers(array $searchData): int {
        /* create sql query */
        $searchData = array_filter($searchData);
        $sql = 'SELECT COUNT(*) AS total FROM '.USERS_TABLE.' JOIN ';
        $sql .= ROLES_TABLE.' ON '.ROLE_ID_FRGN.'='.ROLE_ID.' WHERE ';
        /* append query search */
        if (isset($searchData[USER_ID])) $sql .= USER_ID.'=:'.USER_ID;
        else {
            $and = count($searchData)-1;
//             $keys = array_keys($searchData);
            foreach ($searchData as $key => $val) {
                $searchData[$key] = "%$val%";
                $sql .= "$key LIKE :$key";
                if ($and-- > 0) $sql .= ' AND ';
            }
        }
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($searchData);
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* ############# UPDATE FUNCTIONS ############# */

    /* function to update a user */
    public function changeUserId(int $oldId, int $newId): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Change user id failed',
            SUCCESS => FALSE
        ];
        
        /* set user param */
        $param = [
            'old_id' => $oldId,
            'new_id' => $newId
        ];

        /* disable foreign key check */
        $sql = 'SET FOREIGN_KEY_CHECKS=0;';
        /* set sql query */
        $sql .= 'UPDATE '.USERS_TABLE.' SET '.USER_ID.'=:new_id WHERE '.USER_ID.'=:old_id;';
        /* enable foreign key check */
        $sql .= 'SET FOREIGN_KEY_CHECKS=1;';
        /* get statement and execute query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($param);
        /* if success set success result */
        if ($stmt->rowCount() || $stmt->errorCode() == 0) {
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'User id successfully updated';
            /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        return $result;
    }

    /* function to update a user */
    public function updateUser(int $id, array $data): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Update user failed',
            SUCCESS => FALSE
        ];

        /* set user param */
        $param = [
            'name' => $data[NAME],
            'username' => $data[USERNAME],
            'id' => $id
        ];

        /* set sql query */
        $sql = 'UPDATE '.USERS_TABLE.' SET '.NAME.'=:name, '.USERNAME.'=:username';

        /* if is setted add params */
        if (isset($data[EMAIL])) {
            $sql .= ', '.EMAIL.'=:email';
            $param['email'] = $data[EMAIL];
        }
        if (isset($data[ENABLED])) {
            $sql .= ', '.ENABLED.'=:enabled';
            $param['enabled'] = $data[ENABLED];
        }
        if (isset($data[ROLE_ID_FRGN]) && $data[ROLE_ID_FRGN] !== '') {
            $sql .= ', '.ROLE_ID_FRGN.'=:role_id';
            $param['role_id'] = $data[ROLE_ID_FRGN];
        }
        $sql .= ' WHERE '.USER_ID.'=:id';

        /* get statement and execute query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($param);
        /* if success set success result */
        if ($stmt->rowCount() || $stmt->errorCode() == 0) {
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'User successfully updated';
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        return $result;
    }

    /* function to disable a user */
    public function disabledUser(int $id) : array {
        /* set fail result */
        $result = [
            MESSAGE => 'Fail disable user',
            SUCCESS => FALSE
        ];

        /* prepare and execute sql query */
        $stmt = $this->conn->prepare('UPDATE '.USERS_TABLE.' SET '.ENABLED.'=0 WHERE '.USER_ID.'=:id');
        $stmt->execute(['id' => $id]);

        /* if success then set success result */
        if($stmt->rowCount()) {
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'User disabled';
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        return $result;
    }

    /* function to enable a new email */
    public function updateEmail(int $userId, string $email): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save new email failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.USERS_TABLE.' SET '.EMAIL.'=:email WHERE '.USER_ID.'=:id');
        $stmt->execute([
            'id' => $userId,
            'email' => $email
        ]);
        
        /* if sql success, then set success result */
        if ($stmt->rowCount()) {
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'New email saved';
        /* else append error info on result */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        return $result;
    }

    /* function to set a new password */
    public function updatePassword(int $id, string $password): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Update password failed',
            SUCCESS => FALSE
        ];

        /* get hash of pasword */
        $password = password_hash($password, PASSWORD_DEFAULT);
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.USERS_TABLE.' SET '.PASSWORD.'=:pass WHERE '.USER_ID.'=:id');
        $stmt->execute([
            'pass' => $password,
            'id' => $id
        ]);

        /* if sql success set success result */
        if($stmt->rowCount()) {
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'Password successfully updated';
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        return $result;
    }

    /* ###### LOCKS ###### */


    /* function to lock a user */
    public function lockUser(int $id, string $expireLock): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Lock user failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute */
        $stmt = $this->conn->prepare('UPDATE '.USERS_TABLE.' SET '.EXPIRE_LOCK.'=:expireLock WHERE '.USER_ID.'=:id');
        $stmt->execute(compact('expireLock', 'id'));
        
        /* if query success set success result */
        if($stmt->rowCount()) {
            $result[MESSAGE] = 'User successfully locked';
            $result[SUCCESS] = TRUE;
            /* else set error info */
        } else $result['errorInfo'] = $stmt->errorInfo();
        
        /* return result */
        return $result;
    }

    /* ### SET ### */

    /* function to set the count wrong password */
    public function setCountWrongPass(int $id, int $countWrongPass): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Set wrong password counter failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.USER_LOCK_TABLE.' SET '.COUNT_WRONG_PASSWORDS.'=:countWrongPass WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(compact('countWrongPass', 'id'));
        
        /* if query success */
        if($stmt->rowCount()) {
            /* set success result */
            $result[MESSAGE] = 'Wrong password counter successfully setted';
            $result[SUCCESS] = TRUE;
            /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        /* return result */
        return $result;
    }

    /* function to set count user locks */
    public function setCountUserLocks(int $id, int $countLocks): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Set locks counter failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.USER_LOCK_TABLE.' SET '.COUNT_LOCKS.'=:countLocks WHERE '.USER_ID_FRGN.'=:id');
        $stmt->execute(compact('countLocks', 'id'));
        
        /* if query success */
        if($stmt->rowCount()) {
            /* set success result */
            $result[MESSAGE] = 'User locks counter successfully setted';
            $result[SUCCESS] = TRUE;
            /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        /* return result */
        return $result;
    }

    /* ### RESET ### */

    /* function to reset wrong passowrds of user */
    public function wrongPasswordsReset(int $userId, string $expireDatetime='NULL'): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Reset wrong password failed',
            SUCCESS => FALSE
        ];

        /* prepare sql query and execute it */
        $sql = 'UPDATE '.USER_LOCK_TABLE.' SET '.COUNT_WRONG_PASSWORDS.'=0, '.EXPIRE_TIME_WRONG_PASSWORD.'=:datetime ';
        $sql .= 'WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'datetime' => $expireDatetime,
            'id' => $userId
        ]);
        
        /* if sql query success set success result */
        if($stmt->rowCount()) {
            $result[MESSAGE] = 'Datetime reset wrong password successfully setted';
            $result[SUCCESS] = TRUE;
            /* else append error info on result */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        /* return result */
        return $result;
    }

    /* function to unlock the user */
    public function lockUserReset(int $id): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Locks user failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.USERS_TABLE.' JOIN ';
        $sql .= USER_LOCK_TABLE.' ON '.USER_ID.'='.USER_ID_FRGN;
        $sql .= ' SET '.EXPIRE_LOCK.'=NULL, '.EXPIRE_TIME_WRONG_PASSWORD.'=NULL, '.COUNT_WRONG_PASSWORDS.'=0, '.COUNT_LOCKS.'=0';
        $sql .= ' WHERE '.USER_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        if($stmt->errorCode() == 0) {
            $result[MESSAGE] = 'User locks successfully resetted';
            $result[SUCCESS] = TRUE;
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        return $result;
    }

    /* function to reset user locks */
    public function lockCountsReset(int $id): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Reset locks failed',
            SUCCESS => FALSE
        ];

        /* prepare sql query and execute it */
        $sql = 'UPDATE '.USER_LOCK_TABLE.' SET '.EXPIRE_TIME_WRONG_PASSWORD.'=NULL, '.COUNT_WRONG_PASSWORDS.'=0, '.COUNT_LOCKS.'=0 WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        if($stmt->rowCount()) {
            $result[MESSAGE] = 'Wrong password lock successfully reset';
            $result[SUCCESS] = TRUE;
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        return $result;
    }

    /* ############# DELETE FUNCTIONS ############# */

    /* function to delete a user */
    public function deleteUser(int $id): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Delete user failed',
            SUCCESS => FALSE
        ];

        /* disable foreign key check */
        $sql = 'SET FOREIGN_KEY_CHECKS=0;';
        /* delete query */
        $sql .= 'DELETE FROM '.USERS_TABLE.' WHERE '.USER_ID.'=:id;';
        /* enable foreign key check */
        $sql .= 'SET FOREIGN_KEY_CHECKS=1;';
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        /* if success then set success result */
        if ($stmt->errorCode() == 0){
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'User deleted';
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        return $result;
    }
}
