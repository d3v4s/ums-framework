<?php
namespace app\models;

use \PDO;
use \DateTime;

class User {
    protected $conn;
    protected $appConfig;

    public function __construct(PDO $conn, array $appConfig) {
        $this->conn = $conn;
        $this->appConfig = $appConfig ?? getConfig();
    }

    public function lockUser(int $id): array {
        $result = [
            'message' => 'Lock user failed',
            'success' => FALSE
        ];

        $nLocks = (int) $this->getUser($id)->n_locks;
        $nLocks++;
        $datetime = new DateTime();
        $datetime->modify($this->appConfig['app']['userLockTime']);
        $datetimeUnlock = $datetime->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare('UPDATE users SET n_locks=:nLocks, datetime_unlock_user=:datetimeUnlock WHERE id=:id');
        $stmt->execute(compact('nLocks', 'datetimeUnlock', 'id'));
        
        if($stmt->rowCount()) {
            $result['message'] = 'User successfully locked';
            $result['success'] = TRUE;
        } else $result['errorInfo'] = $stmt->errorInfo();
        
        return $result;
    }

    public function incrementWrongPass(int $id): array {
        $result = [
            'message' => 'Increment n. wrong password failed',
            'success' => FALSE
        ];

        $nWrongPass = (int) $this->getUser($id)->n_wrong_password;
        $nWrongPass++;
        $stmt = $this->conn->prepare('UPDATE users SET n_wrong_password=:nWrongPass WHERE id=:id');
        $stmt->execute(compact('nWrongPass', 'id'));

        if($stmt->rowCount()) {
            $result['message'] = 'N. wrong password successfully increased';
            $result['success'] = TRUE;
        } else $result['errorInfo'] = $stmt->errorInfo();
        
        return $result;
    }

    public function setDatetimeResetWrongPassword(int $id): array {
        $result = [
            'message' => 'Set datetime reset wrong password failed',
            'success' => FALSE
        ];

        $datetime = new DateTime();
        $datetime->modify($this->appConfig['app']['passwordTryTime']);
        $datetimeReset = $datetime->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare('UPDATE users SET n_wrong_password = 0, datetime_reset_wrong_password=:datetimeReset WHERE id=:id');
        $stmt->execute(compact('datetimeReset', 'id'));
        
        if($stmt->rowCount()) {
            $result['message'] = 'Datetime reset wrong password successfully setted';
            $result['success'] = TRUE;
        } else $result['errorInfo'] = $stmt->errorInfo();
        
        return $result;
    }

    public function resetDatetimeAndNWrongPassword(int $id): array {
        $result = [
            'message' => 'Reset datetime and n. wrong password failed',
            'success' => FALSE
        ];

        $stmt = $this->conn->prepare('UPDATE users SET n_wrong_password=0, datetime_reset_wrong_password=NULL WHERE id=:id');
        $stmt->execute(compact('id'));
        
        if($stmt->rowCount()) {
            $result['message'] = 'Datetime and n. wrong password successfully reset';
            $result['success'] = TRUE;
        } else $result['errorInfo'] = $stmt->errorInfo();
        
        return $result;
    }

    public function resetLockUser(int $id): array {
        $result = [
            'message' => 'Reset lock user failed',
            'success' => FALSE
        ];
        
        $stmt = $this->conn->prepare('UPDATE users SET n_locks=0, datetime_unlock_user=NULL WHERE id=:id');
        $stmt->execute(compact('id'));
        
        if($stmt->rowCount()) {
            $result['message'] = 'Lock user successfully reset';
            $result['success'] = TRUE;
        } else $result['errorInfo'] = $stmt->errorInfo();
        
        return $result;
    }

    public function resetWrongPasswordLock(int $id): array {
        $result = [
            'message' => 'Reset lock wrong password failed',
            'success' => FALSE
        ];
        
        $stmt = $this->conn->prepare('UPDATE users SET n_wrong_password=0, datetime_reset_wrong_password=NULL, n_locks=0, datetime_unlock_user=NULL WHERE id=:id');
        $stmt->execute(compact('id'));
        
        if($stmt->rowCount()) {
            $result['message'] = 'Wrong password lock successfully reset';
            $result['success'] = TRUE;
        } else $result['errorInfo'] = $stmt->errorInfo();
        
        return $result;
    }

    public function countUsers(string $search = ''): int {
        $sql = 'SELECT COUNT(*) AS total FROM users';
        if (!empty($search)) {
            $sql .= ' WHERE id = :searchId OR ';
            $sql .= 'name LIKE :search OR ';
            $sql .= 'username LIKE :search OR ';
            $sql .= 'email LIKE :search OR ';
            $sql .= 'roletype LIKE :search';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'searchId' => $search,
            'search' => '%' . $search . '%'
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function updateUserPass(int $id, string $password): array {
        $result = [
            'message' => 'Update password failed',
            'success' => FALSE
        ];
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('UPDATE users SET password=:pass WHERE id=:id');
        
        $stmt->execute([
            'pass' => $password,
            'id' => $id
        ]);
        
        if($stmt->rowCount()) {
            $result['success'] = TRUE;
            $result['message'] = 'Password successfully updated';
        } else $result['errorInfo'] = $stmt->errorInfo();
            
        return $result;
    }

    public function getUserByTokenResetPassword(string $token, bool $unsetPassword = TRUE) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE token_reset_pass = :token');
        $stmt->execute(['token' => $token]);
        
        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        return FALSE;
    }

    public function getUserByTokenEnabler(string $token, bool $unsetPassword = TRUE) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE token_account_enabler = :token');
        $stmt->execute(['token' => $token]);

        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        return FALSE;
    }

    public function getUserByTokenConfirmEmail(string $token, bool $unsetPassword = TRUE) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE token_confirm_email = :token');
        $stmt->execute(['token' => $token]);

        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        return FALSE;
    }

    public function removeTokenResetPassword(int $id): bool {
        $stmt = $this->conn->prepare('UPDATE users SET token_reset_pass = NULL WHERE id=:id');
        $stmt->execute(['id' => $id]);
        
        if($stmt->rowCount()) return TRUE;
        
        return FALSE;
    }

    public function removeTokenConfirmEmail(int $id): bool {
        $stmt = $this->conn->prepare('UPDATE users SET token_confirm_email = NULL WHERE id=:id');
        $stmt->execute(['id' => $id]);
        
        if($stmt->rowCount()) return TRUE;
        
        return FALSE;
    }

    public function removeNewEmailAndToken(int $id): bool {
        $stmt = $this->conn->prepare('UPDATE users SET new_email = NULL, token_confirm_email = NULL WHERE id=:id');
        $stmt->execute(['id' => $id]);
        
        if($stmt->rowCount()) return TRUE;
        
        return FALSE;
    }

    public function removeTokenEnabler(int $id): bool {
        $stmt = $this->conn->prepare('UPDATE users SET token_account_enabler = NULL WHERE id=:id');
        $stmt->execute(['id' => $id]);
        
        if($stmt->rowCount()) return TRUE;
        
        return FALSE;
    }
    
    public function createTokenResetPassword(int $id): array {
        $result = [
            'message' => 'Reset passwor failed',
            'success' => FALSE
        ];
        $datetime = new DateTime();
        $datetime->modify($this->appConfig['app']['expirationTimeResetPassword']);
        $datetimeExpire = $datetime->format('Y-m-d H:i:s');

        $stmt = $this->conn->prepare('UPDATE users SET token_reset_pass=:token, datetime_req_reset_pass_expire=:datetimeExpire WHERE id=:id');
        $stmt->execute([
            'id' => $id,
            'token' => $this->getNewTokenResetPassword(),
            'datetimeExpire' => $datetimeExpire 
        ]);

        if($stmt->rowCount()) {
            $result['success'] = TRUE;
            $result['message'] = 'Link for password reset was generated';
        } else $result['errorInfo'] = $stmt->errorInfo();

        return $result;
    }

    public function enableUser(int $id): array {
        $result = [
            'message' => 'Fail enable user',
            'success' => FALSE
        ];

        $stmt = $this->conn->prepare('UPDATE users SET enabled=1 WHERE id=:id');
        $stmt->execute(['id' => $id]);

        if($stmt->rowCount()) {
            $result['success'] = TRUE;
            $result['message'] = 'User enabled';
        } else $result['errorInfo'] = $stmt->errorInfo();

        return $result;
    }

    public function disabledUser(int $id) : array {
        $result = [
            'message' => 'Fail disable user',
            'success' => FALSE
        ];

        $stmt = $this->conn->prepare('UPDATE users SET enabled=0 WHERE id=:id');
        $stmt->execute(['id' => $id]);

        if($stmt->rowCount()) {
            $result['success'] = TRUE;
            $result['message'] = 'User disabled';
        } else $result['errorInfo'] = $stmt->errorInfo();

        return $result;
    }

    public function confirmEmail(int $id): array {
        $result = [
            'message' => 'Confirm email failed',
            'success' => FALSE
        ];
        $email = $this->getUser($id)->new_email;
        $stmt = $this->conn->prepare('UPDATE users SET email=:email, new_email = NULL WHERE id=:id');
        $stmt->execute([
            'id' => $id,
            'email' => $email
        ]);
        
        if ($stmt->rowCount()) {
            $result['success'] = TRUE;
            $result['message'] = 'Email confirmed';
        } else $result['errorInfo'] = $stmt->errorInfo();

        return $result;
    }

    public function saveUser(array $data, bool $generateTokenAccountEnabler = FALSE): array {
        $result = [
            'message' => 'Save user failed',
            'success' => FALSE
        ];

        $data['pass'] = $data['pass'] ?? $this->appConfig['app']['passDefault'];
        $password = password_hash($data['pass'], PASSWORD_DEFAULT);
        $roletype = $data['roletype'] ?? 'user';

        $sql = 'INSERT INTO users (name, username, email, password, roletype, enabled, token_account_enabler) VALUES ';
        $sql .= "(:name, :username, :email, :password, :roletype, :enabled, :token_account_enabler)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(
            [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $password,
                'roletype' => $roletype,
                'enabled' => (int) $data['enabled'],
                'token_account_enabler' => $generateTokenAccountEnabler ? $this->getNewTokenEnabler() : NULL
            ]
        );
        
        
        if ($stmt->rowCount()) {
            $result['id'] = $this->conn->lastInsertId();
            $result['success'] = TRUE;
            $result['message'] = 'New user saved successfully';
        } else $result['error-info'] = $stmt->errorInfo();

        return $result;
    }
    
    public function deleteUser(int $id): array {
        $result = [
            'message' => 'Delete user failed',
            'success' => FALSE
        ];
        $stmt = $this->conn->prepare('DELETE FROM users WHERE id = :id');
        
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount()){
            $result['success'] = TRUE;
            $result['message'] = 'User deleted';
        } else $result['errorInfo'] = $stmt->errorInfo();

        return $result;
    }
    
    public function getUserByEmail(string $email, bool $unsetPassword = TRUE) {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email) return FALSE;
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        return FALSE;
    }

    public function getUserByNewEmail(string $newEmail, bool $unsetPassword = TRUE) {
        $email = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
        if (!$email) return FALSE;
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE new_email = :email');
        $stmt->execute(['email' => $newEmail]);
        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        return FALSE;
    }

    public function getUserByUsername(string $username, bool $unsetPassword = TRUE) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        return FALSE;
    }

    public function getUser(int $id, bool $unsetPassword = TRUE) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        return FALSE;
    }

    public function getUsers(string $orderBy = 'id', string $orderDir = 'desc', string $search = '', int $start = 0, int $nRow = 10, bool $unsetPassword = TRUE) {
        $orderBy = in_array($orderBy, getList('orderBy')) ? $orderBy : 'id';
        $orderDir = strtoupper($orderDir);
        $orderDir = in_array($orderDir, getList('orderDir')) ? $orderDir : 'DESC';
        $sql = 'SELECT * FROM users ';
        if (!empty($search)) {
            $sql .= "WHERE id = :searchId OR ";
            $sql .= "name LIKE :search OR ";
            $sql .= "username LIKE :search OR ";
            $sql .= "email LIKE :search OR ";
            $sql .= 'roletype LIKE :search ';
        }
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        $sql .= "ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $data = empty($search)? [] : [
            'searchId' => $search,
            'search' => '%'.$search.'%'
        ];
        $stmt->execute($data);
        if ($stmt) {
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            if ($unsetPassword) foreach ($users as $user) unset($user->password);
            return $users;
        }
        return FALSE;
    }

    public function updateUser(int $id, array $data, bool $generateTokenConfirmEmail = FALSE): array {
        $result = [
            'message' => 'Update user failed',
            'success' => FALSE
        ];
        $param = [
            'name' => $data['name'],
            'username' => $data['username'],
            'id' => $id
        ];

        $sql = 'UPDATE users SET name=:name, username=:username';
        if (isset($data['enabled'])) {
            $sql .= ', enabled=:enabled';
            $param['enabled'] = $data['enabled'];
        }
        if (isset($data['roletype']) && $data['roletype'] !== '') {
            $sql .= ', roletype=:roletype';
            $param['roletype'] = $data['roletype']; 
        }
        if ($generateTokenConfirmEmail) {
            $sql .= ', new_email=:new_email, token_confirm_email=:token_confirm_email';
            $param['new_email'] = $data['email'];
            $param['token_confirm_email'] = $this->getNewTokenConfirmEmail();
        } else {
            $sql .= ', email=:email';
            $param['email'] = $data['email'];
        }
        $sql .= ' WHERE id=:id';

        $stmt = $this->conn->prepare($sql);

        $stmt->execute($param);
        if($stmt->rowCount() || $stmt->errorCode() == 0) {
            $result['success'] = TRUE;
            $result['message'] = 'User successfully updated';
        } else $result['errorInfo'] = $stmt->errorInfo();

        return $result;
    }

    private function getNewTokenResetPassword(): string {
        do $token = bin2hex(random_bytes(32));
        while ($this->getUserByTokenResetPassword($token));
        
        return $token;
    }

    private function getNewTokenEnabler(): string {
        do $token = bin2hex(random_bytes(32));
        while ($this->getUserByTokenEnabler($token));

        return $token;
    }

    private function getNewTokenConfirmEmail(): string {
        do $token = bin2hex(random_bytes(32));
        while ($this->getUserByTokenConfirmEmail($token));

        return $token;
    }
}

