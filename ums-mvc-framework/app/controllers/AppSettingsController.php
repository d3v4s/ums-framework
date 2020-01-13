<?php
namespace app\controllers;

use \PDO;
use app\controllers\verifiers\AppSettingsVerifier;
use app\controllers\data\AppSettingsDataFactory;

/**
 * Class controller to mange update and view of app settings
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AppSettingsController extends Controller {
    protected $section;
    protected $appSectionsList = [];

    public function __construct(PDO $conn, array $appConfig, string $layout=SETTINGS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
        $this->appSectionsList =  array_keys($this->appConfig);  // getList('appSections');
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view app settings */
    public function showAppSettings(string $section=DEFAULT_SETTING_SECTION) {
        /* redirect */
        $this->redirectOrFailIfCanNotChangeSettings();

        $this->isSettings = TRUE;
        /* show message error if not valid app section */
        if (!in_array($section, $this->appSectionsList)) {
            $this->showMessage('Invalid settings section', TRUE);
            return;
        }

        /* get app section and generate data output */
        $this->section = $section;
        $data = AppSettingsDataFactory::getInstance($this->appConfig)->getAppSettingsData($section);

        /* add js source */
        array_push($this->jsSrcs,
            [SOURCE => "/js/utils/ums/adm-sttng-$section.js"]
        );

        /* show settings page */
        $this->content = view(getPath('settings', $section), $data);
    }

    /* function to update settings */
    public function updateSettings(string $section) {
        /* redirect */
        $this->redirectOrFailIfCanNotChangeSettings();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_SETTINGS);
        $data = $_POST;

        /* get instance of verifier and switch by section */
        $verifier = AppSettingsVerifier::getInstance($this->appConfig);
        
        switch ($section) {
            case 'app':
                $resUpdate = $verifier->verifyAppSettingsUpdate($data, $tokens);
                break;
            case 'layout':
                $resUpdate = $verifier->verifyLayoutSettingsUpdate($data, $tokens);
                break;
            case 'rsa':
                $resUpdate = $verifier->verifyRsaSettingsUpdate($data, $tokens);
                break;
            case 'security':
                $resUpdate = $verifier->verifySecuritySettingsUpdate($data, $tokens);
                break;
            case 'ums':
                $resUpdate = $verifier->verifyUmsSettingsUpdate($data, $tokens);
                break;
            default:
                $resUpdate =[
                    MESSAGE => 'Invalid settings section',
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
            redirect('/'.APP_SETTINGS_ROUTE.'/'.$data[SECTION]);
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
            MESSAGE => 'Save settings failed',
            SUCCESS => FALSE
        ];

        /* validate section */
        if (!in_array($section, $this->appSectionsList)) return $res;

        /* set update section config and save it */
        $appConfig = $this->appConfig;
        $appConfig[$section] = $data;
        if ($res[SUCCESS] = writeFileIni($appConfig, getPath(getcwd(), 'config', 'config.ini'))) $res[MESSAGE] = 'Settings succesfully saved';
        /* return result */
        return $res;
    }

    /* function to redirect if user can not update settings */
    private function redirectOrFailIfCanNotChangeSettings() {
        if (!$this->userRole[CAN_CHANGE_SETTINGS]) $this->switchFailResponse();
    }
}