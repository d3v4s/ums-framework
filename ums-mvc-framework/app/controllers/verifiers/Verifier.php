<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use \DateTime;

class Verifier {
    protected $appConfig;
    protected $conn;
    static protected $instance;

    static public function getInstance(array $appConfig, PDO $conn = NULL): Verifier {
        if (!isset(static::$instance)) static::$instance = new static($appConfig, $conn);
        return static::$instance;
    }

    protected function __construct(array $appConfig, PDO $conn = NULL) {
        $this->appConfig = $appConfig ?? getConfig();
        $this->conn = $conn;
    }

    public function verifyWrongPassword(int $nWrongPass, int $nLock): array {
        $result = [
            'lock' => FALSE,
            'disable' => FALSE
        ];

        if ($nWrongPass >= $this->appConfig['app']['maxWrongPassword']) {
            $result['lock'] = TRUE;
            $nLock++;
        }

        if ($nLock >= $this->appConfig['app']['maxLocks']) {
            $result['disable'] = TRUE;
            $result['lock'] = FALSE;
        }

        return $result;
    }

    public function verifySignup(string $name, string $email, string $username, string $pass, string $cpass, array $tokens): array {
        $result = [
            'message' => 'Signup failed',
            'success' => false
        ];
        
        if (!$this->verifyTokens($tokens)) return $result;
        
        $confApp = $this->appConfig['app'];
        if (!$this->isValidInput($name, $confApp['minLengthName'], $confApp['maxLengthName'], $confApp['useRegex'], $confApp['regexName'])) {
            $result['message'] = 'Invalid name';
            $result['error'] = 'name';
            return $result;
        }
        if (!$this->isValidInput($username, $confApp['minLengthUsername'], $confApp['maxLengthUsername'], $confApp['useRegex'], $confApp['regexUsername'])) {
            $result['message'] = 'Invalid username';
            $result['error'] = 'username';
            return $result;
        }
        $user = new User($this->conn, $this->appConfig);
        if ($usr = $user->getUserByUsername($username)) {
            if ($this->isValidUser($usr, $confApp['requireConfirmEmail'])) {
                $result['message'] = 'User already exist with this username';
                $result['error'] = 'username';
                return $result;
            }
            $result['deleteUser'][] = $usr->id;
        }
        if (!$this->isValidEmail($email, $confApp['useRegexEmail'], $confApp['regexEmail'])) {
            $result['message'] = 'Invalid email';
            $result['error'] = 'email';
            return $result;
        }
        if ($usr = $user->getUserByEmail($email)) {
            if ($this->isValidUser($usr, $confApp['requireConfirmEmail'])) {
                $result['message'] = 'User already exist with this email';
                $result['error'] = 'email';
                return $result;
            }
            $result['deleteUser'][] = $usr->id;
        }
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
        $result['success'] = true;
        
        return $result;
    }
    
    public function verifyUpdate(int $id, string $name, string $email, string $username, array $tokens): array {
        $result = [
            'changeEmail' => FALSE,
            'message' => 'User update failed',
            'success' => FALSE
        ];
        
        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && ($usr = $user->getUser($id)))) return $result;
        
        $confApp = $this->appConfig['app'];
        if (!$this->isValidInput($name, $confApp['minLengthName'], $confApp['maxLengthName'], $confApp['useRegex'], $confApp['regexName'])) {
            $result['message'] = 'Invalid name';
            $result['error'] = 'name';
            return $result;
        }
        if (!$this->isValidInput($username, $confApp['minLengthUsername'], $confApp['maxLengthUsername'], $confApp['useRegex'], $confApp['regexUsername'])) {
            $result['message'] = 'Invalid username';
            $result['error'] = 'username';
            return $result;
        }
        if (!$this->isValidEmail($email, $confApp['useRegexEmail'], $confApp['regexEmail'])) {
            $result['message'] = 'Invalid email';
            $result['error'] = 'email';
            return $result;
        }
        
        if ($usr->username !== $username && $usrDel = $user->getUserByUsername($username)) {
            if ($this->isValidUser($usrDel, $confApp['requireConfirmEmail'])) {
                $result['message'] = 'User already exist with this username';
                $result['error'] = 'username';
                return $result;
            }
            $result['deleteUser'] = $usrDel->id;
        }
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
        
        unset($result['message']);
        $result['success'] = TRUE;
        
        return $result;
    }
    
    public  function verifyDelete(int $id, array $tokens): array {
        $result = [
            'message' => 'Delete user failed',
            'success' => false
        ];
        
        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && $user->getUser($id))) return $result;
        
        unset($result['message']);
        $result['success'] = true;

        return $result;
    }

    public function verifyDeleteNewEmail(int $id, array $tokens): array {
        $result = [
            'message' => 'Delete email failed',
            'success' => FALSE
        ];
        
        if (!$this->verifyTokens($tokens)) return $result;
        
        $user = new User($this->conn, $this->appConfig);
        if (!(($usr = $user->getUser($id)) && $usr->new_email)) return $result;
        
        unset($result['message']);
        $result['success'] = TRUE;
        return $result;
    }

    protected function verifyTokens(array $tokens): bool {
        return ($tokens[0] ?? 'tkn') === ($tokens[1] ?? '');
    }

    protected function isValidInput(string $input, int $minLength, int $maxLength, bool $useRegex, string $regex = ''): bool {
        if (strlen($input) < $minLength || strlen($input) > $maxLength || ($useRegex && !preg_match($regex, $input))) return FALSE;

        return TRUE;
    }

    protected function isValidEmail(string $email, bool $useRegex, string $regex = ''): bool {
        if ($useRegex) if (!preg_match($regex, $email)) return FALSE;
        else if (!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) return FALSE;

        return TRUE;
    }

    protected function isValidPassword(string $password, int $minLength, bool $checkMaxLength, int $maxLength, bool $requireHardPassword, bool $useRegex, string $regex = ''): bool {
        if (strlen($password) < $minLength || ($checkMaxLength && strlen($password) > $maxLength)) return FALSE;
        if ($requireHardPassword) {
            $rgx = '/^((?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@!%*?&._\-]))[A-Za-z\d$@!%*?&._\-]{'.$minLength.',}$/';
            if (!preg_match($rgx, $password)) return FALSE;
        }
        if ($useRegex && !preg_match($regex, $password)) return FALSE;

        return TRUE;
    }

    protected function isValidDomain(string $domain): bool {
        return (bool) filter_var($domain, FILTER_VALIDATE_DOMAIN);
    }

    protected function isValidUser($user, bool $requireConfirmEmail): bool {
        return $user->enabled || !$requireConfirmEmail || !$user->token_account_enabler;
    }

    protected function isValidNumber($number, int $minValue, int $maxValue): bool {
        return is_numeric($number) && verifyNumVarRange($number, $minValue, $maxValue);
    }

    protected function isUserEnable($user): bool {
        return (bool) $user->enabled;
    }

    protected function isUserTempLocked($user): bool {
        return isset($user->datetime_unlock_user) && new DateTime($user->datetime_unlock_user) > new DateTime();
    }
}
