<?php
namespace app\controllers\verifiers;

/**
 * Class verifier to validate rsa generate requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class RSAVerifier extends Verifier {
    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify generate key pair request */
    public function verifyKeyGenerate(array $tokens): array {
        /* set fail results */
        $result = [
            'message' => 'Generate rsa key pair failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* unset error messege and set success */
        unset($result['message']);
        $result['success'] = TRUE;

        /* return result */
        return $result;
    }

    /* function to verify generate and save key pair requests */
    public function verifyKeyGenerateSave(array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Save rsa key failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* unset error message and set success */
        unset($result['message']);
        $result['success'] = TRUE;

        /* return result */
        return $result;
    }
}

