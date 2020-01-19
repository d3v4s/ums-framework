<?php

namespace app\controllers\data;

use app\models\User;
use app\models\Role;
use \DateTime;
use \PDO;
use app\models\PendingEmail;
use app\models\PendingUser;
use app\models\Session;
use app\models\DeletedUser;

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
        $pendUsers = $pendingUserModel->countPendingUsers();

        /* init pending mails modele and count all pending mails */
        $pendMailModel = new PendingEmail($this->conn);
        $totPendMails = $pendMailModel->countAllPendingEmails();
        $pendMails = $pendMailModel->countPendingEmails();

        /* init session model and count sessions */
        $sessionModel = new Session($this->conn);
        $totSessions = $sessionModel->countAllSessions();
        $validSessions = $sessionModel->countActiveSessions();

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

    /* function to get data of user */
    public function getUserData(string $username, string $datetimeFormat, bool $canUpdateUser, bool $canDeleteUser, bool $canChangePassword, bool $canViewRole, bool $canSendEmail): array {
        /* init user model and get user */
        $userModel = new User($this->conn);
        /* if is numeric get user by id */
        if (is_numeric($username)) $user = $userModel->getUserAndRole($username);
        /* else get user by username */
        else $user = $userModel->getUserAndRoleByUsername($username);
        
        if ($user) {
            /* enable account class */
            $classEnabledAccount = 'text-';
            /* if account is enable */
            if ($user->{ENABLED}) {
                $classEnabledAccount .= 'success';
                $messageEnable = 'ENABLED';
                /* if account is disabled */
            } else {
                $classEnabledAccount .= 'danger';
                $messageEnable = 'DISABLED';
            }
            
            /* init message */
            $messageLockUser = '';
            /* if user has a lock, then set message and format the date */
            if (($isLock = (isset($user->{EXPIRE_LOCK}) && new DateTime($user->{EXPIRE_LOCK}) > new DateTime()))) {
                $user->{EXPIRE_LOCK} = date($datetimeFormat, strtotime($user->{EXPIRE_LOCK}));
                $messageLockUser = '<br>Temporarily locked';
            }
            
            /* format the date */
            $user->{REGISTRATION_DATETIME} = date($datetimeFormat, strtotime($user->{REGISTRATION_DATETIME}));
        }
        
        /* return data */
        return [
            USER => $user,
            IS_LOCK => $isLock,
            TOKEN => generateToken(CSRF_DELETE_USER),
            CLASS_ENABLE_ACC => $classEnabledAccount,
            NO_ESCAPE.MESSAGE_ENABLE_ACC => $messageEnable,
            NO_ESCAPE.MESSAGE_LOCK_ACC => $messageLockUser,
            CAN_UPDATE_USER => $canUpdateUser,
            CAN_DELETE_USER => $canDeleteUser,
            CAN_CHANGE_PASSWORD => $canChangePassword,
            VIEW_ROLE => $canViewRole,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail)
        ];
    }

    /* function to get data of user */
    public function getLocksUserData(int $userId, string $datetimeFormat, bool $canUnlockUser): array {
        /* init user model and get user */
        $userModel = new User($this->conn);
        /* if is numeric get user by id */
        if (is_numeric($userId)) {
            $user = $userModel->getUserAndLock($userId);
            /* enable account class */
            $classEnabledAccount = 'text-';
            /* if account is enable */
            if ($user->{ENABLED}) {
                $classEnabledAccount .= 'success';
                $messageEnable = 'ENABLED';
                /* if account is disabled */
            } else {
                $classEnabledAccount .= 'danger';
                $messageEnable = 'DISABLED';
            }

            /* init message */
            $messageLockUser = '';
            /* if user has a lock, then set message and format the date */
            if (($isLock = (isset($user->{EXPIRE_LOCK}) && new DateTime($user->{EXPIRE_LOCK}) > new DateTime()))) {
                $user->{EXPIRE_LOCK} = date($datetimeFormat, strtotime($user->{EXPIRE_LOCK}));
                $messageLockUser = '<br>Temporarily locked';
            }
            
            /* format the date */
            $user->{REGISTRATION_DATETIME} = date($datetimeFormat, strtotime($user->{REGISTRATION_DATETIME}));
        /* else set user false */
        } else $user = FALSE;
        
        /* return data */
        return [
            USER => $user,
            IS_LOCK => $isLock,
            TOKEN => generateToken(CSRF_UNLOCK_USER),
            CLASS_ENABLE_ACC => $classEnabledAccount,
            NO_ESCAPE.MESSAGE_ENABLE_ACC => $messageEnable,
            NO_ESCAPE.MESSAGE_LOCK_ACC => $messageLockUser,
        ];
    }

    /* function to get data of deleted user */
    public function getDeletedUserData(string $userId, string $datetimeFormat, bool $canViewRole, bool $canSendEmail): array {
        /* init user model and get user */
        $userModel = new DeletedUser($this->conn);
        /* if is numeric get user by id, else set false */
        $user = is_numeric($userId) ? $userModel->getDeleteUserAndRole($userId) : FALSE;
        
        /* if found user */
        if ($user) {
            /* format the date */
            $user->{REGISTRATION_DATETIME} = date($datetimeFormat, strtotime($user->{REGISTRATION_DATETIME}));
            $user->{DELETE_DATETIME} = date($datetimeFormat, strtotime($user->{DELETE_DATETIME}));
        }
        
        /* return data */
        return [
            USER => $user,
            VIEW_ROLE => $canViewRole,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail)
        ];
    }

    /* function to get data of deleted user */
    public function getSessionData(string $sessionId, string $datetimeFormat, bool $canSendEmail): array {
        /* init user model and get user */
        $sessionModel = new Session($this->conn);
        /* if is numeric get user by id, else set false */
        $session = is_numeric($sessionId) ? $sessionModel->getSessionAndUser($sessionId) : FALSE;

        /* if found user format the date */
        if ($session) $session->{EXPIRE_DATETIME} = date($datetimeFormat, strtotime($session->{EXPIRE_DATETIME}));
        
        /* return data */
        return [
            SESSION => $session,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail)
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

