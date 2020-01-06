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
            MESSAGE => 'Add fake users failed',
            GENERATE_TOKEN => FALSE,
            SUCCESS => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $res;
        $res[GENERATE_TOKEN] = TRUE;

        /* validate n. fake user */
        if (!is_numeric($nFakeUsers) || $nFakeUsers < 0) {
            $res[MESSAGE] = 'Invalid n. fake users to add';
            $res[ERROR] = N_USERS;
            return $res;
        }

        /* unset error message */
        unset($res[MESSAGE]);

        /* set success result and return it */
        $res[SUCCESS] = TRUE;
        return $res;
    }
}
