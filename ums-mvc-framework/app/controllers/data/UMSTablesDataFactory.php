<?php

namespace app\controllers\data;

use app\models\PasswordResetRequest;
use app\models\PendingEmail;
use app\models\DeletedUser;
use app\models\PendingUser;
use app\models\Session;
use app\models\User;
use app\models\Role;
use \DateTime;
use \PDO;
use app\core\Router;

/**
 * Class data factory, used for generate
 * and manage the data of response of user
 * management system
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSTablesDataFactory extends PaginationDataFactory {
    private $umsTablesRoute;

    protected function __construct(array $langData, PDO $conn=NULL) {
        parent::__construct($langData, $conn);
        $this->umsTablesRoute = Router::getRoute('app\controllers\UMSTablesController', 'showTable');
        $this->umsTablesRoute = str_replace(':table', '', $this->umsTablesRoute);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## TABLES FUNCTIONS ########## */

    /* function to get data of users list */
    public function getUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model and count users */
        $userModel = new User($this->conn);
        $totUsers = $userModel->countAllUsers($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, USERS_ORDER_BY_LIST, USER_ID);

        /* get search query */
        $searchQuery = $this->getSearchQuery($search);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, $this->umsTablesRoute.USERS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[USERS] = $userModel->getUsers($orderBy, $orderDir, $search, $start, $data[ROWS_FOR_PAGE]);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_USERS] = $totUsers;
        $data[VIEW_ROLE] = $canViewRole;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        /* return data */
        return $data;
    }

    /* function to get data of deleted users list */
    public function getDeletedUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model and count users */
        $delUserModel = new DeletedUser($this->conn);
        $totUsers = $delUserModel->countDeletedUsers($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, DELETED_USERS_ORDER_BY_LIST, USER_ID);

        /* get search query */
        $searchQuery = $this->getSearchQuery($search);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, $this->umsTablesRoute.DELETED_USER_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(DELETED_USER_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], DELETED_USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[USERS] = $delUserModel->getDeletedUsers($orderBy, $orderDir, $search, $start, $data[ROWS_FOR_PAGE]);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_USERS] = $totUsers;
        $data[VIEW_ROLE] = $canViewRole;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        return $data;
    }

    /* function to get data of deleted users list */
    public function getPendingUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model */
        $pendUserModel = new PendingUser($this->conn);

        /* count user */
        $totUsers = $pendUserModel->countAllPendingUsers($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, PENDING_USERS_ORDER_BY_LIST, PENDING_USER_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, $this->umsTablesRoute.PENDING_USERS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PENDING_USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[USERS] = $pendUserModel->getPendingUsers($orderBy, $orderDir, $search, $start, $data[ROWS_FOR_PAGE]);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_USERS] = $totUsers;
        $data[VIEW_ROLE] = $canViewRole;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        return $data;
    }

    /* function to get data of pending emails list */
    public function getPendingEmailsListData(string $orderBy, string $orderDir, int $page, int $mailsForPage, string $search, bool $canSendEmails): array {
        /* init user model */
        $pendEmailModel = new PendingEmail($this->conn);
        
        /* count tot pending mails */
        $totEmails = $pendEmailModel->countAllPendingEmails($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, PENDING_EMAILS_ORDER_BY_LIST, PENDING_EMAIL_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $mailsForPage, $totEmails, $this->umsTablesRoute.PENDING_EMAILS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_EMAILS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PENDING_EMAILS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[EMAILS] = $pendEmailModel->getPendingEmails($orderBy, $orderDir, $search, $start, $data[ROWS_FOR_PAGE]);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_PENDING_MAILS] = $totEmails;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        return $data;
    }

    /* function to get data of roles list */
    public function getRolesListData(string $orderBy, string $orderDir, int $page, int $rolesForPage): array {
        /* init user model */
        $rolesModel = new Role($this->conn);
        
        /* count tot pending mails */
        $totRoles = $rolesModel->countRoles();

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, ROLES_ORDER_BY_LIST, ROLE_ID);
        
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $rolesForPage, $totRoles, $this->umsTablesRoute.ROLES_TABLE);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(ROLES_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], ROLES_ORDER_BY_LIST));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[ROLES] = $rolesModel->getRoles($orderBy, $orderDir, $start, $data[ROWS_FOR_PAGE]);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_ROLES] = $totRoles;
        return $data;
    }

    /* function to get data of sessions list */
    public function geSessionsListData(string $orderBy, string $orderDir, int $page, int $sessionsForPage, string $search): array {
        /* init user model */
        $sessionModel = new Session($this->conn);
        
        /* count tot pending mails */
        $totSessions = $sessionModel->countAllSessions($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, SESSIONS_ORDER_BY_LIST, SESSION_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $sessionsForPage, $totSessions, $this->umsTablesRoute.SESSIONS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(SESSIONS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], SESSIONS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[SESSIONS] = $sessionModel->getSessions($orderBy, $orderDir, $search, $start, $data[ROWS_FOR_PAGE]);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_SESSIONS] = $totSessions;
        return $data;
    }

    /* function to get data of password reset requests list */
    public function gePassResetReqListData(string $orderBy, string $orderDir, int $page, int $requestsForPage, string $search): array {
        /* init user model */
        $passResReqModel = new PasswordResetRequest($this->conn);
        
        /* count tot pending mails */
        $totReq = $passResReqModel->countAllPasswordResetReq($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, PASS_RESET_REQ_ORDER_BY_LIST, PASSWORD_RESET_REQ_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $requestsForPage, $totReq, $this->umsTablesRoute.PASSWORD_RESET_REQ_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PASSWORD_RESET_REQ_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PASS_RESET_REQ_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[REQUESTS] = $passResReqModel->getPassResetRequests($orderBy, $orderDir, $search, $start, $data[ROWS_FOR_PAGE]);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_REQ] = $totReq;
        return $data;
    }

    /* ########## ROWS FUNCTIONS ########## */

    /* function to get data of user */
    public function getUserData(string $username, string $datetimeFormat, bool $canUpdateUser, bool $canDeleteUser, bool $canViewRole, bool $canSendEmail, bool $canUnlockUser): array {
        /* init user model and get user */
        $userModel = new User($this->conn);
        /* if is numeric get user by id */
        if (is_numeric($username)) $user = $userModel->getUserAndRole($username);
        /* else get user by username */
        else $user = $userModel->getUserAndRoleByUsername($username);
        /* init var */
        $messageEnable = '';
        $messageLockUser = '';
        $isLock = FALSE;
        if ($user) {
            /* set enabled message */
            $messageEnable = $user->{ENABLED} ? 'ENABLED' : 'DISABLED';
            
            /* init message */
            $messageLockUser = 'Unlocked';
            /* check lock */
            if (isset($user->{EXPIRE_LOCK})) {
                /* if user has a lock, then set message */
                if (($isLock = new DateTime($user->{EXPIRE_LOCK}) > new DateTime())) $messageLockUser = 'Temporarily locked';
                /* format the date */
                $user->{EXPIRE_LOCK} = date($datetimeFormat, strtotime($user->{EXPIRE_LOCK}));
            }

            /* format the date */
            $user->{REGISTRATION_DATETIME} = date($datetimeFormat, strtotime($user->{REGISTRATION_DATETIME}));
        }
        
        /* return data */
        return [
            USER => $user,
            IS_LOCK => $isLock,
            TOKEN => generateToken(CSRF_DELETE_USER),
            LOCKS_USER_RESET_TOKEN => generateToken(CSRF_LOCK_USER_RESET),
            MESSAGE_ENABLE_ACC => $messageEnable,
            MESSAGE_LOCK_ACC => $messageLockUser,
            CAN_UPDATE_USER => $canUpdateUser,
            CAN_DELETE_USER => $canDeleteUser,
            CAN_UNLOCK_USER => $canUnlockUser,
            VIEW_ROLE => $canViewRole,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail)
        ];
    }

    /* function to get data of deleted user */
    public function getDeletedUserData(string $userId, string $datetimeFormat, bool $canViewRole, bool $canSendEmail, bool $canRestoreUser): array {
        /* init user model and get user */
        $userModel = new DeletedUser($this->conn);
        
        /* if is numeric get session by id */
        if (is_numeric($userId) && ($user = $userModel->getDeleteUserAndRole($userId))) {
            /* if found user check if is expired and format the date*/
            $user->{REGISTRATION_DATETIME} = date($datetimeFormat, strtotime($user->{REGISTRATION_DATETIME}));
            $user->{DELETE_DATETIME} = date($datetimeFormat, strtotime($user->{DELETE_DATETIME}));
        /* else set false */
        } else $user = FALSE;
        
        /* return data */
        return [
            USER => $user,
            VIEW_ROLE => $canViewRole,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail),
            CAN_RESTORE_USER => $canRestoreUser,
            RESTORE_TOKEN => generateToken(CSRF_RESTORE_USER),
        ];
    }

    /* function to get data of session */
    public function getSessionData(string $sessionId, string $datetimeFormat, bool $canSendEmail, bool $canRemoveSession): array {
        /* init user model and get user */
        $sessionModel = new Session($this->conn);
        /* init var */
        $rmvSsnTkn = '';
        $isExpired = TRUE;
        $messageExpire = '';
        /* if is numeric get session by id */
        if (is_numeric($sessionId) && ($session = $sessionModel->getSessionLeftUser($sessionId))) {
            /* if found session check if is expired and format the date*/
            /* if is not set token */
            if (!isset($session->{SESSION_TOKEN})) $messageExpire = 'No token';
            /* else if is expired */
            elseif (new DateTime($session->{EXPIRE_DATETIME}) < new DateTime()) $messageExpire = 'Expired';
            /* else if is valid user */
            elseif (isset($session->{ENABLED}) && $session->{ENABLED}) {
                $rmvSsnTkn = generateToken(CSRF_INVALIDATE_SESSION);
                $messageExpire = 'Valid';
                $isExpired = FALSE;
            }
            $session->{EXPIRE_DATETIME} = date($datetimeFormat, strtotime($session->{EXPIRE_DATETIME}));
        /* else set false */
        } else $session = FALSE;
        /* return data */
        return [
            SESSION => $session,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail),
            IS_EXPIRED => $isExpired,
            MESSAGE_EXPIRE => $messageExpire,
            INVALIDATE_TOKEN => $rmvSsnTkn,
            CAN_REMOVE_SESSION => $canRemoveSession
        ];
    }

    /* function to get lock data of user */
    public function getUserLocksData(int $userId, string $datetimeFormat, bool $canUnlockUser): array {
        /* init user model and get user */
        $userModel = new User($this->conn);
        /* init var */
        $messageEnable = '';
        $messageLockUser = '';
        $isLock = FALSE;
        /* if is numeric get lock by user id */
        if (is_numeric($userId) && ($userLocks = $userModel->getUserAndLock($userId))) {
            /* set enabled message */
            $messageEnable = $userLocks->{ENABLED} ? 'ENABLED' : 'DISABLED';
            
            /* init message */
            $messageLockUser = 'Unlocked';
            $isLock = FALSE;
            /* check lock */
            if (isset($userLocks->{EXPIRE_LOCK})) {
                /* if user has a lock, then set message */
                if (($isLock = new DateTime($userLocks->{EXPIRE_LOCK}) > new DateTime())) $messageLockUser = 'Temporarily locked';
                /* format the date */
                $userLocks->{EXPIRE_LOCK} = date($datetimeFormat, strtotime($userLocks->{EXPIRE_LOCK}));
            }

            /* if is set time wrong password format the date */
            if (isset($userLocks->{EXPIRE_TIME_WRONG_PASSWORD})) $userLocks->{EXPIRE_LOCK} = date($datetimeFormat, strtotime($userLocks->{EXPIRE_LOCK}));
            
            /* format the date */
            $userLocks->{REGISTRATION_DATETIME} = date($datetimeFormat, strtotime($userLocks->{REGISTRATION_DATETIME}));
        /* else set user false */
        } else $userLocks = FALSE;
        
        /* return data */
        return [
            USER => $userLocks,
            IS_LOCK => $isLock,
            LOCKS_USER_RESET_TOKEN => generateToken(CSRF_LOCK_USER_RESET),
            MESSAGE_ENABLE_ACC => $messageEnable,
            MESSAGE_LOCK_ACC => $messageLockUser,
            CAN_UNLOCK_USER => $canUnlockUser,
        ];
    }

    /* function to get data of pending email */
    public function getPendingEmailData(string $pendMailId, string $datetimeFormat, bool $canSendEmail, bool $canRemoveToken): array {
        /* init user model and get user */
        $pendMailModel = new PendingEmail($this->conn);
        /* init var */
        $isValid = FALSE;
        $isExpired = TRUE;
        $resendToken = '';
        $messageExpire = '';
        $invalidateToken = '';
        /* if is numeric get pending email by id nad if found it check if is expired and format the date*/
        if (is_numeric($pendMailId) && ($pendMail = $pendMailModel->getPendingEmailLeftUser($pendMailId))) {
            /* if is not set token */
            if (!isset($pendMail->{ENABLER_TOKEN})) $messageExpire = 'No token';
            /* else if is expired */
            elseif (new DateTime($pendMail->{EXPIRE_DATETIME}) < new DateTime()) {
                $messageExpire = 'Expired';
                $isValid = TRUE;
            }
            /* else if is valid */
            elseif (isset($pendMail->{ENABLED}) && $pendMail->{ENABLED}) {
                $messageExpire = 'Valid';
                $isExpired = FALSE;
                $isValid = TRUE;
            }
            /* set tokens */
            $invalidateToken = $isValid && $canRemoveToken ? generateToken(CSRF_INVALIDATE_PENDING_EMAIL) : '';
            $resendToken = $isValid && $canSendEmail ? generateToken(CSRF_RESEND_ENABLER_EMAIL) : '';
            /* format datetime */
            $pendMail->{EXPIRE_DATETIME} = date($datetimeFormat, strtotime($pendMail->{EXPIRE_DATETIME}));
            /* else set false */
        } else $pendMail = FALSE;
        /* return data */
        return [
            PENDING => $pendMail,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail),
            IS_VALID => $isValid,
            IS_EXPIRED => $isExpired,
            MESSAGE_EXPIRE => $messageExpire,
            CAN_SEND_EMAIL => $canSendEmail,
            CAN_REMOVE_ENABLER_TOKEN => $canRemoveToken,
            RESEND_ENABLER_EMAIL_TOKEN => $resendToken,
            INVALIDATE_TOKEN => $invalidateToken
        ];
    }

    /* function to get data of pending user */
    public function getPendingUserData(string $userId, string $datetimeFormat, bool $canViewRole, bool $canSendEmail, bool $canRemoveToken): array {
        /* init user model and get user */
        $pendUserModel = new PendingUser($this->conn);
        /* init var */
        $messageExpire = '';
        $isExpire = TRUE;
        $isValid = FALSE;
        $resendToken = '';
        $inavalidateToken = '';
        if (is_numeric($userId) && ($user = $pendUserModel->getPendingUserAndRole($userId))) {
            /* set vars */
            $messageExpire = 'Valid';
            $isValid = TRUE;
            /* check lock */
            if (isset($user->{EXPIRE_DATETIME})) {
                /* check if is active user */
                if (isset($user->{USER_ID_FRGN})) {
                    $messageExpire = 'Active user';
                    $isValid = FALSE;
                /* if link is expire set message */
                } elseif (!isset($user->{ENABLER_TOKEN})) {
                    $messageExpire = 'No token';
                    $isValid = FALSE;
                } elseif (($isExpire = new DateTime($user->{EXPIRE_DATETIME}) < new DateTime())) $messageExpire = 'Expired';
                /* format the date */
                $user->{EXPIRE_DATETIME} = date($datetimeFormat, strtotime($user->{EXPIRE_DATETIME}));
            }
            /* set tokens */
            $inavalidateToken = $isValid && $canRemoveToken ? generateToken(CSRF_INVALIDATE_PENDING_USER) : '';
            $resendToken = $isValid && $canSendEmail ? generateToken(CSRF_RESEND_ENABLER_ACC) : '';
            /* format the date */
            $user->{REGISTRATION_DATETIME} = date($datetimeFormat, strtotime($user->{REGISTRATION_DATETIME}));
        }

        /* return data */
        return [
            USER => $user,
            IS_VALID => $isValid,
            IS_EXPIRED => $isExpire,
            RESEND_ENABLER_EMAIL_TOKEN => $resendToken,
            INVALIDATE_TOKEN => $inavalidateToken,
            MESSAGE_EXPIRE => $messageExpire,
            VIEW_ROLE => $canViewRole,
            CAN_SEND_EMAIL => $canSendEmail,
            CAN_REMOVE_ENABLER_TOKEN => $canRemoveToken,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail)
        ];
    }

    /* function to get data of pending email */
    public function getPassResReqData(string $passResReqId, string $datetimeFormat, bool $canSendEmail, bool $canRemoveToken): array {
        /* init user model and get user */
        $passResReqModel = new PasswordResetRequest($this->conn);
        /* init var */
        $isValid = FALSE;
        $isExpired = TRUE;
        $resendToken = '';
        $messageExpire = '';
        $invalidateToken = '';
        /* if is numeric get pending email by id nad if found it check if is expired and format the date*/
        if (is_numeric($passResReqId) && ($passResReq = $passResReqModel->getPassResReqLeftUser($passResReqId))) {
            /* if is not set token */
            if (!isset($passResReq->{PASSWORD_RESET_TOKEN})) $messageExpire = 'No token';
            /* else if is expired */
            elseif (new DateTime($passResReq->{EXPIRE_DATETIME}) < new DateTime()) {
                $messageExpire = 'Expired';
                $isValid = TRUE;
            }

            /* else if is valid */
            elseif (isset($passResReq->{ENABLED}) && $passResReq->{ENABLED}) {
                $messageExpire = 'Valid';
                $isExpired = FALSE;
                $isValid = TRUE;
            }

            /* set tokens */
            $invalidateToken = $isValid && $canRemoveToken ? generateToken(CSRF_INVALIDATE_PASS_RES_REQ) : '';
            $resendToken = $isValid && $canSendEmail ? generateToken(CSRF_RESEND_PASS_RES_REQ) : '';
            /* format datetime */
            $passResReq->{EXPIRE_DATETIME} = date($datetimeFormat, strtotime($passResReq->{EXPIRE_DATETIME}));

            /* else set false */
        } else $passResReq = FALSE;
        /* return data */
        return [
            REQUEST => $passResReq,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmail),
            IS_VALID => $isValid,
            IS_EXPIRED => $isExpired,
            MESSAGE_EXPIRE => $messageExpire,
            CAN_SEND_EMAIL => $canSendEmail,
            CAN_REMOVE_ENABLER_TOKEN => $canRemoveToken,
            RESEND_ENABLER_EMAIL_TOKEN => $resendToken,
            INVALIDATE_TOKEN => $invalidateToken
        ];
    }
    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get link and class for head of table */
    private function getLinkAndClassHeadTable(string $table, string $orderBy, string $orderDir, int $page, int $rowsForPage, array $columnList, string $searchQuery=''): array {
        /* get direction reverse and class */
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        $closeUrl = "/$page/$rowsForPage$searchQuery";
        $data = [];
        foreach ($columnList as $col) {
            $data[LINK_HEAD.$col] = "/ums/table/$table/$col/";
            $data[LINK_HEAD.$col] .= $orderBy === $col ? $orderDirRev : DESC;
            $data[LINK_HEAD.$col] .= $closeUrl;
            $data[CLASS_HEAD.$col] = $orderBy === $col ? "fas fa-sort-$orderDirClass" : '';
        }
        return $data;
    }
}
