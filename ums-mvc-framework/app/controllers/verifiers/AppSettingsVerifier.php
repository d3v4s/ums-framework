<?php
namespace app\controllers\verifiers;

class AppSettingsVerifier extends Verifier {
    private $unitsTimeList = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->unitsTimeList = getList('unitsTime');
    }

    public function setUnitTimeList(array $unitTimeList) {
        $this->unitsTimeList = $unitTimeList;
    }

    public function verifyUpdateAppSettings(array $data, array $tokens): array {
        $result = [
            'message' => 'Settings update failed',
            'success' => FALSE
        ];
        
        if (!$this->verifyTokens($tokens)) return $result;

        if (!$this->isValidInput($data['pageNotFound'], 1, 150, TRUE, '/^[a-zA-Z\d_\-.]+$/')) {
            $result['message'] = 'Invalid page not found';
            $result['error'] = 'pageNotFound';
            return $result;
        }
        if (!$this->isValidInput($data['pageException'], 1, 150, TRUE, '/^[a-zA-Z\d_\-.]+$/')) {
            $result['message'] = 'Invalid page exception';
            $result['error'] = 'pageException';
            return $result;
        }
        if (!$this->isValidNumber($data['maxTimeUnconnectedLoginSession'], -1, 9999)) {
            $result['message'] = 'Invalid value of max time unconneted loggedin session';
            $result['error'] = 'maxTimeUnconnectedLoginSession';
            return $result;
        }
        if (!in_array($data['unitMaxTimeUnconnectedLoginSession'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of max unconneted loggedin session';
            $result['error'] = 'unitMaxTimeUnconnectedLoginSession';
            return $result;
        }
        if (!$this->isValidNumber($data['maxWrongPassword'], 0, 999)) {
            $result['message'] = 'Invalid max wrong password';
            $result['error'] = 'maxWrongPassword';
            return $result;
        }
        if (!$this->isValidNumber($data['passwordTryTime'], 0, 9999)) {
            $result['message'] = 'Invalid value of time try password';
            $result['error'] = 'passwordTryTime';
            return $result;
        }
        if (!in_array($data['unitPasswordTryTime'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of password try';
            $result['error'] = 'unitPasswordTryTime';
            return $result;
        }
        if (!$this->isValidNumber($data['userLockTime'], 0, 9999)) {
            $result['message'] = 'Invalid value of user lock time';
            $result['error'] = 'userLockTime';
            return $result;
        }
        if (!in_array($data['unitUserLockTime'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of user lock';
            $result['error'] = 'unitUserLockTime';
            return $result;
        }
        if (!$this->isValidNumber($data['maxLocks'], 0, 9999)) {
            $result['message'] = 'Invalid value of max locks';
            $result['error'] = 'maxLocks';
            return $result;
        }
        
        if (!$this->isValidNumber($data['minLengthName'], 1, 255)) {
            $result['message'] = 'Invalid min lenght name';
            $result['error'] = 'minLengthName';
            return $result;
        }
        if (!$this->isValidNumber($data['maxLengthName'], 1, 255)) {
            $result['message'] = 'Invalid max lenght name';
            $result['error'] = 'maxLengthName';
            return $result;
        }
        if (!$this->isValidNumber($data['minLengthUsername'], 1, 255)) {
            $result['message'] = 'Invalid min lenght username';
            $result['error'] = 'minLengthUsername';
            return $result;
        }
        if (!$this->isValidNumber($data['maxLengthUsername'], 1, 255)) {
            $result['message'] = 'Invalid max lenght username';
            $result['error'] = 'maxLengthUsername';
            return $result;
        }
        if (!$this->isValidNumber($data['minLengthPassword'], 1, 255)) {
            $result['message'] = 'Invalid min lenght password';
            $result['error'] = 'minLengthPassword';
            return $result;
        }
        if (!$this->isValidNumber($data['maxLengthPassword'], 1, 255)) {
            $result['message'] = 'Invalid max lenght password';
            $result['error'] = 'maxLengthPassword';
            return $result;
        }
        if (!$this->isValidEmail($data['sendEmailFrom'], FALSE)) {
            $result['message'] = 'Invalid send email from';
            $result['error'] = 'sendEmailFrom';
            return $result;
        }
        if (!$this->isValidEmail($data['emailValidationFrom'], FALSE)) {
            $result['message'] = 'Invalid email vaildation from';
            $result['error'] = 'emailValidationFrom';
            return $result;
        }
        if (!$this->isValidDomain($data['urlDomainEmailValidationLink'])) {
            $result['message'] = 'Inavlid URL domain for email vaildation link';
            $result['error'] = 'urlDomainEmailValidationLink';
            return $result;
        }
        if (!$this->isValidEmail($data['emailResetPassFrom'], FALSE)) {
            $result['message'] = 'Invalid email reset password from';
            $result['error'] = 'emailResetPassFrom';
            return $result;
        }
        if (!$this->isValidDomain($data['urlDomainResetPasswordLink'])) {
            $result['message'] = 'Inavlid URL domain for email vaildation link';
            $result['error'] = 'urlDomainResetPasswordLink';
            return $result;
        }
        if (!$this->isValidNumber($data['expirationTimeResetPassword'], -1, 9999)) {
            $result['message'] = 'Invalid value of reset password link expiration time';
            $result['error'] = 'expirationTimeResetPassword';
            return $result;
        }
        if (!in_array($data['unitExpirationTimeResetPassword'], $this->unitsTimeList)) {
            $result['message'] = 'Invalid unit time of reset password link expiration';
            $result['error'] = 'unitExpirationTimeResetPassword';
            return $result;
        }
        if (!preg_match('/^[\d,]+$/', $data['usersForPageList'])) {
            $result['message'] = 'Invalid users for page list';
            $result['error'] = 'usersForPageList';
            return $result;
        }
        if (!$this->isValidNumber($data['linkPagination'], 1, 30)) {
            $result['message'] = 'Invalid n. link pagination';
            $result['error'] = 'linkPagination';
            return $result;
        }
        $regexDateFormat = '/^[dDjlLNSwzFMmntLoyYcruaABgGhHisuv\-\\/_: ]+$/';
        if (!preg_match($regexDateFormat, $data['dateFormat'])) {
            $result['message'] = 'Invalid date format';
            $result['error'] = 'dateFormat';
            return $result;
        }
        if (!preg_match($regexDateFormat, $data['datetimeFormat'])) {
            $result['message'] = 'Invalid datetime format';
            $result['error'] = 'datetimeFormat';
            return $result;
        }
        $data = $this->getDataAppSettingsIni($data);
        
        unset($result['message']);
        $result['success'] = TRUE;
        $result['data'] = $data;
        return $result;
    }

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

    public function verifyUpdateRsaSettings(array $data, array $tokens): array {
        $result = [
            'message' => 'Settings update failed',
            'success' => FALSE
        ];
        
        if (!$this->verifyTokens($tokens)) return $result;

        if (!$this->isValidNumber($data['privateKeyBits'], 1, 1024000)) {
            $result['message'] = 'Invalid private key bits';
            $result['error'] = 'privateKeyBits';
            return $result;
        }
        if (strpos($data['rsaPrivKeyFile'], '/') !== FALSE) {
            $result['message'] = 'Invalid private key filename';
            $result['error'] = 'rsaPrivKeyFile';
            return $result;
        }
        $data = $this->getDataRsaSettingsIni($data);
        
        unset($result['message']);
        $result['success'] = TRUE;
        $result['data'] = $data;
        return $result;
    }

    private function getDataRsaSettingsIni(array $data): array {
        return [
            'digestAlg' => $data['digestAlg'],
            'privateKeyBits' => $data['privateKeyBits'],
            'rsaKeyStatic' => isset($data['rsaKeyStatic']),
            'rsaPrivKeyFile' => $data['rsaPrivKeyFile']
        ];
    }

    public function verifyUpdateLayoutSettings(array $data, array $tokens): array {
        $result = [
            'message' => 'Settings update failed',
            'success' => FALSE
        ];
        
        if (!$this->verifyTokens($tokens)) return $result;

        $dataRes = $this->getDataLayoutSettingsIni($data);
        foreach ($dataRes as $nameLayout => $valueLayout) {
            if (!preg_match('/^[a-zA-Z\d_\-]+$/', $nameLayout)) {
                $result['message'] = 'Invalid layut name: ' . $nameLayout;
                $result['error'] = array_search($nameLayout, $data);
                return $result;
            }
            if (!preg_match('/^[a-zA-Z\d_\-.]+$/', $valueLayout)) {
                $result['message'] = 'Invalid layut value: ' . $valueLayout;
                $result['error'] = array_search($valueLayout, $data);
                return $result;
            }
        }
        
        unset($result['message']);
        $result['success'] = TRUE;
        $result['data'] = $dataRes;
        return $result;
    }

    private function getDataLayoutSettingsIni(array $data): array {
        $nameList = [];
        foreach ($data as $key => $val) if (substr($key, 0, 12) === 'name-layout-') $nameList[substr($key, 12)] = $val;
        
        $dataRes = [];
        foreach ($nameList as $key => $name) $dataRes[$name] = $data['val-layout-'.$key];
        
        return $dataRes;
    }
}
