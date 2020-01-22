<?php
namespace app\controllers\verifiers;

/**
 * Class verifier, to validate email requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class EmailVerifier extends Verifier {
    protected function __construct(array $langMessage) {
        parent::__construct(NULL, $langMessage);
    }

    /* singleton */
    static public function getInstance(array $langMessage=[]): EmailVerifier {
        if (!isset(static::$instance)) static::$instance = new static($langMessage);
        return static::$instance;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify send email request */
    public function verifySendEmail(string $from, string $to, array $tokens): array {
        /* set fail result */
        $res = [
            MESSAGE => $this->langMessage[SEND_EMAIL][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $res;
        $res[GENERATE_TOKEN] = TRUE;

        /* validate from email */
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $res[MESSAGE] = $this->langMessage[GENERIC][INVALID_EMAIL].': "from"';
            return $res;
        }

        /* validate to email */
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $res[MESSAGE] = $this->langMessage[GENERIC][INVALID_EMAIL];
            $res[ERROR] = TO;
            return $res;
        }

        /* unset error message */
        unset($res[MESSAGE]);

        /*set success result and return it */
        $res[SUCCESS] = TRUE;
        return $res;
    }
}
