<?php
namespace app\controllers\verifiers;

use app\models\PendingEmail;
use app\models\User;
use \DateTime;
use \PDO;

/**
 * Class verifier, to validate a requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class Verifier {
    protected $conn;
    static protected $instance;

    /* singleton */
    static public function getInstance(PDO $conn = NULL): Verifier {
        if (!isset(static::$instance)) static::$instance = new static($conn);
        return static::$instance;
    }

    protected function __construct(PDO $conn = NULL) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify wrong passwords and locks of user */
    public function verifyWrongPassword(int $nWrongPass, int $nLock): array {
        /* set result */
        $result = [
            LOCK => FALSE,
            DISABLE => FALSE
        ];

        /* check if user has reached max wrong passwords */
        if ($nWrongPass >= MAX_WRONG_PASSWORDS) {
            $result[LOCK] = TRUE;
            ++$nLock;
        }

        /* check if user has reached max locks */
        if ($nLock >= MAX_LOCKS) {
            $result[DISABLE] = TRUE;
            $result[LOCK] = FALSE;
        }

        /* return result */
        return $result;
    }

    /* function to verify a signup request */
    public function verifySignup(string $name, string $email, string $username, string $pass, string $cpass, array $tokens): array {
        /* set fail function */
        $result = [
            MESSAGE => 'Signup failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

//         /* get app configurations */
//         $umsConf = $this->appConfig[UMS];

        /* validate name */
        if (!$this->isValidInput($name, MIN_LENGTH_NAME, MAX_LENGTH_NAME, USE_REGEX_NAME, REGEX_NAME)) {
            $result[MESSAGE] = 'Invalid name';
            $result[ERROR] = NAME;
            return $result;
        }

        /* validate username */
        if (!$this->isValidInput($username, MIN_LENGTH_USERNAME, MAX_LENGTH_USERNAME, USE_REGEX_USERNAME, REGEX_USERNAME)) {
            $result[MESSAGE] = 'Invalid username';
            $result[ERROR] = USERNAME;
            return $result;
        }

        /* init user and pending user model */
        $user = new User($this->conn);

        /* check if username already exists or is on pending */
        if ($user->getUserByUsername($username)) {
            $result[MESSAGE] = 'User already exist with this username';
            $result[ERROR] = USERNAME;
            return $result;
        }


        /* validate email */
        if (!$this->isValidEmail($email, MIN_LENGTH_EMAIL, MAX_LENGTH_EMAIL, USE_REGEX_EMAIL, REGEX_EMAIL)) {
            $result[MESSAGE] = 'Invalid email';
            $result[ERROR] = EMAIL;
            return $result;
        }

        /* check if email already exists */
        if ($user->getUserByEmail($email)) {
            $result[MESSAGE] = 'User already exist with this email';
            $result[ERROR] = EMAIL;
            return $result;
        }

        /* validate password */
        if (!$this->isValidInput($pass, MIN_LENGTH_PASS, MAX_LENGTH_PASS, USE_REGEX_PASSWORD, REGEX_PASSWORD)) {
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

        /* unset errorm messagge and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = true;

        /* return result */
        return $result;
    }

    /* function to verify a user update request */
    public function verifyUpdate(int $id, string $name, string $email, string $username, array $tokens): array {
        /* set fail result */
        $result = [
            CHANGED_EMAIL => FALSE,
            MESSAGE => 'User update failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init user model and validate user id */
        $userModel = new User($this->conn);
        if (!($user = $userModel->getUser($id))) return $result;

        /* get configuration of app */
//         $umsConf = $this->appConfig[UMS];

        /* validate name */
        if (!$this->isValidInput($name, MIN_LENGTH_NAME, MAX_LENGTH_NAME, USE_REGEX_NAME, REGEX_NAME)) {
            $result[MESSAGE] = 'Invalid name';
            $result[ERROR] = NAME;
            return $result;
        }

        /* validate username */
        if (!$this->isValidInput($username, MIN_LENGTH_USERNAME, MAX_LENGTH_USERNAME, USE_REGEX_USERNAME, REGEX_USERNAME)) {
            $result[MESSAGE] = 'Invalid username';
            $result[ERROR] = USERNAME;
            return $result;
        }

        /* if username is chsnaged, then check if it already exists */
        if ($user->{USERNAME} !== $username && $userModel->getUserByUsername($username)) {
            $result[MESSAGE] = 'User already exist with this username';
            $result[ERROR] = USERNAME;
            return $result;
        }

        /* validate email */
        if (!$this->isValidEmail($email, MIN_LENGTH_EMAIL, MAX_LENGTH_EMAIL, USE_REGEX_EMAIL, REGEX_EMAIL)) {
            $result[MESSAGE] = 'Invalid email';
            $result[ERROR] = EMAIL;
            return $result;
        }

        /* if email is changed, then check if it already exists */
        if ($user->{EMAIL} !== $email) {
            if ($userModel->getUserByEmail($email)) {
                $result[MESSAGE] = 'User already exist with this email';
                $result[ERROR] = EMAIL;
                return $result;
            }
            $result[CHANGED_EMAIL] = TRUE;
        }

        /* unset error message and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = TRUE;

        /* return result */
        return $result;
    }

    /* function to verify a delete user request */
    public  function verifyDelete(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Delete user failed',
            SUCCESS =>  FALSE,
            GENERATE_TOKEN => FALSE
        ];


        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init user model and validate user id */
        $userModel = new User($this->conn);
        if (!($user = $userModel->getUser($id))) return $result;

        /* unset error message and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = TRUE;
        $result[USER] = $user;

        /* return result */
        return $result;
    }

    /* function to verifu delete new email requests */
    public function verifyDeleteNewEmail(int $id, array $tokens): array {
        /* set fail results */
        $result = [
            MESSAGE => 'Delete email failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init pending mail model */
        $pendMail = new PendingEmail($this->conn);

        /* get pending emails by id and check if they have a new email */
        if (!$pendMail->getPendingEmailByUserId($id)) return $result;

        /* unset error message and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = TRUE;

        /* return result */
        return $result;
    }

    /* function to validate the tokens */
    public  function verifyTokens(array $tokens): bool {
        /* check if tokens are not set or are empty, then compare tokens */
        return isset($tokens[0]) && isset($tokens[1]) && !empty($tokens[0]) && !empty($tokens[1]) && $tokens[0] === $tokens[1];
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to validate a input */
    protected function isValidInput(string $input, int $minLength, int $maxLength, bool $useRegex, string $regex = ''): bool {
        return strlen($input) >= $minLength && strlen($input) <= $maxLength && (!$useRegex || preg_match($regex, $input));
    }

    /* function to laidate a email */
    protected function isValidEmail(string $email, int $minLength, int $maxLength, bool $useRegex, string $regex=''): bool {
        return strlen($email) >= $minLength && strlen($email) <= $maxLength && ($email = filter_var($email, FILTER_VALIDATE_EMAIL)) && (!$useRegex || preg_match($regex, $email));
    }

//     /* fuinction to validate a password */
//     protected function isValidPassword(string $password, int $minLength, int $maxLength, bool $useRegex, string $regex = ''): bool {
//         return strlen($password) > $minLength && strlen($password) < $maxLength && (!$useRegex || preg_match($regex, $password));
//     }

    /* fuinction to validate a domain */
    protected function isValidDomain(string $domain): bool {
        return (bool) filter_var($domain, FILTER_VALIDATE_DOMAIN);
    }

    /* function to validate a number */
    protected function isValidNumber($number, int $minValue, int $maxValue): bool {
        return is_numeric($number) && verifyNumVarRange($number, $minValue, $maxValue);
    }

    protected function isUserLockedOrDisabled($user) {
        return $this->isUserTempLocked($user) || !$this->isUserEnable($user);
    }

    /* function to check if is user enabled */
    protected function isUserEnable($user): bool {
        return (bool) $user->{ENABLED};
    }

    /* function to check if user is temporarily locked */
    protected function isUserTempLocked($user): bool {
        return isset($user->{EXPIRE_LOCK}) && new DateTime($user->{EXPIRE_LOCK}) > new DateTime();
    }

//     /* funciton to check if username already exists */
//     protected function usernameAlreadyExists(string $username): bool {
//         /* init user and pending user model */
//         $user = new User($this->conn);
//         $pendUser = new PendingUser($this->conn);
//         /* calc min valid datetime */
//         $minDatetime = getExpireDatetime('-'.$this->appConfig[UMS][ENABLER_LINK_EXPIRE_TIME]);
//         /* check if username is on users or pending tables */
//         return $user->getUserByUsername($username) || $pendUser->isPendingUsername($username, $minDatetime);
//     }

//     /* funciton to check if email already exists */
//     protected function emailAlreadyExists(string $email): bool {
//         /* init user and pending user model */
//         $user = new User($this->conn);
//         $pendUser = new PendingUser($this->conn);
//         /* calc min valid datetime */
//         $minDatetime = getExpireDatetime('-'.$this->appConfig[UMS][ENABLER_LINK_EXPIRE_TIME]);
//         return $user->getUserByEmail($email) || $pendUser->isPendingEmail($email, $minDatetime);
//     }
}
