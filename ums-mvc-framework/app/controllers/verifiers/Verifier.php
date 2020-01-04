<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use \DateTime;

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
            SUCCESS => false
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* get app configurations */
        $confUMS = $this->appConfig[UMS];

        /* validate name */
        if (!$this->isValidInput($name, $confUMS[MIN_LENGHT_NAME], $confUMS[MAX_LENGTH_NAME], $confUMS[USE_REGEX], $confUMS[REGEX_NAME])) {
            $result[MESSAGE] = 'Invalid name';
            $result[ERROR] = 'name';
            return $result;
        }

        /* validate username */
        if (!$this->isValidInput($username, $confUMS[MIN_LENGHT_USERNAME], $confUMS[MAX_LENGTH_USERNAME], $confUMS[USE_REGEX], $confUMS[REGEX_USERNAME])) {
            $result[MESSAGE] = 'Invalid username';
            $result[ERROR] = 'username';
            return $result;
        }

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* check if username already exists */
        if ($user->getUserByUsername($username)) {
            $result[MESSAGE] = 'User already exist with this username';
            $result[ERROR] = 'username';
            return $result;
//             if ($this->isValidUser($usr, $confApp['requireConfirmEmail'])) {
//             }
//             $result['deleteUser'][] = $usr->id;
        }

        /* validate email */
        if (!$this->isValidEmail($email, $confUMS['useRegexEmail'], $confUMS['regexEmail'])) {
            $result[MESSAGE] = 'Invalid email';
            $result[ERROR] = 'email';
            return $result;
        }

        /* check if email already exists */
        if ($user->getUserByEmail($email)) {
            $result[MESSAGE] = 'User already exist with this email';
            $result[ERROR] = 'email';
            return $result;
//             if ($this->isValidUser($usr, $confApp['requireConfirmEmail'])) {
//             }
//             $result['deleteUser'][] = $usr->id;
        }

        /* validate password */
        if (!$this->isValidPassword($pass, $confUMS['minLengthPassword'], $confUMS['checkMaxLengthPassword'], $confUMS['maxLengthPassword'], $confUMS['requireHardPassword'], $confUMS['useRegex'], $confUMS['regexPassword'])) {
            $result[MESSAGE] = 'Invalid password';
            $result[ERROR] = 'pass';
            return $result;
        }

        /* confirm password */
        if ($pass !== $cpass) {
            $result[MESSAGE] = 'Passwords mismatch';
            $result[ERROR] = 'cpass';
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
            'changeEmail' => FALSE,
            MESSAGE => 'User update failed',
            SUCCESS => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate token and user id */
        if (!($this->verifyTokens($tokens) && ($usr = $user->getUser($id)))) return $result;

        /* get configuration of app */
        $confApp = $this->appConfig['app'];

        /* validate name */
        if (!$this->isValidInput($name, $confApp['minLengthName'], $confApp['maxLengthName'], $confApp['useRegex'], $confApp['regexName'])) {
            $result['message'] = 'Invalid name';
            $result['error'] = 'name';
            return $result;
        }

        /* validate username */
        if (!$this->isValidInput($username, $confApp['minLengthUsername'], $confApp['maxLengthUsername'], $confApp['useRegex'], $confApp['regexUsername'])) {
            $result['message'] = 'Invalid username';
            $result['error'] = 'username';
            return $result;
        }

        /* validate email */
        if (!$this->isValidEmail($email, $confApp['useRegexEmail'], $confApp['regexEmail'])) {
            $result['message'] = 'Invalid email';
            $result['error'] = 'email';
            return $result;
        }

        /* check if user already exists */
        if ($usr->username !== $username && $usrDel = $user->getUserByUsername($username)) {
            if ($this->isValidUser($usrDel, $confApp['requireConfirmEmail'])) {
                $result['message'] = 'User already exist with this username';
                $result['error'] = 'username';
                return $result;
            }
            $result['deleteUser'] = $usrDel->id;
        }

        /* check if email already exists */
        if ($usr->email !== $email) {
            if ($usrDel = $user->getUserByEmail($email)) {
                if ($this->isValidUser($usrDel, $confApp['requireConfirmEmail'])) {
                    $result['message'] = 'User already exist with this email';
                    $result['error'] = 'email';
                    return $result;
                }
                $result['deleteUser'] = $usrDel->id;
            }
            $result['changeEmail'] = TRUE;
        }

        /* unset error message and set success */
        unset($result['message']);
        $result['success'] = TRUE;

        /* return result */
        return $result;
    }

    /* function to verify a delete user request */
    public  function verifyDelete(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Delete user failed',
            'success' => false
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* validate tokens and user id */
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;

        /* unset error message and set success */
        unset($result['message']);
        $result['success'] = true;

        /* return result */
        return $result;
    }

    /* function to verifu delete new email requests */
    public function verifyDeleteNewEmail(int $id, array $tokens): array {
        /* set fail results */
        $result = [
            'message' => 'Delete email failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* init user model */
        $user = new User($this->conn, $this->appConfig);

        /* get user by id and check if they have a new email */
        if (!(($usr = $user->getUser($id)) && $usr->new_email)) return $result;

        /* unset error message and set success */
        unset($result['message']);
        $result['success'] = TRUE;

        /* return result */
        return $result;
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to validate the tokens */
    protected function verifyTokens(array $tokens): bool {
        return ($tokens[0] ?? 'tkn') === ($tokens[1] ?? '');
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
            $rgx = '/^((?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@!%*?&._\-]))[A-Za-z\d$@!%*?&._\-]{'.$minLength.',}$/';
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

//     /* function to check if user is a valid user */
//     protected function isValidUser($user, bool $requireConfirmEmail): bool {
//         return $user->enabled || !$requireConfirmEmail || !$user->token_account_enabler;
//     }

    /* function to check if is user enabled */
    protected function isUserEnable($user): bool {
        return (bool) $user->enabled;
    }

    /* function to check if user is temporarily locked */
    protected function isUserTempLocked($user): bool {
        return isset($user->datetime_unlock_user) && new DateTime($user->datetime_unlock_user) > new DateTime();
    }
}
