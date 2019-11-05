<?php
namespace app\controllers\verifiers;

class EmailVerifier extends Verifier {
    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
    }

    public function verifySendEmail(string $from, string $to, array $tokens): array {
        $res = [
            'success' => FALSE,
            'message' => 'Send email failed'
        ];
        
        if (!$this->verifyTokens($tokens)) return $res;

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $res['message'] = 'From email wrong';
            $res['error'] = 'from';
            return $res;
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $res['message'] = 'To email wrong';
            $res['error'] = 'to';
            return $res;
        }
        
        unset($res['message']);
        $res['success'] = TRUE;
        return $res;
    }
}
