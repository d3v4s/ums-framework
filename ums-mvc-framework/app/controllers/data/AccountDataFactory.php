<?php
namespace app\controllers\data;

use app\models\Role;
use app\models\PendingEmail;
use \PDO;

/**
 * Class data factory, to manage response data of user request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AccountDataFactory extends DataFactory {

    protected function __construct(PDO $conn) {
        parent::__construct($conn);
    }

    /* function to get user data */
    public function getUserData($userId): array {
        $roleModel = new Role($this->conn);
        $pendMailModel = new PendingEmail($this->conn);
        return [
            TOKEN_DELETE => generateToken(CSRF_DELETE_ACCOUNT),
            TOKEN_UPDATE => generateToken(CSRF_UPDATE_ACCOUNT),
            TOKEN_RESEND_ENABLER_EMAIL => generateToken(CSRF_RESEND_ENABLER_EMAIL),
            TOKEN_DELETE_NEW_EMAIL => generateToken(CSRF_DELETE_NEW_EMAIL),
            ROLES => $roleModel->getNameAndIdRoles(),
            WAIT_EMAIL_CONFIRM => $pendMailModel->getPendingEmailByUserId($userId)
        ];
    }
}

