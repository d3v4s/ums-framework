<?php
namespace app\controllers;

use \PDO;
use app\controllers\data\UMSDataFactory;

/**
 * Class base controller for ums
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSBaseController extends Controller {
    protected $isNewUser = FALSE;
    protected $isNewEmail = FALSE;
    protected $isSettings = FALSE;
    protected $isUsersList = FALSE;

    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## SHOW FUNCTIONS ########## */

    /* function to show the ums home */
    public function showUmsHome() {
        $this->sendFailIfSimpleUser();
        $this->isUmsHome = TRUE;
        $data = UMSDataFactory::getInstance($this->lang[DATA], $this->conn)->getHomeData();
        $this->content = view(getPath('ums','home'), $data);
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */
    
    /* function to check if user can view role */
    protected function canViewRole() {
        return $this->isAdminUser();
    }

    /* function to redirect if user can not send email */
    protected function sendFailIfCanNotSendEmail() {
        if (!$this->canSendEmails()) $this->switchFailResponse();
    }
}
