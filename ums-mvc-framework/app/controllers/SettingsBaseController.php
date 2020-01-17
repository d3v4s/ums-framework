<?php
namespace app\controllers;

use \PDO;

/**
 * Class controller to mange update and view of app settings
 * @author Andrea Serra (DevAS) https://devas.info
 */
class SettingsBaseController extends UMSBaseController {
    protected $section;
    protected $appSectionsList = [];

    public function __construct(PDO $conn, array $appConfig, string $layout=SETTINGS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
        $this->appSectionsList =  array_keys($this->appConfig);
    }
}
