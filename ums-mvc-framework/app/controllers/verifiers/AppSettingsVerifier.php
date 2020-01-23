<?php
namespace app\controllers\verifiers;

/**
 * Class verifier, to verify the app setting request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AppSettingsVerifier extends Verifier {
//     private $timeUnitList = [];

    protected function __construct(array $langMessage) {
        parent::__construct($langMessage);
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
            MESSAGE => $this->langMessage[SAVE_SETTINGS][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate date format */
        $regexDateFormat = '/^[dDjlLNSwzFMmntLoyYcruaABgGhHisuv\-\\/_: ]+$/';
        if (!$this->isValidInput($data[DATE_FORMAT], 1, 255, TRUE, $regexDateFormat)) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_DATE];
            $result[ERROR] = DATE_FORMAT;
            return $result;
        }

        /* validate datetime format */
        if (!$this->isValidInput($data[DATETIME_FORMAT], 1, 255, TRUE, $regexDateFormat)) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_DATETIME];
            $result[ERROR] = DATETIME_FORMAT;
            return $result;
        }

        /* validate email from */
        if (!$this->isValidEmail($data[SEND_EMAIL_FROM], 3, 255, FALSE)) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_EMAIL];
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
            MESSAGE => $this->langMessage[SAVE_SETTINGS][FAIL],
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
                $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_LAYOUT].': '.$nameLayout;
                $result[ERROR] = array_search($nameLayout, $data);
                return $result;
            }
            /* validate value */
            if (!preg_match('/^[a-zA-Z\d_\-.]+$/', $valueLayout)) {
                $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_LAYOUT].': '.$valueLayout;
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
            MESSAGE => $this->langMessage[SAVE_SETTINGS][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate filename of rsa private key */
        if (strpos($data[RSA_PRIV_KEY_FILE], '/') !== FALSE) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_FILEPATH];
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
            MESSAGE => $this->langMessage[SAVE_SETTINGS][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];

        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;
        
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
            MESSAGE => $this->langMessage[SAVE_SETTINGS][FAIL],
            SUCCESS => FALSE,
            GENERATE_TOKEN => FALSE
        ];
        
        /* verify tokens, and if is valid set generate token */
        if (!$this->verifyTokens($tokens)) return $result;
        $result[GENERATE_TOKEN] = TRUE;

        /* validate domain url for links to be create */
        if (!$this->isValidDomain($data[DOMAIN_URL_LINK])) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_DOMAIN];
            $result[ERROR] = DOMAIN_URL_LINK;
            return $result;
        }

        /* validate enabler email from */
        if (!$this->isValidEmail($data[ENABLER_EMAIL_FROM], 3, 255, FALSE)) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_EMAIL];
            $result[ERROR] = ENABLER_EMAIL_FROM;
            return $result;
        }

        
        /* validate reset password email from */
        if (!$this->isValidEmail($data[PASS_RESET_EMAIL_FROM], 3, 255, FALSE)) {
            $result[MESSAGE] = $this->langMessage[GENERIC][INVALID_EMAIL];
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
    private function hanlderSecuritySettingsData(array &$data) {
        $data[ONLY_HTTPS] = isset($data[ONLY_HTTPS]);
        $data[BLOCK_CHANGE_IP] = isset($data[BLOCK_CHANGE_IP]);
        $this->trimData($data);
    }

    /* function manage result data of UMS settings */
    private function hanlderUmsSettingsData(array &$data) {
        $data[REQUIRE_CONFIRM_EMAIL] = isset($data[REQUIRE_CONFIRM_EMAIL]);
        $this->trimData($data);
    }

    /* function to remove spaces on data values */
    private function trimData(&$data) {
        foreach ($data as $key => $val) $data[$key] = trim($val, ' ');
    }
}
