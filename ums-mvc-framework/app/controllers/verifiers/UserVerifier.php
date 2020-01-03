<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;

class UserVerifier extends Verifier {
    protected function __construct(array $appConfig, PDO $conn) {
        parent::__construct($appConfig, $conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* fucntio to verify a resend validation new email request */
    public function verifyResendNewEmailValidation(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Sending email failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* init user model */
        $user = new User($this->conn, $this->appConfig);
        /* validate user id and check if user has new email on pending */
        if (!(($usr = $user->getUser($id)) && $usr->new_email && $usr->token_confirm_email)) return $result;

        /* unset error message */
        unset($result['message']);

        /* set success result and return it */
        $result['email'] = $usr->new_email;
        $result['token'] = $usr->token_confirm_email;
        $result['success'] = TRUE;
        return $result;
    }

    /* function to verify change password request */
    public function verifyChangePass(int $id, string $oldPass, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            'wrongPass' => FALSE,
            'message' => 'Change password failed',
            'success' => FALSE
        ];

        /* init user model */
        $user = new User($this->conn, $this->appConfig);
        /* validate tokens and user id, next check if user is locked or disabled */
        if (!($this->verifyTokens($tokens) && ($usr = $user->getUser($id, FALSE)) && !$this->isUserTempLocked($usr) && $this->isUserEnable($usr))) return $result;

        /* validate old password */
        if (!password_verify($oldPass, $usr->password)) {
            $result['wrongPass'] = TRUE;
            $result['message'] = 'Wrong current password';
            $result['error'] = 'old-pass';
            return $result;
        }

        /* unset user var */
        unset($usr);
        /* get app config */
        $confApp = $this->appConfig['app'];

        /* validate new password */
        if (!$this->isValidPassword($pass, $confApp['minLengthPassword'], $confApp['checkMaxLengthPassword'], $confApp['maxLengthPassword'], $confApp['requireHardPassword'], $confApp['useRegex'], $confApp['regexPassword'])) {
            $result['message'] = 'Invalid new password';
            $result['error'] = 'pass';
            return $result;
        }

        /* confirm new password */
        if ($pass !== $cpass) {
            $result['message'] = 'New passwords mismatch';
            $result['error'] = 'cpass';
            return $result;
        }

        /* unset error message */
        unset($result['message']);

        /* set success and return result */
        $result['success'] = TRUE;
        return $result;
    }
}

