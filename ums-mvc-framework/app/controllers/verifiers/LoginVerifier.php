<?php
namespace app\controllers\verifiers;

use app\models\PasswordResetRequest;
use app\models\PendingEmail;
use app\models\PendingUser;
use app\models\User;
use \DateTime;
use \PDO;

/**
 * Class verifier to verify a login, logout, signup, ... requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class LoginVerifier extends Verifier {
    protected function __construct(PDO $conn) {
        parent::__construct($conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify a login */
    public function verifyLogin(string $username, string $pass, array $tokens): array {
        /* set fail result */
        $result = [
            WRONG_PASSWORD => FALSE,
            MESSAGE => 'Login failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];
        
        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;
        
        /* get ums configuartions and init user model */
//         $umsConf = $this->appConfig[UMS];
        $user = new User($this->conn);
        /* if is valid email, loggin with it */
        if ($this->isValidEmail($username, USE_REGEX_EMAIL, REGEX_EMAIL)) {
            /* if user not found, then set user not found message and return */
            if (!$usr = $user->getUserByEmail($username, FALSE)) {
                $result[MESSAGE] = 'User not found - Wrong email';
                $result[ERROR] = USER;
                return $result;
            }
        /* else loggin with username, and check if user exists */
        } else if (!$usr = $user->getUserByUsername($username, FALSE)) {
            $result[MESSAGE] = 'User not found - Wrong username';
            $result[ERROR] = USER;
            return $result;
        }
        
        /* check if user is locked or disabled */
        if ($this->isUserLockedOrDisabled($usr)) return $result;
        
        /* verify user password */
        if (!password_verify($pass, $usr->{PASSWORD})) {
            $result[WRONG_PASSWORD] = TRUE;
            $result[USER_ID] = $usr->{USER_ID};
            $result[MESSAGE] = 'Wrong password';
            $result[ERROR] = PASSWORD;
            return $result;
        }
        
        /* unset password of user obj */
        unset($usr->password);
        
        /* set success result and return it */
        $result[MESSAGE] = 'User logged in successfully';
        $result[SUCCESS] = TRUE;
        $result[USER] = $usr;
        return $result;
    }

    /* function to verify a resend signup validation email request */
    public function verifySignupResendEmail(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Resend email failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];
        
        
        /* validate tokens */
        if (!($this->verifyTokens($tokens))) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init pending user model */
        $pendUser = new PendingUser($this->conn);
        
        /* validate user id */
        if (!(is_numeric($id) && ($usr = $pendUser->getPendingUser($id)))) return $result;
        
        /* unset error message */
        unset($result[MESSAGE]);
        
        /* set success result and return it */
        $result[TOKEN] = $usr->{ENABLER_TOKEN};
        $result[EMAIL] = $usr->email;
        $result[SUCCESS] = TRUE;
        return $result;
    }

    /* function to verify logout request */
    public function verifyLogout(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            MESAGE => 'Logout failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init user model and validate user id */
        $user = new User($this->conn);
        if (!$user->getUser($id)) return $result;

        /* unset error message and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = TRUE;

        /* return result */
        return $result;
    }

    /* ############ PASSWORD FUNCTIONS ############ */

    /* function to verify a reset password request */
    public function verifyPassResetReq(string $email, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Password reset request failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate email */
        if (!$this->isValidEmail($email, USE_REGEX_EMAIL, REGEX_EMAIL)) {
            $result[MESSAGE] = 'Invalid email';
            $result[ERROR] = EMAIL;
            return $result;
        }

        /* init user model check if email user exists */
        $user = new User($this->conn);
        if (!$usr = $user->getUserByEmail($email)) {
            $result[MESSAGE] = 'User not found - Wrong email';
            $result[ERROR] = EMAIL;
            return $result;
        }

        /* check if user is locked or disabled */
        if ($this->isUserLockedOrDisabled($usr)) return $result;

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success result and return it */
        $result[USER] = $usr;
        $result[SUCCESS] = TRUE;
        return $result;
    }

    /* function to verify a reset password */
    public function verifyPassReset(string $token, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Password reset failed',
            REMOVE_TOKEN => FALSE,
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* init user model */
        $passResReq = new PasswordResetRequest($this->conn);

        /* validate tokens and user */
        if (!($this->verifyTokens($tokens) && ($user = $passResReq->getUserByResetPasswordToken($token)) && !$this->isUserLockedOrDisabled($user))) return $result; 
        $result[GENERATE_TOKEN] = TRUE;

        /* chech if reset password token is expired */
        if (new DateTime($user->{EXPIRE_DATETIME}) < new DateTime()) {
            /* if expire set fail result and return it */
            $result[MESSAGE] = 'Link expired';
            $result[REMOVE_TOKEN] = TRUE;
            $result[USER_ID] = $user->{USER_ID};
            return $result;
        }

        /* get UMS configurations */
//         $umsConf = $this->appConfig[UMS];

        /* validate password */
        if (!$this->isValidInput($pass, MIN_LENGTH_PASS, MAX_LENGTH_PASS, USE_REGEX, REGEX_PASSWORD)) {
            $result[MESSAGE] = 'Invalid password';
            $result[ERROR] = PASSWORD;
            return $result;
        }

        /* confirm password */
        if ($pass !== $cpass) {
            $result[MESSAGE] = 'Passwords mismatch';
            $result[ERROR] = CONFIRM_PASS;
            return $result;
        }

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success result and return it */
        $result[USER_ID] = $user->{USER_ID};
        $result[SUCCESS] = TRUE;
        return $result;
    }

    /* ############ ENABLER FUNCTIONS ############ */

    /* function to verify enable account request */
    public function verifyEnableAccount(string $token): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Enable account failed',
            REMOVE_TOKEN => FALSE,
            SUCCESS => FALSE
        ];
        
        /* init pending user model */
        $pendUser = new PendingUser($this->conn);
        
        /* validate enabler token and check lock user */
        if (!($user = $pendUser->getUserByAccountEnablerToken($token, FALSE))) return $result;

        $expireTime = new DateTime($user->{REGISTRATION_DATETIME});
        $expireTime->modify(ENABLER_LINK_EXPIRE_TIME);
        if ($expireTime < new DateTime()) {
            $result[MESSAGE] = 'Your enabler link has expire';
            $result[REMOVE_TOKEN] = TRUE;
            return $result;
        }
        /* check if email already exists */
        if ($user->getUserByEmail($user->{EMAIL})) {
            $result[MESSAGE] = 'User already exist with this email';
            $result[REMOVE_TOKEN] = TRUE;
            return $result;
        }

        /* check if username already exists */
        if ($user->getUserByUsername($user->{USERNAME})) {
            $result[MESSAGE] = 'User already exist with this username';
            $result[REMOVE_TOKEN] = TRUE;
            return $result;
        }

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success result and return it */
        $result[USER] = $user;
        $result[SUCCESS] = TRUE;
        return $result;
    }

    /* function to verify a enable new email request */
    public function verifyEnableNewEmail(string $token): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Confirm new email failed',
            SUCCESS => FALSE,
            REMOVE_TOKEN => FALSE
        ];
        
        
        /* validate tokens and user id init pending email model */
        $pendMail = new PendingEmail($this->conn);
        if (!($mail = $pendMail->getUserByEmailEnablerToken($token)) || $this->isUserLockedOrDisabled($mail)) return $result;

        /* check if token is expire */
        if ($mail->{EXPIRE_DATETIME} < new DateTime()) {
            $result[MESSAGE] = 'Your enabler link has expire';
            $result[REMOVE_TOKEN] = TRUE;
            return $result;
        }

        /* check if new email confirmed already exists */
        $userModel = new User($this->conn);
        if ($userModel->getUserByEmail($mail->{NEW_EMAIL})) {
            $result[MESSAGE] = 'User already exist with this email';
            $result[REMOVE_TOKEN] = TRUE;
            return $result;
        }
        
        /* unset error message */
        unset($result[MESSAGE]);
        
        /* set success result and return it */
        $result[USER] = $mail;
        $result[SUCCESS] = TRUE;
        return $result;
    }
}
