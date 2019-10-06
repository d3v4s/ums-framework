<?php
namespace app\models;

use \PDO;

class User {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function countUsers(string $search = '') {
        $sql = 'SELECT COUNT(*) AS total FROM users';
        if (!empty($search)) {
            $sql .= ' WHERE id = \':search\' OR ';
            $sql .= 'name LIKE \'%:search%\' OR ';
            $sql .= 'username LIKE \'%:search%\' OR ';
            $sql .= 'email LIKE \'%:search%\' OR ';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'search' => $search
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function updateUserPass(string $id, string $password) {
        $result = [
            'message' => 'FAIL UPDATE USER',
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
            unset($result['message']);
        } else
            $result['error'] = $stmt->errorInfo();
            
            return $result;
    }

    public function getUserByHash(string $hash) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE hash_confirm = :hash');
        $stmt->execute(['hash' => $hash]);
        if ($stmt)
            return $stmt->fetch(PDO::FETCH_OBJ);
        return FALSE;
    }

    public function enabledUser(string $id) {
        $result = [
            'message' => 'FAIL ENABLE USER',
            'success' => FALSE
        ];
        $stmt = $this->conn->prepare('UPDATE users SET enabled=1 WHERE id=:id');
        $stmt->execute(['id' => $id]);
        
        if($stmt->rowCount()) {
            $result['success'] = TRUE;
            unset($result['message']);
        } else
            $result['error'] = $stmt->errorInfo();
            
        return $result;
    }

    public function saveUser(array $data) {
        $result = [
            'message' => 'FAIL SAVE USER',
            'success' => FALSE
        ];
        $email = $data['email'];
        if (!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $result['message'] = 'WRONG EMAIL';
            return $result;
        }
        $data['pass'] = $data['pass'] ?? 'testuser';
        $password = password_hash($data['pass'], PASSWORD_DEFAULT);
        $roletype = $data['roletype'] ?? 'user';
        do
            $hash = bin2hex(random_bytes(32));
        while (!$this->getUserByHash($hash) === FALSE);
        
        $sql = 'INSERT INTO users (name, username, email, password, roletype, enabled, hash_confirm) VALUES ';
        $sql .= "(:name, :username, :email, :password, :roletype, :enabled, :hash_confirm)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(
            [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $email,
                'password' => $password,
                'roletype' => $roletype,
                'enabled' => (int) $data['enabled'],
                'hash_confirm' => $hash
            ]
        );
        
        
        if($stmt->rowCount()) {
            $result['id'] = $this->conn->lastInsertId();
            $result['success'] = TRUE;
            unset($result['message']);
        } else
            $result['error'] = $stmt->errorInfo();
            
        return $result;
    }
    
    public function deleteUser(int $id) {
        $result = [
            'message' => 'FAIL DELETE USER',
            'success' => FALSE
        ];
        $stmt = $this->conn->prepare('DELETE FROM users WHERE id = :id');
        
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount()){
            unset($result['message']);
            $result['success'] = TRUE;
        }

        return $result;
    }
    
    public function getUserByEmail(string $email) {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email)
            return FALSE;
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt)
            return $stmt->fetch(PDO::FETCH_OBJ);
        
        return FALSE;
    }

    public function getUserByUsername(string $username) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        if ($stmt)
            return $stmt->fetch(PDO::FETCH_OBJ);
            
        return FALSE;
    }
    
    public function getUser(int $id) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
        
    }

    public function getUsers(string $orderBy = 'id', string $orderDir = 'desc', string $search = '', int $start = 0, int $nRow = 10) {
        $orderBy = in_array($orderBy, ['id', 'name', 'username', 'email', 'roletype', 'enabled']) ? $orderBy : 'id';
        $orderDir = strtoupper($orderDir);
        $orderDir = in_array($orderDir, ['ASC', 'DESC']) ? $orderDir : 'DESC';
        $sql = 'SELECT * FROM users ';
        if (!empty($search)) {
            $sql .= "WHERE id = :searchId OR ";
            $sql .= "name LIKE :search OR ";
            $sql .= "username LIKE :search OR ";
            $sql .= "email LIKE :search ";
        }
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        $sql .= "ORDER BY $orderBy $orderDir  LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $data = empty($search)? [] : [
            'searchId' => $search,
            'search' => '%'.$search.'%'
        ];
        $stmt->execute($data);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
        
    }
    
    public function updateUser(int $id, array $data) {
        $result = [
            'message' => 'FAIL UPDATE USER',
            'success' => FALSE
        ];
        $email = $data['email'];
        if (!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $result['message'] = 'WRONG EMAIL';
            return $result;
        }
        $param = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $email,
            'id' => $id,
            'enabled' => $data['enabled']
        ];
        
        $sql = 'UPDATE users SET name=:name, username=:username, email=:email, enabled=:enabled';
        if (!empty($data['roletype'])) {
            $sql .= ', roletype=:roletype';
            $param['roletype'] = $data['roletype']; 
        }
        if ($data['enabled']) {
            $sql .= ', enabled=:enabled';
            $param['enabled'] = $data['enabled'];
        }
        $sql .= ' WHERE id=:id';
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute($param);
        
        if($stmt->rowCount()) {
            $result['success'] = TRUE;
            unset($result['message']);
        } else
            $result['error'] = $stmt->errorInfo();
            
        return $result;
    }
}

