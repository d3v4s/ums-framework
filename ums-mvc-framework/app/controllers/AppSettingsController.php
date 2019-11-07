<?php
namespace app\controllers;

use \PDO;
use app\controllers\verifiers\AppSettingsVerifier;
use app\controllers\data\AppSettingsDataFactory;

class AppSettingsController extends Controller {
    protected $section;
    protected $appSectionsList = [];

    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
        $this->appSectionsList = getList('appSections');
    }

    public function showAppSettings(string $section = 'app') {
        $this->redirectIfCanNotChangeSettings();

        $this->isSettings = TRUE;
        if (!in_array($section, $this->appSectionsList)) {
            $this->showMessage('INVALID SETTINGS SECTION');
            return;
        }
        $this->section = $section;
        $data = AppSettingsDataFactory::getInstance($this->appConfig)->getAppSettingsData($section);
        array_push($this->jsSrcs,
            ['src' => "/js/utils/ums/adm-sttng-$section.js"]
        );

        $this->content = view("settings/admin-settings-$section", $data);
    }

    public function updateSettings(string $section) {
        $this->redirectIfCanNotChangeSettings();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfSettings');
        $data = $_POST;

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

        if ($resUpdate['success']) {
            $res = $this->saveSettingsIni($resUpdate['data'], $section);
            $resUpdate['message'] = $res['message'];
            $resUpdate['success'] = $res['success'];
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resUpdate['success'],
                    'message' => $resUpdate['message'] ?? NULL,
                    'error' => $resUpdate['error'] ?? NULL
                ];
                $resJSON['ntk'] = generateToken('csrfSettings');
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resUpdate['message'])){
                    $_SESSION['message'] = $resUpdate['message'];
                    $_SESSION['success'] = $resUpdate['success'];
                }
                redirect('/ums/app/settings/'.$section);
                break;
        }
    }

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

    private function redirectIfCanNotChangeSettings() {
        if (!userCanChangeSettings()) redirect();
    }
}