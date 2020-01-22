<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use app\models\PendingEmail;

class AccountVerifier extends Verifier {
    protected function __construct(PDO $conn, array $langMessage) {
        parent::__construct($conn, $langMessage);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* fucntio to verify a resend validation new email request */
    public function verifyResendNewEmailValidation(int $userId, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => $this->langMessage[SEND_EMAIL][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init user model */
        $pendMail = new PendingEmail($this->conn);
        if (!($email = $pendMail->getValidPendingEmailByUserId($userId))) return $result;

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success result and return it */
        $result[TO] = $email->{NEW_EMAIL};
        $result[TOKEN] = $email->{ENABLER_TOKEN};
        $result[SUCCESS] = TRUE;
        return $result;
    }

    /* function to verify delete new email requests */
    public function verifyDeleteNewEmail(int $userId, array $tokens): array {
        /* set fail results */
        $result = [
            MESSAGE => $this->langMessage[NEW_EMAIL_DELETE][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];
        
        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;
        
        /* init pending mail model */
        $pendMail = new PendingEmail($this->conn);
        
        /* get pending emails by id and check if they have a new email */
        if (!$pendMail->getValidPendingEmailByUserId($userId)) return $result;
        
        /* unset error message and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = TRUE;
        
        /* return result */
        return $result;
    }

    /* function to verify change password request */
    public function verifyChangePass(int $userId, string $oldPass, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            WRONG_PASSWORD => FALSE,
            MESSAGE => $this->langMessage[CHANGE_PASS][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* init user model */
        $user = new User($this->conn);

        /* validate tokens and user id, next check if user is locked or disabled */
        if (!($this->verifyTokens($tokens) && ($usr = $user->getUser($userId, FALSE)) && !$this->isUserLockedOrDisabled($usr) && $this->isUserEnable($usr))) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate old password */
        if (!password_verify($oldPass, $usr->{PASSWORD})) {
            $result[WRONG_PASSWORD] = TRUE;
            $result[MESSAGE] = $this->langMessage[GENERIC][WRONG_PASSWORD];
            $result[ERROR] = OLD_PASS;
            return $result;
        }

        /* unset user var */
        unset($usr);
        /* get app config */
//         $confApp = $this->appConfig['app'];

        /* validate new password */
        if (!$this->isValidInput($pass, MIN_LENGTH_PASS, MAX_LENGTH_PASS, USE_REGEX_PASSWORD, REGEX_PASSWORD)) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_PASSWORD];
            $result[ERROR] = PASSWORD;
            return $result;
        }

        /* confirm new password */
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
