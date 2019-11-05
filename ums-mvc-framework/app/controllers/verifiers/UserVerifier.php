<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;

class UserVerifier extends Verifier {
    protected function __construct(array $appConfig, PDO $conn) {
        parent::__construct($appConfig, $conn);
    }

    public function verifyResendNewEmailValidation(int $id, array $tokens): array {
        $result = [
            'message' => 'Sending email failed',
            'success' => FALSE
        ];

        if (!$this->verifyTokens($tokens)) return $result;

        $user = new User($this->conn, $this->appConfig);
        if (!(($usr = $user->getUser($id)) && $usr->new_email && $usr->token_confirm_email)) return $result;

        unset($result['message']);
        $result['email'] = $usr->new_email;
        $result['token'] = $usr->token_confirm_email;
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyChangePass(int $id, string $oldPass, string $pass, string $cpass, array $tokens): array {
        $result = [
            'wrongPass' => FALSE,
            'message' => 'Change password failed by ver',
            'success' => FALSE
        ];
        
        $user = new User($this->conn, $this->appConfig);
        if (!($this->verifyTokens($tokens) && ($usr = $user->getUser($id, FALSE)) && !$this->isUserTempLocked($usr) && $this->isUserEnable($usr))) return $result;
        
        if (!password_verify($oldPass, $usr->password)) {
            $result['wrongPass'] = TRUE;
            $result['message'] = 'Wrong current password';
            $result['error'] = 'old-pass';
            return $result;
        }
        unset($usr);
        $confApp = $this->appConfig['app'];
        if (!$this->isValidPassword($pass, $confApp['minLengthPassword'], $confApp['checkMaxLengthPassword'], $confApp['maxLengthPassword'], $confApp['requireHardPassword'], $confApp['useRegex'], $confApp['regexPassword'])) {
            $result['message'] = 'Invalid new password';
            $result['error'] = 'pass';
            return $result;
        }
        if ($pass !== $cpass) {
            $result['message'] = 'New passwords mismatch';
            $result['error'] = 'cpass';
            return $result;
        }
        
        unset($result['message']);
        $result['success'] = TRUE;
        
        return $result;
    }
}

