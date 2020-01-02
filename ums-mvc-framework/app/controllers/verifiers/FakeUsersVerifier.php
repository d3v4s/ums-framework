<?php
namespace app\controllers\verifiers;

/**
 * Class verifier to validate a fake user request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class FakeUsersVerifier extends Verifier {
    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to verify a add fake user request */
    public function verifyAddFakeUsers($nFakeUsers, array $tokens): array {
        /* set fail result */
        $res = [
            'success' => FALSE,
            'message' => 'Add fake users failed'
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $res;

        /* validate n. fake user */
        if (!is_numeric($nFakeUsers) || $nFakeUsers < 0) {
            $res['message'] = 'Invalid n. fake users to add';
            $res['error'] = 'n-users';
            return $res;
        }

        /* unset error message */
        unset($res['message']);

        /* set success result and return it */
        $res['success'] = TRUE;
        return $res;
    }
}
