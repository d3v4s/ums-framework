<?php
namespace app\controllers\data;

/**
 * Class data factory,
 * used for generate and manage the data of response of app settings
 * @author Andrea Serra (DevAS) https://devas.info
 * 
 */
class AppSettingsDataFactory extends DataFactory {

    protected function __construct() {
        parent::__construct();
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to get data by section */
    public function getAppSettingsData(string $section, array $appSettings): array {
        $data = $appSettings[$section];
        switch ($section) {
            case APP:
                $this->handlerAppSettingsData($data);
                break;
            case LAYOUT:
                $this->handlerLayoutSettingsData($data);
                break;
            case RSA:
                $this->handlerRsaSettingsData($data);
                break;
            case SECURITY:
                $this->handlerSecuritySettingsData($data);
                break;
            case UMS:
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
    private function handlerRsaSettingsData(array &$data) {
        /* get path of privete key directory */
        $data[PATH_PRIV_KEY] = getPath(getcwd(), 'config', 'rsa');
        /* generate token for keys generator */
        $data[RSA_TOKEN] = generateToken(CSRF_GEN_SAVE_RSA);
    }

    /* function to mangae the data of security settings */
    private function handlerSecuritySettingsData(array &$data) {
        /* set html checked attribute for check buttons */
        $data[NO_ESCAPE.ONLY_HTTPS] = $data[ONLY_HTTPS] ? CHECKED : '';
        $data[NO_ESCAPE.BLOCK_CHANGE_IP] = $data[BLOCK_CHANGE_IP] ? CHECKED : '';
    }

    /* function to mangae the data of UMS settings */
    private function handlerUmsSettingsData(array &$data) {
        /* set html checked attribute for check buttons */
        $data[NO_ESCAPE.REQUIRE_CONFIRM_EMAIL] = $data[REQUIRE_CONFIRM_EMAIL] ? CHECKED : '';
    }
}
