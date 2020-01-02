<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use \DateTime;

class LoginVerifier extends Verifier {
    protected function __construct(array $appConfig, PDO $conn) {
        parent::__construct($appConfig, $conn);
    }

    public function verifyLogout(int $id, array $tokens): array {
        $result = [
            'message' => 'Logout failed',
            'success' => FALSE
        ];
        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        unset($result['message']);
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyEnableAccount(string $token): array {
        $result = [
            'message' => 'Enable account failed',
            'success' => FALSE
        ];
        
        $user = new User($this->conn, $this->appConfig);
        if (!($usr = $user->getUserByTokenEnabler($token)) || $this->isUserTempLocked($usr)) return $result;
        
        unset($result['message']);
        $result['user'] = $usr;
        $result['success'] = TRUE;
        return $result;
    }

    public function verifySignupResendEmail(int $id, array $tokens): array {
        $result = [
            'message' => 'Resend email failed',
            'success' => FALSE
        ];

        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && is_numeric($id) && ($usr = $user->getUser($id)))) return $result;

        unset($result['message']);
        $result['token'] = $usr->token_account_enabler;
        $result['email'] = $usr->email;
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyValidateNewEmail(string $token): array {
        $result = [
            'message' => 'Confirm new email failed',
            'success' => FALSE
        ];

        $user = new User($this->conn, $this->appConfig);
        if (!($usr = $user->getUserByTokenConfirmEmail($token)) || $this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result;

        if ($usrDel = $user->getUserByEmail($usr->new_email)) {
            if ($this->isValidUser($usrDel, $this->appConfig['app']['requireConfirmEmail'])) {
                $result['message'] = 'User already exist with your new email';
                return $result;
            }
            $result['deleteUser'] = $usrDel->id;
        }
        
        unset($result['message']);
        $result['userId'] = $usr->id;
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyResetPassReq(string $email, array $tokens): array {
        $result = [
            'message' => 'Password reset request failed',
            'success' => FALSE
        ];

        if (!$this->verifyTokens($tokens)) return $result;

        $user = new User($this->conn, $this->appConfig);
        if (!$this->isValidEmail($email, $this->appConfig['app']['useRegexEmail'], $this->appConfig['app']['regexEmail'])) {
            $result['message'] = 'Invalid email';
            $result['error'] = 'email';
            return $result;
        }
        if (!$usr = $user->getUserByEmail($email)) {
            $result['message'] = 'User not found - Wrong email';
            $result['error'] = 'email';
            return $result;
        }
        if ($this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result;
        
        unset($result['message']);
        $result['user'] = $usr;
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyResetPass(string $token, string $pass, string $cpass, array $tokens): array {
        $result = [
            'message' => 'Password reset failed',
            'deleteToken' => FALSE,
            'success' => FALSE
        ];

        $user = new User($this->conn, $this->appConfig);
        if (!$this->verifyTokens($tokens) || !($usr = $user->getUserByTokenResetPassword($token)) || $this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result; 

        if (new DateTime($usr->datetime_req_reset_pass_expire) < new DateTime(date('Y-m-d H:i:s'))) {
            $result['message'] = 'Link expired';
            $result['deleteToken'] = TRUE;
            $result['userId'] = $usr->id;
            return $result;
        }
        
        $confApp = $this->appConfig['app'];
        if (!$this->isValidPassword($pass, $confApp['minLengthPassword'], $confApp['checkMaxLengthPassword'], $confApp['maxLengthPassword'], $confApp['requireHardPassword'], $confApp['useRegex'], $confApp['regexPassword'])) {
            $result['message'] = 'Invalid password';
            $result['error'] = 'pass';
            return $result;
        }
        if ($pass !== $cpass) {
            $result['message'] = 'Passwords mismatch';
            $result['error'] = 'cpass';
            return $result;
        }

        unset($result['message']);
        $result['user'] = $usr;
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyLogin(string $username, string $pass, array $tokens): array {
        $result = [
            'wrongPass' => FALSE,
            'message' => 'Login failed',
            'success' => FALSE
        ];
        
        if (!$this->verifyTokens($tokens)) return $result;
        
        $confApp = $this->appConfig['app'];
        $user = new User($this->conn, $this->appConfig);
        if ($this->isValidEmail($username, $confApp['useRegexEmail'], $confApp['regexEmail'])) {
            if (!$usr = $user->getUserByEmail($username, FALSE)) {
                if ($user->getUserByNewEmail($username)) {
                    $result['message'] = 'Confirm your email';
                    $result['error'] = 'user';
                    return $result;
                }
                $result['message'] = 'User not found - Wrong email';
                $result['error'] = 'user';
                return $result;
            }
        } else if (!$usr = $user->getUserByUsername($username, FALSE)) {
            $result['message'] = 'User not found - Wrong username';
            $result['error'] = 'user';
            return $result;
        }

        if ($this->isUserTempLocked($usr) || !$this->isUserEnable($usr)) return $result;

        if (!password_verify($pass, $usr->password)) {
            $result['wrongPass'] = TRUE;
            $result['userId'] = $usr->id;
            $result['message'] = 'Wrong password';
            $result['error'] = 'pass';
            return $result;
        }
        
        unset($usr->password);
        $result['message'] = 'User logged in successfully';
        $result['success'] = TRUE;
        $result['user'] = $usr;
        return $result;
    }
}
