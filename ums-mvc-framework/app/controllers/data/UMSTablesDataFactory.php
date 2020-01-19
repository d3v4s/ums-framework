<?php

namespace app\controllers\data;

use app\models\PasswordResetRequest;
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
class UMSTablesDataFactory extends PaginationDataFactory {

    protected function __construct(PDO $conn=NULL) {
        parent::__construct($conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

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
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        /* add user data */
        $start = $usersForPage * ($page - 1);
        $data[USERS] = $userModel->getUsers($orderBy, $orderDir, $search, $start, $usersForPage);
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
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(DELETED_USER_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], DELETED_USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $usersForPage * ($page - 1);
        $data[USERS] = $delUserModel->getDeletedUsers($orderBy, $orderDir, $search, $start, $usersForPage);
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
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PENDING_USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $usersForPage * ($page - 1);
        $data[USERS] = $pendUserModel->getPendingUsers($orderBy, $orderDir, $search, $start, $usersForPage);
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
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $mailsForPage, $totEmails, '/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_EMAILS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PENDING_EMAILS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $mailsForPage * ($page - 1);
        $data[EMAILS] = $pendEmailModel->getPendingEmails($orderBy, $orderDir, $search, $start, $mailsForPage);
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
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $rolesForPage, $totRoles, '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(ROLES_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], ROLES_ORDER_BY_LIST));
        /* add user data */
        $start = $rolesForPage * ($page - 1);
        $data[ROLES] = $rolesModel->getRoles($orderBy, $orderDir, $start, $rolesForPage);
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
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $sessionsForPage, $totSessions, '/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(SESSIONS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], SESSIONS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $sessionsForPage * ($page - 1);
        $data[SESSIONS] = $sessionModel->getSessions($orderBy, $orderDir, $search, $start, $sessionsForPage);
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
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $requestsForPage, $totReq, '/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PASSWORD_RESET_REQ_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PASS_RESET_REQ_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $requestsForPage * ($page - 1);
        $data[REQUESTS] = $passResReqModel->getPassResetRequests($orderBy, $orderDir, $search, $start, $requestsForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_REQ] = $totReq;
        return $data;
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
            $data[LINK_HEAD.$col] = '/'.UMS_TABLES_ROUTE."/$table/$col/";
            $data[LINK_HEAD.$col] .= $orderBy === $col ? $orderDirRev : DESC;
            $data[LINK_HEAD.$col] .= $closeUrl;
            $data[CLASS_HEAD.$col] = $orderBy === $col ? "fas fa-sort-$orderDirClass" : '';
        }
        return $data;
    }
}
