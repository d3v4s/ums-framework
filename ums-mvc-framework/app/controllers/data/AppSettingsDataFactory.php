<?php
namespace app\controllers\data;

/**
 * Class data factory,
 * used for generate and manage the data of response of app settings
 * @author Andrea Serra (DevAS) https://devas.info
 * 
 */
class AppSettingsDataFactory extends DataFactory {
    private $appConfig;

    protected function __construct(array $appConfig) {
        parent::__construct();
        $this->appConfig = $appConfig;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

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
        return;
    }

    /* function to mangae the data of layout settings */
    private function handlerLayoutSettingsData(array &$data) {
        $data = [
            LAYOUT => $data
        ];
    }

    /* function to mangae the data of rsa settings */
    private function handlerDataRsaSettings(array &$data) {
        /* get path of privete key directory */
        $data[PATH_PRIV_KEY] = getPath(getcwd(), 'config', 'rsa');
        /* generate token for keys generator */
        $data[TOKEN_RSA] = generateToken(CSRF_GEN_SAVE_RSA);
    }

    /* function to mangae the data of security settings */
    private function handlerSecuritySettingsData(array &$data) {
        /* set html checked attribute for check buttons */
        $data[NO_ESCAPE.ONLY_HTTPS] = $data[ONLY_HTTPS] ? 'checked="checked"' : '';
        $data[NO_ESCAPE.BLOCK_CHANGE_IP] = $data[BLOCK_CHANGE_IP] ? 'checked="checked"' : '';
    }

    /* function to mangae the data of UMS settings */
    private function handlerUmsSettingsData(array &$data) {
        /* set html checked attribute for check buttons */
        $data[NO_ESCAPE.REQUIRE_CONFIRM_EMAIL] = $data[REQUIRE_CONFIRM_EMAIL] ? 'checked="checked"' : '';
    }
}
