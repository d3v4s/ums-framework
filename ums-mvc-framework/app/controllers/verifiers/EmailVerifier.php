<?php
namespace app\controllers\verifiers;

/**
 * Class verifier, to validate email requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class EmailVerifier extends Verifier {
    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify send email request */
    public function verifySendEmail(string $from, string $to, array $tokens): array {
        /* set fail result */
        $res = [
            MESSAGE => 'Send email failed',
            SUCCESS => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $res;

        /* validate from email */
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $res[MESSAGE] = 'From email wrong';
            $res[ERROR] = 'from';
            return $res;
        }

        /* validate to email */
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $res[MESSAGE] = 'To email wrong';
            $res[ERROR] = 'to';
            return $res;
        }

        /* unset error message */
        unset($res[MESSAGE]);

        /*set success result and return it */
        $res[SUCCESS] = TRUE;
        return $res;
    }
}
