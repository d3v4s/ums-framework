<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use \DateTime;
use app\models\PendingEmail;

/**
 * Class verifier, to validate a requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class Verifier {
    protected $appConfig;
    protected $conn;
    static protected $instance;

    /* singleton */
    static public function getInstance(array $appConfig, PDO $conn = NULL): Verifier {
        if (!isset(static::$instance)) static::$instance = new static($appConfig, $conn);
        return static::$instance;
    }

    protected function __construct(array $appConfig, PDO $conn = NULL) {
        $this->appConfig = $appConfig ?? getConfig();
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
        if ($nWrongPass >= $this->appConfig[SECURITY][MAX_WRONG_PASSWORDS]) {
            $result[LOCK] = TRUE;
            ++$nLock;
        }

        /* check if user has reached max locks */
        if ($nLock >= $this->appConfig[SECURITY][MAX_LOCKS]) {
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
            SUCCESS => false,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* get app configurations */
        $umsConf = $this->appConfig[UMS];

        /* validate name */
        if (!$this->isValidInput($name, $umsConf[MIN_LENGTH_NAME], $umsConf[MAX_LENGTH_NAME], $umsConf[USE_REGEX], $umsConf[REGEX_NAME])) {
            $result[MESSAGE] = 'Invalid name';
            $result[ERROR] = NAME;
            return $result;
        }

        /* validate username */
        if (!$this->isValidInput($username, $umsConf[MIN_LENGTH_USERNAME], $umsConf[MAX_LENGTH_USERNAME], $umsConf[USE_REGEX], $umsConf[REGEX_USERNAME])) {
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
        if (!$this->isValidEmail($email, $umsConf[USE_REGEX_EMAIL], $umsConf[REGEX_EMAIL])) {
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
        if (!$this->isValidPassword($pass, $umsConf[MIN_LENGTH_PASS], $umsConf[CHECK_MAX_LENGTH_PASS], $umsConf[MAX_LENGTH_PASS], $umsConf[REQUIRE_HARD_PASS], $umsConf[USE_REGEX], $umsConf[REGEX_PASSWORD])) {
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
        $user = new User($this->conn);
        if (!($usr = $user->getUser($id))) return $result;

        /* get configuration of app */
        $umsConf = $this->appConfig[UMS];

        /* validate name */
        if (!$this->isValidInput($name, $umsConf[MIN_LENGTH_NAME], $umsConf[MAX_LENGTH_USERNAME], $umsConf[USE_REGEX], $umsConf[REGEX_NAME])) {
            $result[MESSAGE] = 'Invalid name';
            $result[ERROR] = NAME;
            return $result;
        }

        /* validate username */
        if (!$this->isValidInput($username, $umsConf[MIN_LENGTH_USERNAME], $umsConf[MAX_LENGTH_USERNAME], $umsConf[USE_REGEX], $umsConf[REGEX_USERNAME])) {
            $result[MESSAGE] = 'Invalid username';
            $result[ERROR] = USERNAME;
            return $result;
        }

        /* validate email */
        if (!$this->isValidEmail($email, $umsConf[USE_REGEX_EMAIL], $umsConf[REGEX_EMAIL])) {
            $result[MESSAGE] = 'Invalid email';
            $result[ERROR] = EMAIL;
            return $result;
        }

        /* if username is chsnaged, then check if it already exists */
        if ($usr->{USERNAME} !== $username && $user->getUserByUsername($username)) {
            $result[MESSAGE] = 'User already exist with this username';
            $result[ERROR] = USERNAME;
            return $result;
//             if ($this->isValidUser($usrDel, $umsConf['requireConfirmEmail'])) {
//             }
//             $result['deleteUser'] = $usrDel->id;
        }

        /* if email is changed, then check if it already exists */
        if ($usr->email !== $email) {
            if ($user->getUserByEmail($email)) {
                $result[MESSAGE] = 'User already exist with this email';
                $result[ERROR] = EMAIL;
                return $result;
//                 if ($this->isValidUser($usrDel, $umsConf['requireConfirmEmail'])) {
//                 }
//                 $result['deleteUser'] = $usrDel->id;
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
        $user = new User($this->conn);
        if (!$user->getUser($id)) return $result;

        /* unset error message and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = true;

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

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to validate the tokens */
    protected function verifyTokens(array $tokens): bool {
        /* if tokens are not set or are empty, then return FALSE */ 
        if (!(isset($tokens[0]) && isset($tokens[1])) || empty($tokens[0]) || empty($tokens[1])) return FALSE;
        /* compare tokens */
        return ($tokens[0] === $tokens[1]);
    }

    /* function to validate a input */
    protected function isValidInput(string $input, int $minLength, int $maxLength, bool $useRegex, string $regex = ''): bool {
        if (strlen($input) < $minLength || strlen($input) > $maxLength || ($useRegex && !preg_match($regex, $input))) return FALSE;
        return TRUE;
    }

    /* function to laidate a email */
    protected function isValidEmail(string $email, bool $useRegex, string $regex = ''): bool {
        if ($useRegex) if (!preg_match($regex, $email)) return FALSE;
        else if (!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) return FALSE;

        return TRUE;
    }

    /* fuinction to validate a password */
    protected function isValidPassword(string $password, int $minLength, bool $checkMaxLength, int $maxLength, bool $requireHardPassword, bool $useRegex, string $regex = ''): bool {
        if (strlen($password) < $minLength || ($checkMaxLength && strlen($password) > $maxLength)) return FALSE;
        if ($requireHardPassword) {
//             $rgx = '/^((?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@!%*?&._\-]))[A-Za-z\d$@!%*?&._\-]{'.$minLength.',}$/';
            $rgx = '/^((?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]))[A-Za-z\d\W_]{8,}$/';
            if (!preg_match($rgx, $password)) return FALSE;
        }
        if ($useRegex && !preg_match($regex, $password)) return FALSE;

        return TRUE;
    }

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
