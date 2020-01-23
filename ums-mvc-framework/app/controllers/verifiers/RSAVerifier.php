<?php
namespace app\controllers\verifiers;

/**
 * Class verifier to validate rsa generate requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class RSAVerifier extends Verifier {

    protected function __construct() {
        parent::__construct();
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify generate key pair request */
    public function verifyGenerateKey(array $tokens): array {
        /* set fail results */
        $result = [
            MESSAGE => 'Generate rsa key pair failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* unset error messege and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = TRUE;

        /* return result */
        return $result;
    }

    /* function to verify generate and save key pair requests */
    public function verifyKeyGenerateSave(array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Save rsa key failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* unset error message and set success */
        unset($result[MESSAGE]);
        $result[SUCCESS] = TRUE;

        /* return result */
        return $result;
    }
}

