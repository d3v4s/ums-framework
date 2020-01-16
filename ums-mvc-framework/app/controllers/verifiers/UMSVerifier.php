<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use app\models\Role;

class UMSVerifier extends Verifier {
//     protected $userRoles = [];

    protected function __construct(PDO $conn) {
        parent::__construct($conn);
//         $this->userRoles = getList('userRoles');
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

//     /* function to set list of user roles */
//     public function setUserRoles(array $userRoles) {
//         $this->userRoles = $userRoles;
//     }

    /* functio to verify a new user request */
    public function verifyNewUser(string $name, string $email, string $username, string $pass, string $cpass, string $role, array $tokens): array {
        /* verify signup */
        /* validate name, email, username, password and token */
        $result = $this->verifySignup($name, $email, $username, $pass, $cpass, $tokens);

        /* if success */
        if ($result[SUCCESS]) {
            $result[SUCCESS] = FALSE;
            $result[MESSAGE] = 'Add new user failed';
            /* init role model and validate role type */
            $roleModel = new Role($this->conn);
            if (in_array($role, $roleModel->getRoleIdList())) {
                $result[SUCCESS] = TRUE;
                unset($result[MESSAGE]);
            } else {
                $result[MESSAGE] = 'Invalid roletype';
                $result[ERROR] = ROLE_ID_FRGN;
            }
        }

        /* return result */
        return $result;
    }

    /* function to verify a update user request */
    public function verifyUpdateUser(string $id, string $name, string $email, string $username, string $role, array $tokens): array {
        /* validate id, name, email, username and token */
        $result = $this->verifyUpdate($id, $name, $email, $username, $tokens);

        $roleModel = new Role($this->conn);
        
        /* validate role type */
        if ($result[SUCCESS]) {
            $result[SUCCESS] = FALSE;
            $result[MESSAGE] = 'Update user failed';
            if (in_array($role, $roleModel->getRoleIdList())) {
                $result[SUCCESS] = TRUE;
                unset($result[MESSAGE]);
            } else {
                $result[MESSAGE] = 'Invalid roletype';
                $result[ERROR] = ROLE_ID_FRGN;
            }
        }

        /* return result */
        return $result;
    }

    /* function to verify reset user locks request */
    public function verifUnlockUser(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Unlock user failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens nad user id */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init user model and validate user */
        $user = new User($this->conn);
        if ($user->getUser($id)) return $result;

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success and return result */
        $result[SUCCESS] = TRUE;
        return $result;
    }

//     /* function to verify reset user locks request */
//     public function verifyResetLockUser(int $id, array $tokens): array {
//         /* set fail result */
//         $result = [
//             'message' => 'Reset lock user failed',
//             'success' => FALSE
//         ];

//         /* init user model */
//         $user = new User($this->conn, $this->appConfig);

//         /* validate tokens nad user id */
//         if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

//         /* unset error message */
//         unset($result['message']);

//         /* set success and return result */
//         $result['success'] = TRUE;
//         return $result;
//     }

//     /* function to verify reset wrong password request */
//     public function verifyResetWrongPasswords(int $id, array $tokens): array {
//         /* set fail result */
//         $result = [
//             'message' => 'Reset wrong passwords failed',
//             'success' => FALSE
//         ];

//         /* init user model */
//         $user = new User($this->conn, $this->appConfig);

//         /* validate tokens and user id */
//         if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

//         /* unset error message */
//         unset($result['message']);

//         /* set success and return result */
//         $result['success'] = TRUE;
//         return $result;
//     }

    /* function to verify update password request */
    public function verifyUpdatePass(int $id, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Password update failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;
        
        /* init user model and validate user id*/
        $user = new User($this->conn);
        if (!$user->getUser($id)) return $result;

        /* confirm password */
        if ($pass !== $cpass) {
            $result[MESSAGE] = 'Passwords mismatch';
            $result[ERROR] = CONFIRM_PASS;
            return $result;
        }

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success and return result */
        $result[SUCCESS] = TRUE;
        return $result;
    }
}
