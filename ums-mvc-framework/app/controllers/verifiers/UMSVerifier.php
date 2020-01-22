<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use app\models\Role;
use app\models\DeletedUser;
use app\models\Session;

class UMSVerifier extends Verifier {

    protected function __construct(PDO $conn, array $langMessage) {
        parent::__construct($conn, $langMessage);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* functio to verify a new user request */
    public function verifyNewUser(string $name, string $email, string $username, string $pass, string $cpass, string $role, array $tokens): array {
        /* verify signup */
        /* validate name, email, username, password and token */
        $result = $this->verifySignup($name, $email, $username, $pass, $cpass, $tokens);

        /* if success */
        if ($result[SUCCESS]) {
            $result[SUCCESS] = FALSE;
            $result[MESSAGE] = $this->langMessage[SAVE_USER][FAIL];
            /* init role model and validate role type */
            $roleModel = new Role($this->conn);
            if (in_array($role, $roleModel->getRoleIdList())) {
                $result[SUCCESS] = TRUE;
                unset($result[MESSAGE]);
            } else {
                $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_ROLETYPE];
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
            $result[MESSAGE] = $this->langMessage[USER_UPDATE][FAIL];
            if (in_array($role, $roleModel->getRoleIdList())) {
                $result[SUCCESS] = TRUE;
                unset($result[MESSAGE]);
            } else {
                $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_ROLETYPE];
                $result[ERROR] = ROLE_ID_FRGN;
            }
        }

        /* return result */
        return $result;
    }

    /* function to verify reset user locks request */
    public function verifyLockCounterReset(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => $this->langMessage[LOCK_USER_RESET][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens nad user id */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init user model and validate user */
        $user = new User($this->conn);
        if (!$user->getUser($id)) return $result;

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success and return result */
        $result[SUCCESS] = TRUE;
        return $result;
    }

    /* function to verify restore delete user request */
    public function verifyRestoreUser(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => $this->langMessage[RESTORE_USER][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];
        
        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;
        
        /* init deleted user model and validate user */
        $delUserModel = new DeletedUser($this->conn);
        if (!($user = $delUserModel->getDeleteUserByUserId($id))) return $result;

        /* init user model and check if username or email already exists */
        $userModel = new User($this->conn);
        if ($userModel->getUserByUsername($user->{USERNAME})) {
            $result[MESSAGE] = $this->langMessage[GENERIC][USERNAME_ALREADY_EXISTS];
            return $result;
        }
        if ($userModel->getUserByEmail($user->{EMAIL})) {
            $result[MESSAGE] = $this->langMessage[GENERIC][EMAIL_ALREADY_EXISTS];
            return $result;
        }

        /* unset error message */
        unset($result[MESSAGE]);
        /* set success and return result */
        $result[SUCCESS] = TRUE;
        $result[USER] = $user;
        return $result;
    }

    /* function to verify a update user request */
    public function verifyRemoveSession(string $sessionId, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => $this->langMessage[REMOVE_SESSION][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init session model validate sessionm id */
        $sessionModel = new Session($this->conn);
        if (!$sessionModel->getSessionAndUser($sessionId)) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_ID];
            return $result;
        }
        /* set successs result and return it */
        $result[SUCCESS] = TRUE;
        unset($result[MESSAGE]);
        return $result;
    }

    /* function to verify update password request */
    public function verifyUpdatePass(int $id, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => $this->langMessage[CHANGE_PASS][FAIL],
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
            $result[MESSAGE] = $this->langMessage[GENERIC][PASS_MISMATCH];
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
