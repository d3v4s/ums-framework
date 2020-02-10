<?php
namespace app\controllers;

use \PDO;
use app\controllers\verifiers\AppSettingsVerifier;
use app\controllers\data\AppSettingsDataFactory;
use app\core\Router;

/**
 * Class controller to mange update and view of app settings
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AppSettingsController extends SettingsBaseController {
    protected $section;

    public function __construct(PDO $conn, array $appConfig, string $layout=SETTINGS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
        $this->lang = array_merge_recursive($this->lang, $this->getLanguageArray('settings'));
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view app settings */
    public function showAppSettings(string $section=DEFAULT_SETTING_SECTION) {
        /* redirect */
        $this->sendFailIfCanNotChangeSettings();

        $this->isSettings = TRUE;
        /* show message error if not valid app section */
        if (!in_array($section, $this->appSectionsList)) {
            $this->showMessage($this->lang[MESSAGE][INVALID_SETTING], TRUE);
            return;
        }

        /* get app section and generate data output */
        $this->section = $section;
        $data = AppSettingsDataFactory::getInstance()->getAppSettingsData($section, $this->appConfig);

        /* add js source */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/settings.js']
        );

        /* add other js sources */
        if ($section === LAYOUT) $this->jsSrcs[] = [SOURCE => '/js/utils/ums/layout-settings.js'];
        elseif ($section === RSA) $this->jsSrcs[] = [SOURCE => '/js/utils/ums/rsa-gen-save.js'];

        /* show settings page */
        $this->content = view(getPath('settings', $section), $data);
    }

    /* function to update settings */
    public function updateSettings(string $section) {
        /* redirect */
        $this->sendFailIfCanNotChangeSettings();

        /* require double login */
        $this->handlerDoubleLogin();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_SETTINGS);
        $data = $_POST;

        /* get instance of verifier and switch by section */
        $verifier = AppSettingsVerifier::getInstance($this->lang[MESSAGE]);
        
        switch ($section) {
            case APP:
                $resUpdate = $verifier->verifyAppSettingsUpdate($data, $tokens);
                break;
            case LAYOUT:
                $resUpdate = $verifier->verifyLayoutSettingsUpdate($data, $tokens);
                break;
            case RSA:
                $resUpdate = $verifier->verifyRsaSettingsUpdate($data, $tokens);
                break;
            case SECURITY:
                $resUpdate = $verifier->verifySecuritySettingsUpdate($data, $tokens);
                break;
            case UMS:
                $resUpdate = $verifier->verifyUmsSettingsUpdate($data, $tokens);
                break;
            default:
                $resUpdate =[
                    MESSAGE => $this->lang[MESSAGE][INVALID_SETTING],
                    SUCCESS => FALSE,
                    GENERATE_TOKEN => FALSE
                ];
                $section = DEFAULT_SETTING_SECTION;
                break;
        }

        /* if success */
        if ($resUpdate[SUCCESS]) {
            /* save settings */
            $res = $this->saveSettingsIni($resUpdate[DATA], $section);
            $resUpdate[MESSAGE] = $res[MESSAGE];
            $resUpdate[SUCCESS] = $res[SUCCESS];
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resUpdate[SUCCESS],
            MESSAGE => $resUpdate[MESSAGE] ?? NULL,
            ERROR => $resUpdate[ERROR] ?? NULL,
            SECTION => $section
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])){
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            $redirect = Router::getRoute('app\controllers\AppSettingsController', 'showAppSettings').'/'.$data[SECTION];
            redirect($redirect);
        };

        $this->switchResponse($dataOut, $resUpdate[GENERATE_TOKEN], $funcDefault, CSRF_SETTINGS);
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to save settings on ini file */
    private function saveSettingsIni(array $data, string $section): array {
        /* fail result */
        $res = [
            MESSAGE => $this->lang[MESSAGE][SAVE_SETTINGS][FAIL],
            SUCCESS => FALSE
        ];

        /* validate section */
        if (!in_array($section, $this->appSectionsList)) return $res;

        /* set update section config and save it */
        $appConfig = $this->appConfig;
        $appConfig[$section] = $data;
        if ($res[SUCCESS] = writeFileIni($appConfig, getPath(getcwd(), 'config', 'config.ini'))) $res[MESSAGE] = $this->lang[MESSAGE][SAVE_SETTINGS][SUCCESS];
        /* return result */
        return $res;
    }

    /* function to redirect if user can not update settings */
    private function sendFailIfCanNotChangeSettings() {
        if (!$this->canChangeSettings()) $this->switchFailResponse();
    }
}