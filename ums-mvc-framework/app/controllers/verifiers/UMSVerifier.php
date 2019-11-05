<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;

class UMSVerifier extends Verifier {
    protected $userRoles = [];

    protected function __construct(array $appConfig, PDO $conn) {
        parent::__construct($appConfig, $conn);
        $this->userRoles = getList('userRoles');
    }

    public function setUserRoles(array $userRoles) {
        $this->userRoles = $userRoles;
    }

    public function verifyNewUser(string $name, string $email, string $username, string $pass, string $cpass, string $role, array $tokens): array {
        $result = $this->verifySignup($name, $email, $username, $pass, $cpass, $tokens);
        if ($result['success'] && !in_array($role, $this->userRoles)) {
            return [
                'message' => 'Invalid roletype',
                'error' => 'role',
                'success' => FALSE
            ];
        }

        return $result;
    }

    public function verifyUpdateUser(string $id, string $name, string $email, string $username, string $role, array $tokens): array {
        $result = $this->verifyUpdate($id, $name, $email, $username, $tokens);
        if ($result['success'] && !in_array($role, $this->userRoles)) {
            return [
                'message' => 'Invalid roletype',
                'error' => 'role',
                'success' => FALSE
            ];
        }

        return $result;
    }

    public function verifyResetLockUser(int $id, array $tokens): array {
        $result = [
            'message' => 'Reset lock user failed',
            'success' => FALSE
        ];
        
        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;
        
        unset($result['message']);
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyResetWrongPasswords(int $id, array $tokens): array {
        $result = [
            'message' => 'Reset wrong passwords failed',
            'success' => FALSE
        ];

        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        unset($result['message']);
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyUpdatePass(int $id, string $pass, string $cpass, array $tokens): array {
        $result = [
            'message' => 'Update user password failed',
            'success' => FALSE
        ];

        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        if ($pass !== $cpass) {
            $result['message'] = 'Passwords mismatch';
            $result['error'] = 'cpass';
            return $result;
        }

        unset($result['message']);
        $result['success'] = TRUE;
        return $result;
    }
}
