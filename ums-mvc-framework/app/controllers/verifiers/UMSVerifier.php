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

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to set list of user roles */
    public function setUserRoles(array $userRoles) {
        $this->userRoles = $userRoles;
    }

    /* functio to verify a new user request */
    public function verifyNewUser(string $name, string $email, string $username, string $pass, string $cpass, string $role, array $tokens): array {
        /* verify signup */
        /* validate name, email, username, password and token */
        $result = $this->verifySignup($name, $email, $username, $pass, $cpass, $tokens);

        /* validate role type */
        if ($result['success'] && !in_array($role, $this->userRoles)) {
            return [
                'message' => 'Invalid roletype',
                'error' => 'role',
                'success' => FALSE
            ];
        }

        /* return result */
        return $result;
    }

    /* function to verify a update user request */
    public function verifyUpdateUser(string $id, string $name, string $email, string $username, string $role, array $tokens): array {
        /* verify update */
        /* validate id, name, email, username and token */
        $result = $this->verifyUpdate($id, $name, $email, $username, $tokens);

        /* validate role type */
        if ($result['success'] && !in_array($role, $this->userRoles)) {
            return [
                'message' => 'Invalid roletype',
                'error' => 'role',
                'success' => FALSE
            ];
        }

        /* return result */
        return $result;
    }

    /* function to verify reset user locks request */
    public function verifyResetLockUser(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Reset lock user failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens nad user id */
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        /* unset error message */
        unset($result['message']);

        /* set success and return result */
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify reset wrong password request */
    public function verifyResetWrongPasswords(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Reset wrong passwords failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user id */
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        /* unset error message */
        unset($result['message']);

        /* set success and return result */
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify update password request */
    public function verifyUpdatePass(int $id, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Update user password failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user id */
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        /* confirm password */
        if ($pass !== $cpass) {
            $result['message'] = 'Passwords mismatch';
            $result['error'] = 'cpass';
            return $result;
        }

        /* unset error message */
        unset($result['message']);

        /* set success and return result */
        $result['success'] = TRUE;
        return $result;
    }
}
