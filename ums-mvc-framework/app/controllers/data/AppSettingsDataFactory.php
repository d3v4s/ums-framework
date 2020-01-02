<?php
namespace app\controllers\data;

/**
 * Class data factory,
 * used for generate and manage the data of response of app settings
 * @author Andrea Serra (DevAS) https://devas.info
 * 
 */
class AppSettingsDataFactory extends DataFactory {
    private $unitsTimeList = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->unitsTimeList = getList('unitsTime');
    }

    public function setUnitTimeList(array $unitsTimeList) {
        $this->unitsTimeList = $unitsTimeList;
    }

    public function getAppSettingsData(string $section): array {
        $data = $this->appConfig[$section];
        switch ($section) {
            case 'app':
                $this->manageDataAppSettings($data);
                break;
            case 'rsa':
                $this->menageDataRsaSettings($data);
                break;
            case 'layout':
                $this->menageDataLayoutSettings($data);
                break;
        }
        $data['token'] = generateToken('csrfSettings');
        return $data;
    }

    private function manageDataAppSettings(array &$data) {
        $data['urlServer'] = getUrlServer();
        $data['_checkedOnlyHttps'] = $data['onlyHttps'] ? 'checked="checked"' : '';
        $data['_checkedBlockChangeIp'] = $data['blockChangeIp'] ? 'checked="checked"' : '';
        $data['_checkedShowMessageException'] = $data['showMessageException'] ? 'checked="checked"' : '';
        $data['_checkedConnectTimeLoginSession'] = $data['checkConnectTimeLoginSession'] ? 'checked="checked"' : '';
        $timeSplit = explode(' ', $data['maxTimeUnconnectedLoginSession']);
        $data['maxTimeUnconnectedLoginSession'] = $timeSplit[0];
        $data['unitMaxTimeUnconnectedLoginSession'] = $timeSplit[1];
        $timeSplit = explode(' ', $data['passwordTryTime']);
        $data['passwordTryTime'] = $timeSplit[0];
        $data['untiPasswordTryTime'] = $timeSplit[1];
        $timeSplit = explode(' ', $data['userLockTime']);
        $data['userLockTime'] = $timeSplit[0];
        $data['unitUserLockTime'] = $timeSplit[1];
        $data['_checkedMaxLenghtPassword'] = $data['checkMaxLengthPassword'] ? 'checked="checked"' : '';
        $data['_checkedRequireHardPassword'] = $data['requireHardPassword'] ? 'checked="checked"' : '';
        $data['_checkedUseRegex'] = $data['useRegex'] ? 'checked="checked"' : '';
        $data['_checkedUseRegexEmail'] = $data['useRegexEmail'] ? 'checked="checked"' : '';
        $data['_checkedRequireConfirmEmail'] = $data['requireConfirmEmail'] ? 'checked="checked"' : '';
        $data['_checkedUseServerDomainEmailValidationLink'] = $data['useServerDomainEmailValidationLink'] ? 'checked="checked"' : '';
        $data['_checkedUseServerDomainResetPassLink'] = $data['useServerDomainResetPassLink'] ? 'checked="checked"' : '';
        $timeSplit = explode(' ', $data['expirationTimeResetPassword']);
        $data['expirationTimeResetPassword'] = $timeSplit[0];
        $data['unitExpirationTimeResetPassword'] = $timeSplit[1];
        $data['_checkedAddFakeUsersPage'] = $data['addFakeUsersPage'] ? 'checked="checked"' : '';
        $data['unitsTimeList'] = $this->unitsTimeList;
    }
    
    private function menageDataRsaSettings(array &$data) {
        $data['_checkedRsaKeyStatic'] = $data['rsaKeyStatic'] ? 'checked="checked"' : '';
        $data['pathPrivKey'] = getcwd() . '/config/rsa/';
        $data['tokenGenSave'] = generateToken('csrfGenSave');
    }
    
    private function menageDataLayoutSettings(array &$data) {
        $data = [
            'layout' => $data
        ];
    }
}

