<?php
namespace app\controllers\verifiers;

class RSAVerifier extends Verifier {
    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
    }

    public function verifyKeyGenerate(array $tokens): array {
        $result = [
            'message' => 'Generate rsa key pair failed',
            'success' => FALSE
        ];

        if (!$this->verifyTokens($tokens)) return $result;

        unset($result['message']);
        $result['success'] = TRUE;
        return $result;
    }

    public function verifyKeyGenerateSave(array $tokens): array {
        $result = [
            'message' => 'Save rsa key failed',
            'success' => FALSE
        ];
        
        if (!$this->verifyTokens($tokens)) return $result;
        
        unset($result['message']);
        $result['success'] = TRUE;
        return $result;
    }
}

