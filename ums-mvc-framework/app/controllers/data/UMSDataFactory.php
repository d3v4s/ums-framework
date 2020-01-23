<?php

namespace app\controllers\data;

use app\models\PendingEmail;
use app\models\DeletedUser;
use app\models\PendingUser;
use app\models\Session;
use app\models\User;
use app\models\Role;
use \PDO;

/**
 * Class data factory, used for generate
 * and manage the data of response of user
 * management system
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSDataFactory extends DataFactory {

    protected function __construct(PDO $conn=NULL) {
        parent::__construct($conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    public function getHomeData() {
        /* init user model and count users */
        $userModel = new User($this->conn);
        $totUsers = $userModel->countAllUsers();
        $enabUsers = $userModel->countEnabledUsers();

        /* init deleted user model and count deleted users */
        $delUserModel = new DeletedUser($this->conn);
        $totDeleted = $delUserModel->countDeletedUsers();

        /* init pending user model and count pending users */
        $pendingUserModel = new PendingUser($this->conn);
        $totPendUsers = $pendingUserModel->countAllPendingUsers();
        $pendUsers = $pendingUserModel->countValidPendingUsers();

        /* init pending mails modele and count all pending mails */
        $pendMailModel = new PendingEmail($this->conn);
        $totPendMails = $pendMailModel->countAllPendingEmails();
        $pendMails = $pendMailModel->countValidPendingEmails();

        /* init session model and count sessions */
        $sessionModel = new Session($this->conn);
        $totSessions = $sessionModel->countAllSessions();
        $validSessions = $sessionModel->countValidSessions();

        /* return data */
        return [
            ENABLED_USERS => $enabUsers,
            TOT_USERS => $totUsers,
            TOT_DELETED_USERS => $totDeleted,
            PENDING_USERS => $pendUsers,
            TOT_PENDING_USERS => $totPendUsers,
            TOT_PENDING_MAILS => $totPendMails,
            PENDING_EMAILS => $pendMails,
            TOT_SESSIONS => $totSessions,
            VALID_SESSIONS => $validSessions
        ];
    }

    /* function to get data for update user */
    public function getUpdateUserData($username): array {
        /* init user model and get user */
        $user = new User($this->conn);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);

        /* init role model */
        $role = new Role($this->conn);
        /* return data */
        return [
            USER => $usr,
            ROLES => $role->getNameAndIdRoles(),
            TOKEN => generateToken(CSRF_UPDATE_USER),
            NO_ESCAPE.ENABLED => ($usr->enabled) ? CHECKED : ''
        ];
    }

    /* function to get data for new user */
    public function getNewUserData(): array {
        /* init role model */
        $role = new Role($this->conn);
        /* return data */
        return [
            ROLES => $role->getNameAndIdRoles(),
            TOKEN => generateToken(CSRF_NEW_USER),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON)
        ];
    }
}

