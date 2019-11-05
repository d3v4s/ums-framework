<?php
namespace app\controllers\verifiers;

class FakeUsersVerifier extends Verifier {
    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
    }

    public function verifyAddFakeUsers($nFakeUsers, array $tokens): array {
        $res = [
            'success' => FALSE,
            'message' => 'Add fake users failed'
        ];

        if (!$this->verifyTokens($tokens)) return $res;

        if (!is_numeric($nFakeUsers) || $nFakeUsers < 0) {
            $res['message'] = 'Invalid n. fake users to add';
            $res['error'] = 'n-users';
            return $res;
        }

        unset($res['message']);
        $res['success'] = TRUE;
        return $res;
    }
}

