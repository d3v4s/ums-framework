<?php
namespace app\controllers\verifiers;

use app\models\User;
use \PDO;
use app\models\PendingEmail;

class AccountVerifier extends Verifier {
    protected function __construct(PDO $conn) {
        parent::__construct($conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* fucntio to verify a resend validation new email request */
    public function verifyResendNewEmailValidation(int $id, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Sending email failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* init user model */
        $pendMail = new PendingEmail($this->conn);
        if (!($email = $pendMail->getPendingEmailByUserId($id))) return $result;

        /* unset error message */
        unset($result[MESSAGE]);

        /* set success result and return it */
        $result[TO] = $email->{NEW_EMAIL};
        $result[TOKEN] = $email->{ENABLER_TOKEN};
        $result[SUCCESS] = TRUE;
        return $result;
    }

    /* function to verify change password request */
    public function verifyChangePass(int $id, string $oldPass, string $pass, string $cpass, array $tokens): array {
        /* set fail result */
        $result = [
            WRONG_PASSWORD => FALSE,
            MESSAGE => 'Change password failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* init user model */
        $user = new User($this->conn);

        /* validate tokens and user id, next check if user is locked or disabled */
        if (!($this->verifyTokens($tokens) && ($usr = $user->getUser($id, FALSE)) && !$this->isUserLockedOrDisabled($usr) && $this->isUserEnable($usr))) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate old password */
        if (!password_verify($oldPass, $usr->{PASSWORD})) {
            $result[WRONG_PASSWORD] = TRUE;
            $result[MESSAGE] = 'Wrong current password';
            $result[ERROR] = OLD_PASS;
            return $result;
        }

        /* unset user var */
        unset($usr);
        /* get app config */
//         $confApp = $this->appConfig['app'];

        /* validate new password */
        if (!$this->isValidInput($pass, MIN_LENGTH_PASS, MAX_LENGTH_PASS, USE_REGEX_PASSWORD, REGEX_PASSWORD)) {
            $result[MESSAGE] = 'Invalid new password';
            $result[ERROR] = PASSWORD;
            return $result;
        }

        /* confirm new password */
        if ($pass !== $cpass) {
            $result[MESSAGE] = 'New passwords mismatch';
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
