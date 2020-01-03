<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use \DateTime;

/**
 * Class verifier to verify a login, logout, signup, ... requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class LoginVerifier extends Verifier {
    protected function __construct(array $appConfig, PDO $conn) {
        parent::__construct($appConfig, $conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify logout request */
    public function verifyLogout(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Logout failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user id */
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        /* unset error message and set success */
        unset($result['message']);
        $result['success'] = TRUE;

        /* return result */
        return $result;
    }

    /* function to verify enable account request */
    public function verifyEnableAccount(string $token): array {
        /* set fail result */
        $result = [
            'message' => 'Enable account failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user id */
        if (!($usr = $user->getUserByTokenEnabler($token)) || $this->isUserTempLocked($usr)) return $result;

        /* unset error message */ 
        unset($result['message']);

        /* set success result and return it */
        $result['user'] = $usr;
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify a resend signup validation email request */
    public function verifySignupResendEmail(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Resend email failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user id */
        if (!($this->verifyTokens($tokens) && is_numeric($id) && ($usr = $user->getUser($id)))) return $result;

        /* unset error message */
        unset($result['message']);

        /* set success result and return it */
        $result['token'] = $usr->token_account_enabler;
        $result['email'] = $usr->email;
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify a confirm new email request */
    public function verifyValidateNewEmail(string $token): array {
        /* set fail result */
        $result = [
            'message' => 'Confirm new email failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user id */
        if (!($usr = $user->getUserByTokenConfirmEmail($token)) || $this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result;

        /* check if new email confirmed already exists */
        if ($usrDel = $user->getUserByEmail($usr->new_email)) {
            /* check if is valid user */
            if ($this->isValidUser($usrDel, $this->appConfig['app']['requireConfirmEmail'])) {
                $result['message'] = 'User already exist with your new email';
                return $result;
            }
            /* if is not valid user set to delete it */
            $result['deleteUser'] = $usrDel->id;
        }

        /* unset error message */
        unset($result['message']);

        /* set success result and return it */
        $result['userId'] = $usr->id;
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify a reset password request */
    public function verifyResetPassReq(string $email, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Password reset request failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate email */
        if (!$this->isValidEmail($email, $this->appConfig['app']['useRegexEmail'], $this->appConfig['app']['regexEmail'])) {
            $result['message'] = 'Invalid email';
            $result['error'] = 'email';
            return $result;
        }

        /* check if email user exists */
        if (!$usr = $user->getUserByEmail($email)) {
            $result['message'] = 'User not found - Wrong email';
            $result['error'] = 'email';
            return $result;
        }

        /* check if user is locked or disabled */
        if ($this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result;

        /* unset error message */
        unset($result['message']);

        /* set success result and return it */
        $result['user'] = $usr;
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify a reset password */
    public function verifyResetPass(string $token, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Password reset failed',
            'deleteToken' => FALSE,
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user */
        if (!$this->verifyTokens($tokens) || !($usr = $user->getUserByTokenResetPassword($token)) || $this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result; 

        /* chech if reset password token is expired */
        if (new DateTime($usr->datetime_req_reset_pass_expire) < new DateTime(date('Y-m-d H:i:s'))) {
            $result['message'] = 'Link expired';
            $result['deleteToken'] = TRUE;
            $result['userId'] = $usr->id;
            return $result;
        }

        /* get app configurations */
        $confApp = $this->appConfig['app'];

        /* validate password */
        if (!$this->isValidPassword($pass, $confApp['minLengthPassword'], $confApp['checkMaxLengthPassword'], $confApp['maxLengthPassword'], $confApp['requireHardPassword'], $confApp['useRegex'], $confApp['regexPassword'])) {
            $result['message'] = 'Invalid password';
            $result['error'] = 'pass';
            return $result;
        }

        /* confirm password */
        if ($pass !== $cpass) {
            $result['message'] = 'Passwords mismatch';
            $result['error'] = 'cpass';
            return $result;
        }

        /* unset error message */
        unset($result['message']);

        /* set success result and return it */
        $result['user'] = $usr;
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify a login */
    public function verifyLogin(string $username, string $pass, array $tokens): array {
        /* set fail result */
        $result = [
            'wrongPass' => FALSE,
            'message' => 'Login failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        
        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* get app configuartions */
        $confApp = $this->appConfig['app'];

        /* if is valid email, loggin with it */
        if ($this->isValidEmail($username, $confApp['useRegexEmail'], $confApp['regexEmail'])) {
            /* if user not found */
            if (!$usr = $user->getUserByEmail($username, FALSE)) {
                /* if is pending, send confirm email message */
                if ($user->getUserByNewEmail($username)) {
                    $result['message'] = 'Confirm your email';
                    $result['error'] = 'user';
                    return $result;
                }
                /* set user not found message and return */
                $result['message'] = 'User not found - Wrong email';
                $result['error'] = 'user';
                return $result;
            }
        /* else loggin with username, and check if user exists */
        } else if (!$usr = $user->getUserByUsername($username, FALSE)) {
            $result['message'] = 'User not found - Wrong username';
            $result['error'] = 'user';
            return $result;
        }

        /* check if user is locked or disabled */
        if ($this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result;

        /* verify user password */
        if (!password_verify($pass, $usr->password)) {
            $result['wrongPass'] = TRUE;
            $result['userId'] = $usr->id;
            $result['message'] = 'Wrong password';
            $result['error'] = 'pass';
            return $result;
        }

        /* unset password of user obj */
        unset($usr->password);

        /* set success result and return it */
        $result['message'] = 'User logged in successfully';
        $result['success'] = TRUE;
        $result['user'] = $usr;
        return $result;
    }
}
