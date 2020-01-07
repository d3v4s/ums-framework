<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on ums db tables
 * @author Andrea Serra (DevAS) https://devas.info
 */
class User {
    protected $conn;
    protected $appConfig;

    public function __construct(PDO $conn, array $appConfig=NULL) {
        $this->conn = $conn;
//         $this->appConfig = $appConfig ?? getConfig();
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ############# CREATE FUNCTIONS ############# */

    /* function to save new user */
    public function saveUser(array $data): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save user failed',
            SUCCESS => FALSE
        ];
        
//         $data[PASSWORD] = $data[PASSWORD]; // ?? $this->appConfig[UMS][PASS_DEFAULT];
        /* create hash of password */
        $password = password_hash($data[PASSWORD], PASSWORD_DEFAULT);
//         $roleId = $data[ROLE] ?? $this->appConfig[DEFAULT_USER_ROLE];
        
        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.USERS_TABLE.' ('.NAME.', '.USERNAME.', '.EMAIL.', '.PASSWORD.', '.ROLE_ID_FRGN.', '.ENABLED.', '.REGISTRATION_DATETIME.') VALUES ';
        $sql .= "(:name, :username, :email, :password, :role_id, :enabled, :reg_datetime)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(
            [
                'name' => $data[NAME],
                'username' => $data[USERNAME],
                'email' => $data[EMAIL],
                'password' => $password,
                'role_id' => $data[ROLE],
                'enabled' => (int) $data[ENABLED],
                'reg_datetime' => $data[REGISTRATION_DATETIME] ?? NULL
                
            ]
            );
        
        /* if sql query success, then set success result */
        if ($stmt->rowCount()) {
            $result[USER_ID] = $this->conn->lastInsertId();
            $result[MESSAGE] = 'New user saved successfully';
            $result[SUCCESS] = TRUE;
        /* else set error info result */
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        /* return result */
        return $result;
    }

    /* ############# READ FUNCTIONS ############# */

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

//     /* function to get user by email enabler token */
//     public function getUserByEmailEnablerToken(string $token, bool $unsetPassword=TRUE) {
//         /* prepare sql query, then execute */
//         $sql = 'SELECT * FROM '.PENDING_EMAILS_TABLE.' JOIN ';
//         $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
//         $sql .= ' WHERE '.ENABLER_TOKEN.' = :token';
//         $stmt = $this->conn->prepare($sql);
//         $stmt->execute(['token' => $token]);
        
//         /* if find user return it */
//         if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
//             /* if require unset password */
//             if ($unsetPassword) unset($user->{PASSWORD});
//             return $user;
//         }
//         /* else return false */
//         return FALSE;
//     }
    

    /* ############# UPDATE FUNCTIONS ############# */

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
    
    /* function to set the count wrong password */
    public function setCountWrongPass(int $id, int $countWrongPass): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Set counter wrong password failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.USER_LOCK_TABLE.' SET '.COUNT_WRONG_PASSWORDS.'=:countWrongPass WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(compact('countWrongPass', 'id'));
        
        /* if query success */
        if($stmt->rowCount()) {
            /* set success result */
            $result[MESSAGE] = 'Counter wrong password successfully setted';
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
            MESSAGE => 'Set counter wrong password failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.USER_LOCK_TABLE.' SET '.COUNT_LOCKS.'=:countLocks WHERE '.USER_ID_FRGN.'=:id');
        $stmt->execute(compact('countLocks', 'id'));
        
        /* if query success */
        if($stmt->rowCount()) {
            /* set success result */
            $result[MESSAGE] = 'Counter user locks successfully setted';
            $result[SUCCESS] = TRUE;
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();
        
        /* return result */
        return $result;
    }
    
    /* function to reset wrong passowrds of user */
    public function resetWrongPasswords(int $userId, string $expireDatetime=NULL): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Reset wrong password failed',
            SUCCESS => FALSE
        ];
        
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.USER_LOCK_TABLE.' SET '.COUNT_WRONG_PASSWORD.'=0, '.EXPIRE_TIME_WRONG_PASSWORD.'=:datetime ';
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
    
    /* function to enable a new email */
    public function updateEmail(int $userId, string $email): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Confirm email failed',
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
            $result[MESSAGE] = 'Email confirmed';
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

    /* function to reset user locks */
    public function resetLockCounts(int $id): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Reset locks failed',
            SUCCESS => FALSE
        ];

        /* prepare sql query and execute it */
        $sql = 'UPDATE '.USER_LOCK_TABLE.' SET '.COUNT_WRONG_PASSWORDS.'=0, '.COUNT_LOCKS.'=0 WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
    
        if($stmt->rowCount()) {
            $result['message'] = 'Wrong password lock successfully reset';
            $result['success'] = TRUE;
        } else $result['errorInfo'] = $stmt->errorInfo();

        return $result;
    }

    /* ############# DELETE FUNCTIONS ############# */

    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
//     /* function to enable a user */
//     public function enableUser(int $id): array {
//         $result = [
//             'message' => 'Fail enable user',
//             'success' => FALSE
//         ];
        
//         $stmt = $this->conn->prepare('UPDATE users SET enabled=1 WHERE id=:id');
//         $stmt->execute(['id' => $id]);
        
//         if($stmt->rowCount()) {
//             $result['success'] = TRUE;
//             $result['message'] = 'User enabled';
//         } else $result['errorInfo'] = $stmt->errorInfo();
        
//         return $result;
//     }
    
    
    
    
    
    
    
    
//     public function resetDatetimeAndNWrongPassword(int $id): array {
//         $result = [
//             'message' => 'Reset datetime and n. wrong password failed',
//             'success' => FALSE
//         ];

//         $stmt = $this->conn->prepare('UPDATE users SET n_wrong_password=0, datetime_reset_wrong_password=NULL WHERE id=:id');
//         $stmt->execute(compact('id'));
        
//         if($stmt->rowCount()) {
//             $result['message'] = 'Datetime and n. wrong password successfully reset';
//             $result['success'] = TRUE;
//         } else $result['errorInfo'] = $stmt->errorInfo();
        
//         return $result;
//     }

//     public function resetLockUser(int $id): array {
//         $result = [
//             'message' => 'Reset lock user failed',
//             'success' => FALSE
//         ];
        
//         $stmt = $this->conn->prepare('UPDATE users SET n_locks=0, datetime_unlock_user=NULL WHERE id=:id');
//         $stmt->execute(compact('id'));
        
//         if($stmt->rowCount()) {
//             $result['message'] = 'Lock user successfully reset';
//             $result['success'] = TRUE;
//         } else $result['errorInfo'] = $stmt->errorInfo();
        
//         return $result;
//     }

//     public function resetWrongPasswordLock(int $id): array {
//         $result = [
//             'message' => 'Reset lock wrong password failed',
//             'success' => FALSE
//         ];
        
//         $stmt = $this->conn->prepare('UPDATE users SET n_wrong_password=0, datetime_reset_wrong_password=NULL, n_locks=0, datetime_unlock_user=NULL WHERE id=:id');
//         $stmt->execute(compact('id'));
        
//         if($stmt->rowCount()) {
//             $result['message'] = 'Wrong password lock successfully reset';
//             $result['success'] = TRUE;
//         } else $result['errorInfo'] = $stmt->errorInfo();
        
//         return $result;
//     }

//     public function countUsers(string $search = ''): int {
//         $sql = 'SELECT COUNT(*) AS total FROM users';
//         if (!empty($search)) {
//             $sql .= ' WHERE id = :searchId OR ';
//             $sql .= 'name LIKE :search OR ';
//             $sql .= 'username LIKE :search OR ';
//             $sql .= 'email LIKE :search OR ';
//             $sql .= 'roletype LIKE :search';
//         }
//         $stmt = $this->conn->prepare($sql);
//         $stmt->execute([
//             'searchId' => $search,
//             'search' => '%' . $search . '%'
//         ]);
//         return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
//     }

//     public function getUserByTokenResetPassword(string $token, bool $unsetPassword = TRUE) {
//         $stmt = $this->conn->prepare('SELECT * FROM users WHERE token_reset_pass = :token');
//         $stmt->execute(['token' => $token]);
        
//         if ($stmt) {
//             $user = $stmt->fetch(PDO::FETCH_OBJ);
//             if ($unsetPassword) unset($user->password);
//             return $user;
//         }
//         return FALSE;
//     }


//     /*function to remove password reset token */
//     public function removePasswordResetToken(int $id): bool {
//         /* prepare sql query, then execute */
//         $stmt = $this->conn->prepare('UPDATE users SET token_reset_pass = NULL WHERE id=:id');
//         $stmt->execute(['id' => $id]);
        
//         if($stmt->rowCount()) return TRUE;
        
//         return FALSE;
//     }

//     public function removeNewEmailAndToken(int $id): bool {
//         $stmt = $this->conn->prepare('UPDATE users SET new_email = NULL, token_confirm_email = NULL WHERE id=:id');
//         $stmt->execute(['id' => $id]);
        
//         if($stmt->rowCount()) return TRUE;
        
//         return FALSE;
//     }

    
//     public function createTokenResetPassword(int $id): array {
//         $result = [
//             'message' => 'Reset passwor failed',
//             'success' => FALSE
//         ];
//         $datetime = new DateTime();
//         $datetime->modify($this->appConfig['app']['expirationTimeResetPassword']);
//         $datetimeExpire = $datetime->format('Y-m-d H:i:s');

//         $stmt = $this->conn->prepare('UPDATE users SET token_reset_pass=:token, datetime_req_reset_pass_expire=:datetimeExpire WHERE id=:id');
//         $stmt->execute([
//             'id' => $id,
//             'token' => $this->getNewResetPasswordToken(),
//             'datetimeExpire' => $datetimeExpire 
//         ]);

//         if($stmt->rowCount()) {
//             $result['success'] = TRUE;
//             $result['message'] = 'Link for password reset was generated';
//         } else $result['errorInfo'] = $stmt->errorInfo();

//         return $result;
//     }

//     public function disabledUser(int $id) : array {
//         $result = [
//             'message' => 'Fail disable user',
//             'success' => FALSE
//         ];

//         $stmt = $this->conn->prepare('UPDATE users SET enabled=0 WHERE id=:id');
//         $stmt->execute(['id' => $id]);

//         if($stmt->rowCount()) {
//             $result['success'] = TRUE;
//             $result['message'] = 'User disabled';
//         } else $result['errorInfo'] = $stmt->errorInfo();

//         return $result;
//     }


//     public function deleteUser(int $id): array {
//         $result = [
//             'message' => 'Delete user failed',
//             'success' => FALSE
//         ];
//         $stmt = $this->conn->prepare('DELETE FROM users WHERE id = :id');
        
//         $stmt->execute(['id' => $id]);
//         if ($stmt->rowCount()){
//             $result['success'] = TRUE;
//             $result['message'] = 'User deleted';
//         } else $result['errorInfo'] = $stmt->errorInfo();

//         return $result;
//     }

//     public function getUserByNewEmail(string $newEmail, bool $unsetPassword = TRUE) {
//         $email = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
//         if (!$email) return FALSE;
//         $stmt = $this->conn->prepare('SELECT * FROM users WHERE new_email = :email');
//         $stmt->execute(['email' => $newEmail]);
//         if ($stmt) {
//             $user = $stmt->fetch(PDO::FETCH_OBJ);
//             if ($unsetPassword) unset($user->password);
//             return $user;
//         }
//         return FALSE;
//     }

//     public function getUsers(string $orderBy = 'id', string $orderDir = 'desc', string $search = '', int $start = 0, int $nRow = 10, bool $unsetPassword = TRUE) {
//         $orderBy = in_array($orderBy, getList('orderBy')) ? $orderBy : 'id';
//         $orderDir = strtoupper($orderDir);
//         $orderDir = in_array($orderDir, getList('orderDir')) ? $orderDir : 'DESC';
//         $sql = 'SELECT * FROM users ';
//         if (!empty($search)) {
//             $sql .= "WHERE id = :searchId OR ";
//             $sql .= "name LIKE :search OR ";
//             $sql .= "username LIKE :search OR ";
//             $sql .= "email LIKE :search OR ";
//             $sql .= 'roletype LIKE :search ';
//         }
//         $start = is_numeric($start) ? $start : 0;
//         $nRow = is_numeric($nRow) ? $nRow : 20;
//         $sql .= "ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
//         $stmt = $this->conn->prepare($sql);
//         $data = empty($search)? [] : [
//             'searchId' => $search,
//             'search' => '%'.$search.'%'
//         ];
//         $stmt->execute($data);
//         if ($stmt) {
//             $users = $stmt->fetchAll(PDO::FETCH_OBJ);
//             if ($unsetPassword) foreach ($users as $user) unset($user->password);
//             return $users;
//         }
//         return FALSE;
//     }

//     public function updateUser(int $id, array $data, bool $generateTokenConfirmEmail = FALSE): array {
//         $result = [
//             'message' => 'Update user failed',
//             'success' => FALSE
//         ];
//         $param = [
//             'name' => $data['name'],
//             'username' => $data['username'],
//             'id' => $id
//         ];

//         $sql = 'UPDATE users SET name=:name, username=:username';
//         if (isset($data['enabled'])) {
//             $sql .= ', enabled=:enabled';
//             $param['enabled'] = $data['enabled'];
//         }
//         if (isset($data['roletype']) && $data['roletype'] !== '') {
//             $sql .= ', roletype=:roletype';
//             $param['roletype'] = $data['roletype']; 
//         }
//         if ($generateTokenConfirmEmail) {
//             $sql .= ', new_email=:new_email, token_confirm_email=:token_confirm_email';
//             $param['new_email'] = $data['email'];
//             $param['token_confirm_email'] = $this->getNewConfirmEmailToken();
//         } else {
//             $sql .= ', email=:email';
//             $param['email'] = $data['email'];
//         }
//         $sql .= ' WHERE id=:id';

//         $stmt = $this->conn->prepare($sql);

//         $stmt->execute($param);
//         if($stmt->rowCount() || $stmt->errorCode() == 0) {
//             $result['success'] = TRUE;
//             $result['message'] = 'User successfully updated';
//         } else $result['errorInfo'] = $stmt->errorInfo();

//         return $result;
//     }

//     /* function to get user lock property */
//     public function getUserLock(int $id) {
//         /* set fail results */
//         $result = [
//             MESSAGE => 'Invalid id',
//             SUCCESS => FALSE
//         ];
        
//         /* prepare sql query */
//         $sql = 'SELECT * FROM '.USER_LOCK_TABLE.' WHERE '.USER_ID_FRGN.' = :id';
//         $stmt = $this->conn->prepare($sql);
//         /* execute sql query */
//         $stmt->execute([
//             'id' => $id
//         ]);
        
//         /* check statement, get lock property and return success result */
//         if ($stmt && ($userLock = $stmt->fetch(PDO::FETCH_OBJ))) {
//             unset($result[MESSAGE]);
//             $result[SUCCESS] = TRUE;
//             $result[USER] = $userLock;
//             return $result;
//         }
        
//         /* return fail result */
//         return $result;
//     }

//     private function getNewResetPasswordToken(): string {
//         /* generates a random string until it's not unique */
//         do $token = getSecureRandomString();
//         while ($this->getUserByTokenResetPassword($token));

//         /* return unique token */
//         return $token;
//     }

//     private function getNewConfirmEmailToken(): string {
//         /* generates a random string until it's not unique */
//         do $token = getSecureRandomString();
//         while ($this->getUserByEmailEnablerToken($token));

//         /* return unique token */
//         return $token;
//     }
}

