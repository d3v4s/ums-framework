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

    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
        $this->appSectionsList = getList('appSections');
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view app settings */
    public function showAppSettings(string $section = 'app') {
        /* redirect */
        $this->redirectIfCanNotChangeSettings();

        $this->isSettings = TRUE;
        /* show message error if not valid app section */
        if (!in_array($section, $this->appSectionsList)) {
            $this->showMessage('INVALID SETTINGS SECTION');
            return;
        }

        /* get app section and generate data output */
        $this->section = $section;
        $data = AppSettingsDataFactory::getInstance($this->appConfig)->getAppSettingsData($section);

        /* add js source */
        array_push($this->jsSrcs,
            ['src' => "/js/utils/ums/adm-sttng-$section.js"]
        );

        /* show settings page */
        $this->content = view("settings/admin-settings-$section", $data);
    }

    /* function to update settings */
    public function updateSettings(string $section) {
        /* redirect */
        $this->redirectIfCanNotChangeSettings();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfSettings');
        $data = $_POST;

        /* get instance of verifier and switch by section */
        $verifier = AppSettingsVerifier::getInstance($this->appConfig);
        switch ($section) {
            case 'app':
                $resUpdate = $verifier->verifyUpdateAppSettings($data, $tokens);
                break;
            case 'rsa':
                $resUpdate = $verifier->verifyUpdateRsaSettings($data, $tokens);
                break;
            case 'layout':
                $resUpdate = $verifier->verifyUpdateLayoutSettings($data, $tokens);
                break;
            default:
                $resUpdate['message'] = 'Invalid settings section';
                $resUpdate['success'] = FALSE;
                $section = 'app';
                break;
        }

        /* if success */
        if ($resUpdate['success']) {
            /* save settings */
            $res = $this->saveSettingsIni($resUpdate['data'], $section);
            $resUpdate['message'] = $res['message'];
            $resUpdate['success'] = $res['success'];
        }

        /* result data */
        $dataOut = [
            'success' => $resUpdate['success'],
            'message' => $resUpdate['message'] ?? NULL,
            'error' => $resUpdate['error'] ?? NULL,
            'section' => $section
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])){
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/ums/app/settings/'.$data['section']);
        };

        $this->switchResponse($dataOut, TRUE, $funcDefault, 'csrfSettings');
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = [
//                     'success' => $resUpdate['success'],
//                     'message' => $resUpdate['message'] ?? NULL,
//                     'error' => $resUpdate['error'] ?? NULL
//                 ];
//                 $resJSON['ntk'] = generateToken('csrfSettings');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 break;
//         }
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to save settings on ini file */
    private function saveSettingsIni(array $data, string $section): array {
        $res = [
            'message' => 'Save settings failed',
            'success' => FALSE
        ];
        if (!in_array($section, $this->appSectionsList)) return $res;
        $appConfig = $this->appConfig;
        $appConfig[$section] = $data;
        if ($res['success'] = writeFileIni($appConfig, getcwd().'/config/config.ini')) $res['message'] = 'Settings succesfully saved';
        return $res;
    }

    /* function to redirect if user can not update settings */
    private function redirectIfCanNotChangeSettings() {
        if (!userCanChangeSettings()) redirect();
    }
}