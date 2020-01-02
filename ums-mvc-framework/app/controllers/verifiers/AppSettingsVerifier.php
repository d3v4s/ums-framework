<?php
namespace app\controllers\verifiers;

/**
 * Class verifier, to verify the app setting request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AppSettingsVerifier extends Verifier {
    private $unitsTimeList = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->unitsTimeList = getList('unitsTime');
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to set list of unit times */
    public function setUnitTimeList(array $unitTimeList) {
        $this->unitsTimeList = $unitTimeList;
    }

    /* function to verify update of app settings */
    public function verifyUpdateAppSettings(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Settings update failed',
            'success' => FALSE
        ];

        /* verify tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* validate page not found */
        if (!$this->isValidInput($data['pageNotFound'], 1, 150, TRUE, '/^[a-zA-Z\d_\-.]+$/')) {
            $result['message'] = 'Invalid page not found';
            $result['error'] = 'pageNotFound';
            return $result;
        }

        /* validate page exception */
        if (!$this->isValidInput($data['pageException'], 1, 150, TRUE, '/^[a-zA-Z\d_\-.]+$/')) {
            $result['message'] = 'Invalid page exception';
            $result['error'] = 'pageException';
            return $result;
        }

        /* validate max unconnected time on login session */
        if (!$this->isValidNumber($data['maxTimeUnconnectedLoginSession'], -1, 9999)) {
            $result['message'] = 'Invalid value of max time unconneted loggedin session';
            $result['error'] = 'maxTimeUnconnectedLoginSession';
            return $result;
        }

        /* validate max unconnected unit time on login session */
        if (!in_array($data['unitMaxTimeUnconnectedLoginSession'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of max unconneted loggedin session';
            $result['error'] = 'unitMaxTimeUnconnectedLoginSession';
            return $result;
        }

        /* validate max wrong passwords */
        if (!$this->isValidNumber($data['maxWrongPassword'], 0, 999)) {
            $result['message'] = 'Invalid max wrong password';
            $result['error'] = 'maxWrongPassword';
            return $result;
        }

        /* validate password try time */
        if (!$this->isValidNumber($data['passwordTryTime'], 0, 9999)) {
            $result['message'] = 'Invalid value of time try password';
            $result['error'] = 'passwordTryTime';
            return $result;
        }

        /* validate pasword try unit time */
        if (!in_array($data['unitPasswordTryTime'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of password try';
            $result['error'] = 'unitPasswordTryTime';
            return $result;
        }

        /* validate user lock time */
        if (!$this->isValidNumber($data['userLockTime'], 0, 9999)) {
            $result['message'] = 'Invalid value of user lock time';
            $result['error'] = 'userLockTime';
            return $result;
        }

        /* validate user lock unit time */
        if (!in_array($data['unitUserLockTime'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of user lock';
            $result['error'] = 'unitUserLockTime';
            return $result;
        }

        /* validate n. max locks */
        if (!$this->isValidNumber($data['maxLocks'], 0, 9999)) {
            $result['message'] = 'Invalid value of max locks';
            $result['error'] = 'maxLocks';
            return $result;
        }

        /* validate min length of name */
        if (!$this->isValidNumber($data['minLengthName'], 1, 255)) {
            $result['message'] = 'Invalid min lenght name';
            $result['error'] = 'minLengthName';
            return $result;
        }

        /* validate max legth of name */
        if (!$this->isValidNumber($data['maxLengthName'], 1, 255)) {
            $result['message'] = 'Invalid max lenght name';
            $result['error'] = 'maxLengthName';
            return $result;
        }

        /* validate min length of username */
        if (!$this->isValidNumber($data['minLengthUsername'], 1, 255)) {
            $result['message'] = 'Invalid min lenght username';
            $result['error'] = 'minLengthUsername';
            return $result;
        }

        /* validate max legth of username */
        if (!$this->isValidNumber($data['maxLengthUsername'], 1, 255)) {
            $result['message'] = 'Invalid max lenght username';
            $result['error'] = 'maxLengthUsername';
            return $result;
        }

        /* validate min length of password */
        if (!$this->isValidNumber($data['minLengthPassword'], 1, 255)) {
            $result['message'] = 'Invalid min lenght password';
            $result['error'] = 'minLengthPassword';
            return $result;
        }

        /* validate max legth of password */
        if (!$this->isValidNumber($data['maxLengthPassword'], 1, 255)) {
            $result['message'] = 'Invalid max lenght password';
            $result['error'] = 'maxLengthPassword';
            return $result;
        }

        /* validate email from */
        if (!$this->isValidEmail($data['sendEmailFrom'], FALSE)) {
            $result['message'] = 'Invalid send email from';
            $result['error'] = 'sendEmailFrom';
            return $result;
        }

        /* validate email validator from */
        if (!$this->isValidEmail($data['emailValidationFrom'], FALSE)) {
            $result['message'] = 'Invalid email vaildation from';
            $result['error'] = 'emailValidationFrom';
            return $result;
        }

        /* validate url for email validator link */
        if (!$this->isValidDomain($data['urlDomainEmailValidationLink'])) {
            $result['message'] = 'Inavlid URL domain for email vaildation link';
            $result['error'] = 'urlDomainEmailValidationLink';
            return $result;
        }

        /* validate email reset password from */
        if (!$this->isValidEmail($data['emailResetPassFrom'], FALSE)) {
            $result['message'] = 'Invalid email reset password from';
            $result['error'] = 'emailResetPassFrom';
            return $result;
        }

        /* validate url for pasword reset link */
        if (!$this->isValidDomain($data['urlDomainResetPasswordLink'])) {
            $result['message'] = 'Inavlid URL domain for email vaildation link';
            $result['error'] = 'urlDomainResetPasswordLink';
            return $result;
        }

        /* validate expiration time of password reset link */
        if (!$this->isValidNumber($data['expirationTimeResetPassword'], -1, 9999)) {
            $result['message'] = 'Invalid value of reset password link expiration time';
            $result['error'] = 'expirationTimeResetPassword';
            return $result;
        }

        /* validate expiration unit time of password reset link */
        if (!in_array($data['unitExpirationTimeResetPassword'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of reset password link expiration';
            $result['error'] = 'unitExpirationTimeResetPassword';
            return $result;
        }

        /* validate list of n. users for page */
        if (!preg_match('/^[\d,]+$/', $data['usersForPageList'])) {
            $result['message'] = 'Invalid users for page list';
            $result['error'] = 'usersForPageList';
            return $result;
        }

        /* validate n. link visible on pagination */
        if (!$this->isValidNumber($data['linkPagination'], 1, 30)) {
            $result['message'] = 'Invalid n. link pagination';
            $result['error'] = 'linkPagination';
            return $result;
        }

        /* validate date format */
        $regexDateFormat = '/^[dDjlLNSwzFMmntLoyYcruaABgGhHisuv\-\\/_: ]+$/';
        if (!preg_match($regexDateFormat, $data['dateFormat'])) {
            $result['message'] = 'Invalid date format';
            $result['error'] = 'dateFormat';
            return $result;
        }

        /* validate datetime format */
        if (!preg_match($regexDateFormat, $data['datetimeFormat'])) {
            $result['message'] = 'Invalid datetime format';
            $result['error'] = 'datetimeFormat';
            return $result;
        }

        /* get result data */
        $data = $this->getDataAppSettingsIni($data);

        /* unset error message */
        unset($result['message']);
        /* set result, and return it */
        $result['success'] = TRUE;
        $result['data'] = $data;
        return $result;
    }

    /* function to verify update of rsa settings */
    public function verifyUpdateRsaSettings(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Settings update failed',
            'success' => FALSE
        ];

        /* validate tokens */
        if (!$this->verifyTokens($tokens)) return $result;

        /* validate n. bits of private key */ 
        if (!$this->isValidNumber($data['privateKeyBits'], 1, 1024000)) {
            $result['message'] = 'Invalid private key bits';
            $result['error'] = 'privateKeyBits';
            return $result;
        }

        /* validate filename of rsa private key */
        if (strpos($data['rsaPrivKeyFile'], '/') !== FALSE) {
            $result['message'] = 'Invalid private key filename';
            $result['error'] = 'rsaPrivKeyFile';
            return $result;
        }

        /* get result data */
        $data = $this->getDataRsaSettingsIni($data);

        /* unset error messsage */
        unset($result['message']);

        /* set result and return it */
        $result['success'] = TRUE;
        $result['data'] = $data;
        return $result;
    }

    /* function to verify update of layout settings */
    public function verifyUpdateLayoutSettings(array $data, array $tokens): array {
        /* set fail result */
        $result = [
            'message' => 'Settings update failed',
            'success' => FALSE
        ];

        /* validate token */
        if (!$this->verifyTokens($tokens)) return $result;

        /* get layout data */
        $dataRes = $this->getDataLayoutSettingsIni($data);
        /* validate layout data */
        foreach ($dataRes as $nameLayout => $valueLayout) {
            /* validate name */
            if (!preg_match('/^[a-zA-Z\d_\-]+$/', $nameLayout)) {
                $result['message'] = 'Invalid layut name: ' . $nameLayout;
                $result['error'] = array_search($nameLayout, $data);
                return $result;
            }
            /* validate value */
            if (!preg_match('/^[a-zA-Z\d_\-.]+$/', $valueLayout)) {
                $result['message'] = 'Invalid layut value: ' . $valueLayout;
                $result['error'] = array_search($valueLayout, $data);
                return $result;
            }
        }

        /* unset errro message */
        unset($result['message']);

        /* set result and return it */
        $result['success'] = TRUE;
        $result['data'] = $dataRes;
        return $result;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function get result data of app settings */
    private function getDataAppSettingsIni(array $data): array {
        return [
        //             'urlServer' => $data['url-server'],
            'onlyHttps' => isset($data['onlyHttps']),
            'blockChangeIp' => isset($data['blockChangeIp']),
            'pageNotFound' => $data['pageNotFound'],
            'showMessageException' => isset($data['showMessageException']),
            'pageException' => $data['pageException'],
            'checkConnectTimeLoginSession' => isset($data['checkConnectTimeLoginSession']),
            'maxTimeUnconnectedLoginSession' => trim($data['maxTimeUnconnectedLoginSession'], ' ').' '.trim($data['unitMaxTimeUnconnectedLoginSession'], ' '),
            'maxWrongPassword' => $data['maxWrongPassword'],
            'passwordTryTime' => trim($data['passwordTryTime'], ' ').' '.trim($data['unitPasswordTryTime'], ' '),
            'userLockTime' => trim($data['userLockTime'], ' ').' '.trim($data['unitUserLockTime'], ' '),
            'maxLocks' => $data['maxLocks'],
            'minLengthName' => $data['minLengthName'],
            'maxLengthName' => $data['maxLengthName'],
            'minLengthUsername' => $data['minLengthUsername'],
            'maxLengthUsername' => $data['maxLengthUsername'],
            'minLengthPassword' => $data['minLengthPassword'],
            'checkMaxLengthPassword' => isset($data['checkMaxLengthPassword']),
            'maxLengthPassword' => $data['maxLengthPassword'],
            'requireHardPassword' => isset($data['requireHardPassword']),
            'passDefault' => $data['passDefault'],
            'useRegex' => isset($data['useRegex']),
            'regexName' => $data['regexName'],
            'regexUsername' => $data['regexUsername'],
            'regexPassword' => $data['regexPassword'],
            'useRegexEmail' => isset($data['useRegexEmail']),
            'regexEmail' => $data['regexEmail'],
            'sendEmailFrom' => $data['sendEmailFrom'],
            'requireConfirmEmail' => isset($data['requireConfirmEmail']),
            'emailValidationFrom' => $data['emailValidationFrom'],
            'useServerDomainEmailValidationLink' => isset($data['useServerDomainEmailValidationLink']),
            'urlDomainEmailValidationLink' => $data['urlDomainEmailValidationLink'],
            'emailResetPassFrom' => $data['emailResetPassFrom'],
            'useServerDomainResetPassLink' => isset($data['useServerDomainResetPassLink']),
            'urlDomainResetPasswordLink' => $data['urlDomainResetPasswordLink'],
            'expirationTimeResetPassword' => trim($data['expirationTimeResetPassword'], ' ').' '.trim($data['unitExpirationTimeResetPassword'], ' '),
            'addFakeUsersPage' => isset($data['addFakeUsersPage']),
            'usersForPageList' => $data['usersForPageList'],
            'linkPagination' => $data['linkPagination'],
            'dateFormat' => $data['dateFormat'],
            'datetimeFormat' => $data['datetimeFormat']
        ];
    }

    /* function to get result data of rsa settings */
    private function getDataRsaSettingsIni(array $data): array {
        return [
            'digestAlg' => $data['digestAlg'],
            'privateKeyBits' => $data['privateKeyBits'],
            'rsaKeyStatic' => isset($data['rsaKeyStatic']),
            'rsaPrivKeyFile' => $data['rsaPrivKeyFile']
        ];
    }

    /* function to get result data of layout settings */
    private function getDataLayoutSettingsIni(array $data): array {
        /* create layout name list */
        $nameList = [];
        foreach ($data as $key => $val) if (substr($key, 0, 12) === 'name-layout-') $nameList[substr($key, 12)] = $val;

        /* create result data with name and value of layout */
        $dataRes = [];
        foreach ($nameList as $key => $name) $dataRes[$name] = $data['val-layout-'.$key];

        /* return result */
        return $dataRes;
    }
}
