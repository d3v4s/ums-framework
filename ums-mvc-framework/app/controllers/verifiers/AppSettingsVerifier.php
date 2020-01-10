<?php
namespace app\controllers\verifiers;

/**
 * Class verifier, to verify the app setting request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AppSettingsVerifier extends Verifier {
//     private $timeUnitList = [];

    protected function __construct() {
        parent::__construct();
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

//     /* function to set list of unit times */
//     public function setTimeUnitList(array $timeUnitList) {
//         $this->timeUnitList = $timeUnitList;
//     }

    /* function to verify update of app settings */
    public function verifyAppSettingsUpdate(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Settings update failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate date format */
        $regexDateFormat = '/^[dDjlLNSwzFMmntLoyYcruaABgGhHisuv\-\\/_: ]+$/';
        if (!$this->isValidInput($data[DATE_FORMAT], 1, 255, TRUE, $regexDateFormat)) {
            $result[MESSAGE] = 'Invalid date format';
            $result[ERROR] = DATE_FORMAT;
            return $result;
        }

        /* validate datetime format */
        if (!$this->isValidInput($data[DATETIME_FORMAT], 1, 255, TRUE, $regexDateFormat)) {
            $result[MESSAGE] = 'Invalid datetime format';
            $result[ERROR] = DATETIME_FORMAT;
            return $result;
        }

        /* validate email from */
        if (!$this->isValidEmail($data[SEND_EMAIL_FROM], FALSE)) {
            $result[MESSAGE] = 'Invalid send email from';
            $result[ERROR] = SEND_EMAIL_FROM;
            return $result;
        }

        /* get result data */
        $this->hanlderAppSettingsData($data);

        /* unset error message */
        unset($result[MESSAGE]);
        /* set result, and return it */
        $result[SUCCESS] = TRUE;
        $result[DATA] = $data;
        return $result;
    }

    
    /* function to verify update of layout settings */
    public function verifyLayoutSettingsUpdate(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Settings update failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];
        
        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;
        
        /* get layout data */
        $dataRes = $this->getLayoutSettingsData($data);
        /* validate layout data */
        foreach ($dataRes as $nameLayout => $valueLayout) {
            /* validate name */
            if (!preg_match('/^[a-zA-Z\d_\-]+$/', $nameLayout)) {
                $result[MESSAGE] = 'Invalid layut name: '.$nameLayout;
                $result[ERROR] = array_search($nameLayout, $data);
                return $result;
            }
            /* validate value */
            if (!preg_match('/^[a-zA-Z\d_\-.]+$/', $valueLayout)) {
                $result[MESSAGE] = 'Invalid layut value: '.$valueLayout;
                $result[ERROR] = array_search($valueLayout, $data);
                return $result;
            }
        }
        
        /* unset errro message */
        unset($result[MESSAGE]);
        
        /* set result and return it */
        $result[SUCCESS] = TRUE;
        $result[DATA] = $dataRes;
        return $result;
    }

    /* function to verify update of rsa settings */
    public function verifyRsaSettingsUpdate(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Settings update failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate filename of rsa private key */
        if (strpos($data[RSA_PRIV_KEY_FILE], '/') !== FALSE) {
            $result[MESSAGE] = 'Invalid private key filename';
            $result[ERROR] = RSA_PRIV_KEY_FILE;
            return $result;
        }

        /* get result data */
        $this->handlerRsaSettingsData($data);

        /* unset error messsage */
        unset($result[MESSAGE]);

        /* set result and return it */
        $result[SUCCESS] = TRUE;
        $result[DATA] = $data;
        return $result;
    }

    /* function to verify update of secuity settings */
    public function verifySecuritySettingsUpdate(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Settings update failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;
        
//         /* validate max unconnected time on login session */
//         if (!$this->isValidNumber($data[MAX_TIME_UNCONNECTED_LOGIN_SESSION], -1, 9999)) {
//             $result[MESSAGE] = 'Invalid value of max time unconneted loggedin session';
//             $result[ERROR] = MAX_TIME_UNCONNECTED_LOGIN_SESSION;
//             return $result;
//         }
        
// //         /* validate max unconnected unit time on login session */
// //         if (!in_array($data[TIME_UNIT.MAX_TIME_UNCONNECTED_LOGIN_SESSION], $this->timeUnitList)) {
// //             $result[MESSAGE] = 'Invalid unit time of max unconneted loggedin session';
// //             $result[ERROR] = TIME_UNIT.MAX_TIME_UNCONNECTED_LOGIN_SESSION;
// //             return $result;
// //         }
        
//         /* validate max wrong passwords */
//         if (!$this->isValidNumber($data[MAX_WRONG_PASSWORDS], 0, 999)) {
//             $result[MESSAGE] = 'Invalid max wrong password';
//             $result[ERROR] = MAX_WRONG_PASSWORDS;
//             return $result;
//         }
        
//         /* validate password try time */
//         if (!$this->isValidNumber($data[PASS_TRY_TIME], 0, 9999)) {
//             $result[MESSAGE] = 'Invalid value of time try password';
//             $result[ERROR] = PASS_TRY_TIME;
//             return $result;
//         }
        
// //         /* validate pasword try unit time */
// //         if (!in_array($data[TIME_UNIT.PASS_TRY_TIME], TIME_UNIT_)) {
// //             $result[MESSAGE] = 'Invalid unit time of password try';
// //             $result[ERROR] = TIME_UNIT.PASS_TRY_TIME;
// //             return $result;
// //         }
        
//         /* validate user lock time */
//         if (!$this->isValidNumber($data[USER_LOCK_TIME], 0, 9999)) {
//             $result[MESSAGE] = 'Invalid value of user lock time';
//             $result[ERROR] = USER_LOCK_TIME;
//             return $result;
//         }
        
// //         /* validate user lock unit time */
// //         if (!in_array($data[TIME_UNIT.USER_LOCK_TIME], $this->timeUnitList)) {
// //             $result[MESSAGE] = 'Invalid unit time of user lock';
// //             $result[ERROR] = TIME_UNIT.USER_LOCK_TIME;
// //             return $result;
// //         }
        
//         /* validate n. max locks */
//         if (!$this->isValidNumber($data[MAX_LOCKS], 0, 9999)) {
//             $result[MESSAGE] = 'Invalid value of max locks';
//             $result[ERROR] = MAX_LOCKS;
//             return $result;
//         }

        /* get result data */
        $this->hanlderSecuritySettingsData($data);

        /* unset error message */
        unset($result[MESSAGE]);
        /* set result, and return it */
        $result[SUCCESS] = TRUE;
        $result[DATA] = $data;
        return $result;
    }

    /* function to verify update of ums settings */
    public function verifyUmsSettingsUpdate(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            MESSAGE => 'Settings update failed',
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];
        
        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate domain url for links to be create */
        if (!$this->isValidDomain($data[DOMAIN_URL_LINK])) {
            $result[MESSAGE] = 'Inavlid domain URL for links';
            $result[ERROR] = DOMAIN_URL_LINK;
            return $result;
        }

        /* validate enabler email from */
        if (!$this->isValidEmail($data[ENABLER_EMAIL_FROM], FALSE)) {
            $result[MESSAGE] = 'Invalid email for enabler email';
            $result[ERROR] = ENABLER_EMAIL_FROM;
            return $result;
        }

        
        /* validate reset password email from */
        if (!$this->isValidEmail($data[PASS_RESET_EMAIL_FROM], FALSE)) {
            $result[MESSAGE] = 'Invalid reset password email from';
            $result[ERROR] = PASS_RESET_EMAIL_FROM;
            return $result;
        }
        
        /* get result data */
        $this->hanlderUmsSettingsData($data);
        
        /* unset error message */
        unset($result[MESSAGE]);
        /* set result, and return it */
        $result[SUCCESS] = TRUE;
        $result[DATA] = $data;
        return $result;
    }


    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to manage result data of app settings */
    private function hanlderAppSettingsData(array &$data) {
        $this->trimData($data);
    }

    /* function to get result data of layout settings */
    private function getLayoutSettingsData(array $data): array {
        /* create layout name list */
        $nameList = [];
        foreach ($data as $key => $val) if (substr($key, 0, ($len = mb_strlen(NAME_LAYOUT_DATA))) === NAME_LAYOUT_DATA) $nameList[substr($key, $len)] = $val;

        /* create result data with name and value of layout */
        $dataRes = [];
        foreach ($nameList as $key => $name) $dataRes[$name] = $data[VAL_LAYOUT_DATA.$key];

        /* return result */
        return $dataRes;
    }

    /* function to manage result data of rsa settings */
    private function handlerRsaSettingsData(array &$data) {
        $this->trimData($data);
    }

    /* function manage result data of security settings */
    private function hanlderSecuritySettingsData(array &$data): array {
        $data[ONLY_HTTPS] = isset($data[ONLY_HTTPS]);
        $data[BLOCK_CHANGE_IP] = isset($data[BLOCK_CHANGE_IP]);
        $this->trimData($data);
    }

    /* function manage result data of UMS settings */
    private function hanlderUmsSettingsData(array &$data): array {
        $data[REQUIRE_CONFIRM_EMAIL] = isset($data[REQUIRE_CONFIRM_EMAIL]);
        $this->trimData($data);
    }

    /* function to remove spaces on data values */
    private function trimData(&$data) {
        foreach ($data as $key => $val) $data[$key] = trim($val, ' ');
    }
}
