<?php
namespace app\controllers\data;

/**
 * Class data factory,
 * used for generate and manage the data of response of app settings
 * @author Andrea Serra (DevAS) https://devas.info
 * 
 */
class AppSettingsDataFactory extends DataFactory {
    private $timeUnitList = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->timeUnitList = getList(TIME_UNIT_LIST);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to set the list of unit times */
    public function setUnitTimeList(array $unitsTimeList) {
        $this->timeUnitList = $unitsTimeList;
    }

    /* function to get data by section */
    public function getAppSettingsData(string $section): array {
        $data = $this->appConfig[$section];
        switch ($section) {
            case 'app':
                $this->handlerAppSettingsData($data);
                break;
            case 'layout':
                $this->handlerLayoutSettingsData($data);
                break;
            case 'rsa':
                $this->handlerDataRsaSettings($data);
                break;
            case 'security':
                $this->handlerDataSecutiySettings($data);
                break;
            case 'ums':
                $this->handlerUmsSettingsData($data);
                break;
        }
        $data[TOKEN] = generateToken(CSRF_SETTINGS);
        return $data;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to mangae the data of app settings */
    private function handlerAppSettingsData(array &$data) {
        /* get url of server */
        $data[URL_SERVER] = getServerUrl();
        /* set html checked attribute for check buttons */
        $data[NO_ESCAPE.SHOW_MESSAGE_EXCEPTION] = $data[SHOW_MESSAGE_EXCEPTION] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.CHECK_MAX_LENGTH_PASS] = $data[CHECK_MAX_LENGTH_PASS] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.REQUIRE_HARD_PASS] = $data[REQUIRE_HARD_PASS] ? 'checked="checked"' : '';
        $data[TIME_UNIT_LIST] = $this->timeUnitList;
//         $data['_checkedAddFakeUsersPage'] = $data['addFakeUsersPage'] ? 'checked="checked"' : '';
    }

    /* function to mangae the data of layout settings */
    private function handlerLayoutSettingsData(array &$data) {
        $data = [
            LAYOUT => $data
        ];
    }

    /* function to mangae the data of rsa settings */
    private function handlerDataRsaSettings(array &$data) {
//         $data['_checkedRsaKeyStatic'] = $data['rsaKeyStatic'] ? 'checked="checked"' : '';
        /* get path of privete key directory */
        $data[PATH_PRIV_KEY] = getPath(getcwd(), 'config', 'rsa');
        /* generate token for keys generator */
        $data[CSRF_GEN_SAVE_RSA] = generateToken(CSRF_GEN_SAVE_RSA);
    }

    /* function to mangae the data of security settings */
    private function handlerSecuritySettingsData(array &$data) {
        /* set html checked attribute for check buttons */
        $data[NO_ESCAPE.ONLY_HTTPS] = $data[ONLY_HTTPS] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.BLOCK_CHANGE_IP] = $data[BLOCK_CHANGE_IP] ? 'checked="checked"' : '';
        /* split time and unit */
        $timeSplit = explode(' ', $data[MAX_TIME_UNCONNECTED_LOGIN_SESSION]);
        $data[MAX_TIME_UNCONNECTED_LOGIN_SESSION] = $timeSplit[0];
        $data[TIME_UNIT.MAX_TIME_UNCONNECTED_LOGIN_SESSION] = $timeSplit[1];
        /* split time and unit */
        $timeSplit = explode(' ', $data[PASS_TRY_TIME]);
        $data[PASS_TRY_TIME] = $timeSplit[0];
        $data[TIME_UNIT.PASS_TRY_TIME] = $timeSplit[1];
        /* split time and unit */
        $timeSplit = explode(' ', $data[USER_LOCK_TIME]);
        $data[USER_LOCK_TIME] = $timeSplit[0];
        $data[USER_LOCK_TIME] = $timeSplit[1];
    }

    /* function to mangae the data of UMS settings */
    private function handlerUmsSettingsData(array &$data) {
        /* set html checked attribute for check buttons */
        $data[NO_ESCAPE.SHOW_MESSAGE_EXCEPTION] = $data[SHOW_MESSAGE_EXCEPTION] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.CHECK_MAX_LENGTH_PASS] = $data[CHECK_MAX_LENGTH_PASS] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.REQUIRE_HARD_PASS] = $data[REQUIRE_HARD_PASS] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.USE_REGEX] = $data[USE_REGEX] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.USE_REGEX_EMAIL] = $data[USE_REGEX_EMAIL] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.REQUIRE_CONFIRM_EMAIL] = $data[REQUIRE_CONFIRM_EMAIL] ? 'checked="checked"' : '';
        /* split time and unit */
        $timeSplit = explode(' ', $data[PASS_RESET_EXPIRE_TIME]);
        $data[PASS_RESET_EXPIRE_TIME] = $timeSplit[0];
        $data[TIME_UNIT.PASS_RESET_EXPIRE_TIME] = $timeSplit[1];
    }
}
