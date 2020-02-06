<?php
namespace app\controllers\data;

use app\models\Role;
use app\models\PendingEmail;
use \PDO;
use app\models\User;
use app\models\Session;

/**
 * Class data factory, to manage response data of user request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AccountDataFactory extends DataFactory {

    protected function __construct(array $langData,PDO $conn=NULL) {
        parent::__construct($langData, $conn);
    }

    /* function to get account delete data */
    public function getDeleteAccountData() {
        return [
            TOKEN => generateToken(CSRF_DELETE_ACCOUNT),
            LANG => $this->langData
        ];
    }

    /* function to get account settings data */
    public function getAccountSettingsData($userId): array {
        $userModel = new User($this->conn);
        $roleModel = new Role($this->conn);
        $pendMailModel = new PendingEmail($this->conn);
        return [
            RESEND_ENABLER_EMAIL_TOKEN => generateToken(CSRF_RESEND_ENABLER_EMAIL),
            DELETE_NEW_EMAIL_TOKEN => generateToken(CSRF_DELETE_NEW_EMAIL),
            UPDATE_TOKEN => generateToken(CSRF_UPDATE_ACCOUNT),
            WAIT_EMAIL_CONFIRM => $pendMailModel->getValidPendingEmailByUserId($userId),
            ROLES => $roleModel->getNameAndIdRoles(),
            USER => $userModel->getUser($userId),
            LANG => $this->langData
        ];
    }

    /* function to get account info data */
    public function getAccountInfoData($userId): array {
        /* init user model and get user with role */
        $userModel = new User($this->conn);
        $user = $userModel->getUserAndRole($userId);
        return [
            VIEW_ROLE => !isSimpleUser($user->{ROLE_ID_FRGN}),
            LANG => $this->langData,
            USER => $user
        ];
    }

    /* function to get sessions of user */
    public function getSessionsData($userId, $currentSessId): array {
        /* init session model */
        $sessionModel = new Session($this->conn);
        return [
            SESSIONS => $sessionModel->getValidSessionsByUserId($userId),
            TOKEN => generateToken(CSRF_INVALIDATE_SESSION),
            CURRENT_SESSION => $currentSessId,
            LANG => $this->langData
        ];
    }

    /* function to get change password data */
    public function getChangePasswordData() {
        return [
            TOKEN => generateToken(CSRF_CHANGE_PASS),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON),
            LANG => $this->lang[DATA]
        ];
    }
}
