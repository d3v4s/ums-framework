<?php
namespace app\controllers\verifiers;

use app\models\Role;

/**
 * Class verifier, to verify the app setting request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AppSettingsVerifier extends Verifier {
    private $timeUnitList = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->timeUnitList = getList(TIME_UNIT_LIST);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to set list of unit times */
    public function setTimeUnitList(array $timeUnitList) {
        $this->timeUnitList = $timeUnitList;
    }

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

        /* validate page not found */
        if (!$this->isValidInput($data[PAGE_NOT_FOUND], 1, 150, TRUE, '/^[a-zA-Z\d_\-.]+$/')) {
            $result[MESSAGE] = 'Invalid page not found';
            $result[ERROR] = PAGE_NOT_FOUND;
            return $result;
        }

        /* validate page exception */
        if (!$this->isValidInput($data[PAGE_EXCEPTION], 1, 150, TRUE, '/^[a-zA-Z\d_\-.]+$/')) {
            $result[MESSAGE] = 'Invalid page exception';
            $result[ERROR] = PAGE_EXCEPTION;
            return $result;
        }

        /* validate email from */
        if (!$this->isValidEmail($data[SEND_EMAIL_FROM], FALSE)) {
            $result[MESSAGE] = 'Invalid send email from';
            $result[ERROR] = SEND_EMAIL_FROM;
            return $result;
        }

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

        /* validate n. bits of private key */ 
        if (!$this->isValidNumber($data[PRIVATE_KEY_BITS], 1, 1024000)) {
            $result[MESSAGE] = 'Invalid private key bits';
            $result[ERROR] = PRIVATE_KEY_BITS;
            return $result;
        }

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
        
        /* validate max unconnected time on login session */
        if (!$this->isValidNumber($data[MAX_TIME_UNCONNECTED_LOGIN_SESSION], -1, 9999)) {
            $result[MESSAGE] = 'Invalid value of max time unconneted loggedin session';
            $result[ERROR] = MAX_TIME_UNCONNECTED_LOGIN_SESSION;
            return $result;
        }
        
        /* validate max unconnected unit time on login session */
        if (!in_array($data[TIME_UNIT.MAX_TIME_UNCONNECTED_LOGIN_SESSION], $this->timeUnitList)) {
            $result[MESSAGE] = 'Invalid unit time of max unconneted loggedin session';
            $result[ERROR] = TIME_UNIT.MAX_TIME_UNCONNECTED_LOGIN_SESSION;
            return $result;
        }
        
        /* validate max wrong passwords */
        if (!$this->isValidNumber($data[MAX_WRONG_PASSWORDS], 0, 999)) {
            $result[MESSAGE] = 'Invalid max wrong password';
            $result[ERROR] = MAX_WRONG_PASSWORDS;
            return $result;
        }
        
        /* validate password try time */
        if (!$this->isValidNumber($data[PASS_TRY_TIME], 0, 9999)) {
            $result[MESSAGE] = 'Invalid value of time try password';
            $result[ERROR] = PASS_TRY_TIME;
            return $result;
        }
        
        /* validate pasword try unit time */
        if (!in_array($data[TIME_UNIT.PASS_TRY_TIME], $this->timeUnitList)) {
            $result[MESSAGE] = 'Invalid unit time of password try';
            $result[ERROR] = TIME_UNIT.PASS_TRY_TIME;
            return $result;
        }
        
        /* validate user lock time */
        if (!$this->isValidNumber($data[USER_LOCK_TIME], 0, 9999)) {
            $result[MESSAGE] = 'Invalid value of user lock time';
            $result[ERROR] = USER_LOCK_TIME;
            return $result;
        }
        
        /* validate user lock unit time */
        if (!in_array($data[TIME_UNIT.USER_LOCK_TIME], $this->timeUnitList)) {
            $result[MESSAGE] = 'Invalid unit time of user lock';
            $result[ERROR] = TIME_UNIT.USER_LOCK_TIME;
            return $result;
        }
        
        /* validate n. max locks */
        if (!$this->isValidNumber($data[MAX_LOCKS], 0, 9999)) {
            $result[MESSAGE] = 'Invalid value of max locks';
            $result[ERROR] = MAX_LOCKS;
            return $result;
        }

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

        /* init role model */
        $role = new Role($this->conn);

        /* validate default user */
        if (!(is_numeric($data[DEFAULT_USER_ROLE]) && $role->getRole($data[DEFAULT_USER_ROLE]))) {
            $result[MESSAGE] = 'Invalid default user';
            $result[ERROR] = MIN_LENGTH_NAME;
        }
        
        /* validate min length of name */
        if (!$this->isValidNumber($data[MIN_LENGTH_NAME], 1, 255)) {
            $result[MESSAGE] = 'Invalid min lenght name';
            $result[ERROR] = MIN_LENGTH_NAME;
            return $result;
        }
        
        /* validate max legth of name */
        if (!$this->isValidNumber($data[MAX_LENGTH_NAME], 1, 255)) {
            $result[MESSAGE] = 'Invalid max lenght name';
            $result[ERROR] = MAX_LENGTH_NAME;
            return $result;
        }
        
        /* validate min length of username */
        if (!$this->isValidNumber($data[MIN_LENGTH_USERNAME], 1, 255)) {
            $result[MESSAGE] = 'Invalid min lenght username';
            $result[ERROR] = MIN_LENGTH_USERNAME;
            return $result;
        }
        
        /* validate max legth of username */
        if (!$this->isValidNumber($data[MAX_LENGTH_USERNAME], 1, 255)) {
            $result[MESSAGE] = 'Invalid max lenght username';
            $result[ERROR] = MAX_LENGTH_USERNAME;
            return $result;
        }
        
        /* validate min length of password */
        if (!$this->isValidNumber($data[MIN_LENGTH_PASS], 1, 255)) {
            $result[MESSAGE] = 'Invalid min lenght password';
            $result[ERROR] = MIN_LENGTH_PASS;
            return $result;
        }
        
        /* validate max legth of password */
        if (!$this->isValidNumber($data[MAX_LENGTH_PASS], 1, 255)) {
            $result[MESSAGE] = 'Invalid max lenght password';
            $result[ERROR] = MAX_LENGTH_PASS;
            return $result;
        }
        
        /* validate n. link visible on pagination */
        if (!$this->isValidNumber($data[LINK_PAGINATION], 1, 30)) {
            $result[MESSAGE] = 'Invalid n. link pagination';
            $result[ERROR] = LINK_PAGINATION;
            return $result;
        }

        /* validate enabler email from */
        if (!$this->isValidEmail($data[ENABLER_EMAIL_FROM], FALSE)) {
            $result[MESSAGE] = 'Invalid email for enabler email';
            $result[ERROR] = ENABLER_EMAIL_FROM;
            return $result;
        }

        /* validate domain url for links to be create */
        if (!$this->isValidDomain($data[DOMAIN_URL_LINK])) {
            $result[MESSAGE] = 'Inavlid domain URL for links';
            $result[ERROR] = DOMAIN_URL_LINK;
            return $result;
        }
        
        /* validate reset password email from */
        if (!$this->isValidEmail($data[PASS_RESET_EMAIL_FROM], FALSE)) {
            $result[MESSAGE] = 'Invalid reset password email from';
            $result[ERROR] = PASS_RESET_EMAIL_FROM;
            return $result;
        }
        
        /* validate expiration time of password reset link */
        if (!$this->isValidNumber($data[PASS_RESET_EXPIRE_TIME], -1, 9999)) {
            $result[MESSAGE] = 'Invalid value of password reset link expiration time';
            $result[ERROR] = PASS_RESET_EXPIRE_TIME;
            return $result;
        }
        
        /* validate expiration unit time of password reset link */
        if (!in_array($data[TIME_UNIT.PASS_RESET_EXPIRE_TIME], $this->timeUnitList)) {
            $result[MESSAGE] = 'Invalid time unit of password reset link expiration time';
            $result[ERROR] = TIME_UNIT.PASS_RESET_EXPIRE_TIME;
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
        $data[SHOW_MESSAGE_EXCEPTION] = isset($data[SHOW_MESSAGE_EXCEPTION]);
        $this->trimData($data);
//         return [
//         //             'urlServer' => $data['url-server'],
//             PAGE_NOT_FOUND => $data[PAGE],
//             'showMessageException' => isset($data['showMessageException']),
//             'pageException' => $data['pageException'],
//             'checkConnectTimeLoginSession' => isset($data['checkConnectTimeLoginSession']),
//             'maxTimeUnconnectedLoginSession' => trim($data['maxTimeUnconnectedLoginSession'], ' ').' '.trim($data['unitMaxTimeUnconnectedLoginSession'], ' '),
//             'maxWrongPassword' => $data['maxWrongPassword'],
//             'passwordTryTime' => trim($data['passwordTryTime'], ' ').' '.trim($data['unitPasswordTryTime'], ' '),
//             'userLockTime' => trim($data['userLockTime'], ' ').' '.trim($data['unitUserLockTime'], ' '),
//             'maxLocks' => $data['maxLocks'],
//             'minLengthName' => $data['minLengthName'],
//             'maxLengthName' => $data['maxLengthName'],
//             'minLengthUsername' => $data['minLengthUsername'],
//             'maxLengthUsername' => $data['maxLengthUsername'],
//             'minLengthPassword' => $data['minLengthPassword'],
//             'checkMaxLengthPassword' => isset($data['checkMaxLengthPassword']),
//             'maxLengthPassword' => $data['maxLengthPassword'],
//             'requireHardPassword' => isset($data['requireHardPassword']),
//             'passDefault' => $data['passDefault'],
//             'useRegex' => isset($data['useRegex']),
//             'regexName' => $data['regexName'],
//             'regexUsername' => $data['regexUsername'],
//             'regexPassword' => $data['regexPassword'],
//             'useRegexEmail' => isset($data['useRegexEmail']),
//             'regexEmail' => $data['regexEmail'],
//             'sendEmailFrom' => $data['sendEmailFrom'],
//             'requireConfirmEmail' => isset($data['requireConfirmEmail']),
//             'emailValidationFrom' => $data['emailValidationFrom'],
//             'useServerDomainEmailValidationLink' => isset($data['useServerDomainEmailValidationLink']),
//             'urlDomainEmailValidationLink' => $data['urlDomainEmailValidationLink'],
//             'emailResetPassFrom' => $data['emailResetPassFrom'],
//             'useServerDomainResetPassLink' => isset($data['useServerDomainResetPassLink']),
//             'urlDomainResetPasswordLink' => $data['urlDomainResetPasswordLink'],
//             'expirationTimeResetPassword' => trim($data['expirationTimeResetPassword'], ' ').' '.trim($data['unitExpirationTimeResetPassword'], ' '),
//             'addFakeUsersPage' => isset($data['addFakeUsersPage']),
//             'usersForPageList' => $data['usersForPageList'],
//             'linkPagination' => $data['linkPagination'],
//             'dateFormat' => $data['dateFormat'],
//             'datetimeFormat' => $data['datetimeFormat']
//         ];
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
//         return [
//             'digestAlg' => $data['digestAlg'],
//             'privateKeyBits' => $data['privateKeyBits'],
//             'rsaKeyStatic' => isset($data['rsaKeyStatic']),
//             'rsaPrivKeyFile' => $data['rsaPrivKeyFile']
//         ];
    }

    /* function manage result data of security settings */
    private function hanlderSecuritySettingsData(array &$data): array {
        $data[ONLY_HTTPS] = isset($data[ONLY_HTTPS]);
        $data[BLOCK_CHANGE_IP] = isset($data[BLOCK_CHANGE_IP]);
        $data[MAX_TIME_UNCONNECTED_LOGIN_SESSION] = trim($data[MAX_TIME_UNCONNECTED_LOGIN_SESSION], ' ').' '.trim($data[TIME_UNIT.MAX_TIME_UNCONNECTED_LOGIN_SESSION], ' ');
        $data[PASS_TRY_TIME] = trim($data[PASS_TRY_TIME], ' ').' '.trim($data[TIME_UNIT.PASS_TRY_TIME], ' ');
        $data[USER_LOCK_TIME] = trim($data[USER_LOCK_TIME], ' ').' '.trim($data[TIME_UNIT.USER_LOCK_TIME], ' ');
        $this->trimData($data);
    }

    /* function manage result data of UMS settings */
    private function hanlderUmsSettingsData(array &$data): array {
        $data[CHECK_MAX_LENGTH_PASS] = isset($data[CHECK_MAX_LENGTH_PASS]);
        $data[REQUIRE_HARD_PASS] = isset($data[REQUIRE_HARD_PASS]);
        $data[USE_REGEX] = isset($data[USE_REGEX]);
        $data[USE_REGEX_EMAIL] = isset($data[USE_REGEX_EMAIL]);
        $data[REQUIRE_CONFIRM_EMAIL] = isset($data[REQUIRE_CONFIRM_EMAIL]);
        $data[PASS_RESET_EXPIRE_TIME] = trim($data[PASS_RESET_EXPIRE_TIME], ' ').' '.trim($data[TIME_UNIT.PASS_RESET_EXPIRE_TIME], ' ');
        $this->trimData($data);
    }

    /* function to remove spaces on data values */
    private function trimData(&$data) {
        foreach ($data as $key => $val) $data[$key] = trim($val, ' ');
    }
}
