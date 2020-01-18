<?php

namespace app\controllers\data;

use app\models\User;
use \PDO;
use app\models\DeletedUser;
use app\models\PendingUser;
use app\models\PendingEmail;
use app\models\Role;
use app\models\Session;
use app\models\PasswordResetRequest;

/**
 * Class data factory, used for generate
 * and manage the data of response of user
 * management system
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSTablesDataFactory extends DataFactory {

    protected function __construct(PDO $conn=NULL) {
        parent::__construct($conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to get data of users list */
    public function getUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model */
        $userModel = new User($this->conn);

        /* count user */
        $totUsers = $userModel->countUsers($search);
        /* calc users for page and n. pages */
        $usersForPage = in_array($usersForPage, ROWS_FOR_PAGE_LIST) ? $usersForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totUsers/$usersForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $usersForPage * ($page - 1);
        $nlinkPagination = LINK_PAGINATION;

        /* calc start and stop page of pagination */
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;

        /* get order by and order direction */
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $orderBy = in_array($orderBy, ORDER_BY_LIST) ? $orderBy : USER_ID;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;

        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
        $closeUrl = "/$page/$usersForPage$searchQuery";

        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE.'/'.USER_ID.'/';
        $linkHeadId .= $orderBy === USER_ID ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === USER_ID ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head name */
        $linkHeadName = '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE.'/'.NAME.'/';
        $linkHeadName .= $orderBy === NAME ? $orderDirRev : DESC;
        $linkHeadName .= $closeUrl;
        $classHeadName = $orderBy === NAME ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head username */
        $linkHeadUsername = '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE.'/'.USERNAME.'/';
        $linkHeadUsername .= $orderBy === USERNAME ? $orderDirRev : DESC;
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === USERNAME ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head email */
        $linkHeadEmail ='/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE.'/'.EMAIL.'/';
        $linkHeadEmail .= $orderBy === EMAIL ? $orderDirRev : DESC;
        $linkHeadEmail .= $closeUrl;
        $classHeadEmail = $orderBy === EMAIL ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head enabled */
        $linkHeadEnabled = '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE.'/'.ENABLED.'/';
        $linkHeadEnabled .= $orderBy === ENABLED ? $orderDirRev : DESC;
        $linkHeadEnabled .= $closeUrl;
        $classHeadEnabled = $orderBy === ENABLED ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head roletype */
        $linkHeadRole = '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE.'/'.ROLE.'/';
        $linkHeadRole .= $orderBy === ROLE ? $orderDirRev : DESC;
        $linkHeadRole .= $closeUrl;
        $classHeadRole = $orderBy === ROLE ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $usersForPage . $searchQuery;
        $baseLinkPagination = '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE."/$orderBy/$orderDir/";

        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';

        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';

        /* return data */
        return [
            ORDER_BY => $orderBy,
            SEARCH => $search,
            SEARCH_QUERY => $searchQuery,
            PAGE => $page,
            ROWS_FOR_PAGE => $usersForPage,
            TOT_USERS => $totUsers,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            LINK_HEAD.USER_ID => $linkHeadId,
            CLASS_HEAD.USER_ID => $classHeadId,
            LINK_HEAD.NAME => $linkHeadName,
            CLASS_HEAD.NAME => $classHeadName,
            LINK_HEAD.USERNAME => $linkHeadUsername,
            CLASS_HEAD.USERNAME => $classHeadUsername,
            LINK_HEAD.EMAIL => $linkHeadEmail,
            CLASS_HEAD.EMAIL => $classHeadEmail,
            LINK_HEAD.ENABLED => $linkHeadEnabled,
            CLASS_HEAD.ENABLED => $classHeadEnabled,
            LINK_HEAD.ROLE => $linkHeadRole,
            CLASS_HEAD.ROLE => $classHeadRole,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            USERS => $userModel->getUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl",
            VIEW_ROLE => $canViewRole,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmails)
        ];
    }

    /* function to get data of deleted users list */
    public function getDeletedUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model */
        $delUserModel = new DeletedUser($this->conn);
        
        /* count user */
        $totUsers = $delUserModel->countDeletedUsers($search);
        /* calc users for page and n. pages */
        $usersForPage = in_array($usersForPage, ROWS_FOR_PAGE_LIST) ? $usersForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totUsers/$usersForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $usersForPage * ($page - 1);
        $nlinkPagination = LINK_PAGINATION;
        
        /* calc start and stop page of pagination */
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
        
        /* get order by and order direction */
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $orderBy = in_array($orderBy, DELETED_USERS_ORDER_BY_LIST) ? $orderBy : USER_ID;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        
        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
        $closeUrl = "/$page/$usersForPage$searchQuery";
        
        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.USER_ID.'/';
        $linkHeadId .= $orderBy === USER_ID ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === USER_ID ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head name */
        $linkHeadName = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.NAME.'/';
        $linkHeadName .= $orderBy === NAME ? $orderDirRev : DESC;
        $linkHeadName .= $closeUrl;
        $classHeadName = $orderBy === NAME ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head username */
        $linkHeadUsername = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.USERNAME.'/';
        $linkHeadUsername .= $orderBy === USERNAME ? $orderDirRev : DESC;
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === USERNAME ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head email */
        $linkHeadEmail ='/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.EMAIL.'/';
        $linkHeadEmail .= $orderBy === EMAIL ? $orderDirRev : DESC;
        $linkHeadEmail .= $closeUrl;
        $classHeadEmail = $orderBy === EMAIL ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head roletype */
        $linkHeadRole = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.ROLE.'/';
        $linkHeadRole .= $orderBy === ROLE ? $orderDirRev : DESC;
        $linkHeadRole .= $closeUrl;
        $classHeadRole = $orderBy === ROLE ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head roletype */
        $linkHeadRegDate = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.REGISTRATION_DATETIME.'/';
        $linkHeadRegDate .= $orderBy === REGISTRATION_DATETIME ? $orderDirRev : DESC;
        $linkHeadRegDate .= $closeUrl;
        $classHeadRegDate = $orderBy === REGISTRATION_DATETIME ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head roletype */
        $linkHeadDelDate = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.DELETE_DATETIME.'/';
        $linkHeadDelDate .= $orderBy === DELETE_DATETIME ? $orderDirRev : DESC;
        $linkHeadDelDate .= $closeUrl;
        $classHeadDelDate = $orderBy === DELETE_DATETIME ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $usersForPage . $searchQuery;
        $baseLinkPagination = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE."/$orderBy/$orderDir/";
        
        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';
        
        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';

        /* return data */
        return [
            ORDER_BY => $orderBy,
            SEARCH => $search,
            SEARCH_QUERY => $searchQuery,
            PAGE => $page,
            ROWS_FOR_PAGE => $usersForPage,
            TOT_USERS => $totUsers,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            LINK_HEAD.USER_ID => $linkHeadId,
            CLASS_HEAD.USER_ID => $classHeadId,
            LINK_HEAD.NAME => $linkHeadName,
            CLASS_HEAD.NAME => $classHeadName,
            LINK_HEAD.USERNAME => $linkHeadUsername,
            CLASS_HEAD.USERNAME => $classHeadUsername,
            LINK_HEAD.EMAIL => $linkHeadEmail,
            CLASS_HEAD.EMAIL => $classHeadEmail,
            LINK_HEAD.ROLE => $linkHeadRole,
            CLASS_HEAD.ROLE => $classHeadRole,
            LINK_HEAD.REGISTRATION_DATETIME => $linkHeadRegDate,
            CLASS_HEAD.REGISTRATION_DATETIME => $classHeadRegDate,
            LINK_HEAD.DELETE_DATETIME => $linkHeadDelDate,
            CLASS_HEAD.DELETE_DATETIME => $classHeadDelDate,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            USERS => $delUserModel->getDeletedUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl", 
            VIEW_ROLE => $canViewRole,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmails)
        ];
    }

    /* function to get data of deleted users list */
    public function getPendingUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model */
        $pendUserModel = new PendingUser($this->conn);
        
        /* count user */
        $totUsers = $pendUserModel->countAllPendingUsers($search);
        /* calc users for page and n. pages */
        $usersForPage = in_array($usersForPage, ROWS_FOR_PAGE_LIST) ? $usersForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totUsers/$usersForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $usersForPage * ($page - 1);
        $nlinkPagination = LINK_PAGINATION;
        
        /* calc start and stop page of pagination */
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
        
        /* get order by and order direction */
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $orderBy = in_array($orderBy, PENDING_USERS_ORDER_BY_LIST) ? $orderBy : PENDING_USER_ID;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        
        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
        $closeUrl = "/$page/$usersForPage$searchQuery";
        
        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.PENDING_USER_ID.'/';
        $linkHeadId .= $orderBy === PENDING_USER_ID ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === PENDING_USER_ID ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head name */
        $linkHeadName = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.NAME.'/';
        $linkHeadName .= $orderBy === NAME ? $orderDirRev : DESC;
        $linkHeadName .= $closeUrl;
        $classHeadName = $orderBy === NAME ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head username */
        $linkHeadUsername = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.USERNAME.'/';
        $linkHeadUsername .= $orderBy === USERNAME ? $orderDirRev : DESC;
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === USERNAME ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head email */
        $linkHeadEmail ='/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.EMAIL.'/';
        $linkHeadEmail .= $orderBy === EMAIL ? $orderDirRev : DESC;
        $linkHeadEmail .= $closeUrl;
        $classHeadEmail = $orderBy === EMAIL ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head roletype */
        $linkHeadRole = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.ROLE.'/';
        $linkHeadRole .= $orderBy === ROLE ? $orderDirRev : DESC;
        $linkHeadRole .= $closeUrl;
        $classHeadRole = $orderBy === ROLE ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadToken = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.ENABLER_TOKEN.'/';
        $linkHeadToken .= $orderBy === ENABLER_TOKEN ? $orderDirRev : DESC;
        $linkHeadToken .= $closeUrl;
        $classHeadToken = $orderBy === ENABLER_TOKEN ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head expire datetime */
        $linkHeadRegDate = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.REGISTRATION_DATETIME.'/';
        $linkHeadRegDate .= $orderBy === REGISTRATION_DATETIME ? $orderDirRev : DESC;
        $linkHeadRegDate .= $closeUrl;
        $classHeadRegDate = $orderBy === REGISTRATION_DATETIME ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head expire datetime */
        $linkHeadExpDate = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.EXPIRE_DATETIME.'/';
        $linkHeadExpDate .= $orderBy === EXPIRE_DATETIME ? $orderDirRev : DESC;
        $linkHeadExpDate .= $closeUrl;
        $classHeadExpDate = $orderBy === EXPIRE_DATETIME ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $usersForPage . $searchQuery;
        $baseLinkPagination = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE."/$orderBy/$orderDir/";

        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';

        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';

        /* return data */
        return [
            ORDER_BY => $orderBy,
            SEARCH => $search,
            SEARCH_QUERY => $searchQuery,
            PAGE => $page,
            ROWS_FOR_PAGE => $usersForPage,
            TOT_USERS => $totUsers,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            LINK_HEAD.PENDING_USER_ID => $linkHeadId,
            CLASS_HEAD.PENDING_USER_ID => $classHeadId,
            LINK_HEAD.NAME => $linkHeadName,
            CLASS_HEAD.NAME => $classHeadName,
            LINK_HEAD.USERNAME => $linkHeadUsername,
            CLASS_HEAD.USERNAME => $classHeadUsername,
            LINK_HEAD.EMAIL => $linkHeadEmail,
            CLASS_HEAD.EMAIL => $classHeadEmail,
            LINK_HEAD.ROLE => $linkHeadRole,
            CLASS_HEAD.ROLE => $classHeadRole,
            LINK_HEAD.ENABLER_TOKEN => $linkHeadToken,
            CLASS_HEAD.ENABLER_TOKEN => $classHeadToken,
            LINK_HEAD.REGISTRATION_DATETIME => $linkHeadRegDate,
            CLASS_HEAD.REGISTRATION_DATETIME => $classHeadRegDate,
            LINK_HEAD.EXPIRE_DATETIME => $linkHeadExpDate,
            CLASS_HEAD.EXPIRE_DATETIME => $classHeadExpDate,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            USERS => $pendUserModel->getPendingUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl",
            VIEW_ROLE => $canViewRole,
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmails)
        ];
    }

    /* function to get data of pending emails list */
    public function getPendingEmailsListData(string $orderBy, string $orderDir, int $page, int $mailsForPage, string $search, bool $canSendEmails): array {
        /* init user model */
        $pendEmailModel = new PendingEmail($this->conn);
        
        /* count tot pending mails */
        $totEmails = $pendEmailModel->countAllPendingEmails($search);

        /* calc users for page and n. pages */
        $mailsForPage = in_array($mailsForPage, ROWS_FOR_PAGE_LIST) ? $mailsForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totEmails/$mailsForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $mailsForPage * ($page - 1);
        $nlinkPagination = LINK_PAGINATION;
        
        /* calc start and stop page of pagination */
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
        
        /* get order by and order direction */
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $orderBy = in_array($orderBy, PENDING_EMAILS_ORDER_BY_LIST) ? $orderBy : PENDING_EMAIL_ID;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        
        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
        $closeUrl = "/$page/$mailsForPage$searchQuery";
        
        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE.'/'.PENDING_EMAIL_ID.'/';
        $linkHeadId .= $orderBy === PENDING_EMAIL_ID ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === PENDING_EMAIL_ID ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head username */
        $linkHeadUsername = '/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE.'/'.USERNAME.'/';
        $linkHeadUsername .= $orderBy === USERNAME ? $orderDirRev : DESC;
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === USERNAME ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head new email */
        $linkHeadNewEmail ='/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE.'/'.NEW_EMAIL.'/';
        $linkHeadNewEmail .= $orderBy === NEW_EMAIL ? $orderDirRev : DESC;
        $linkHeadNewEmail .= $closeUrl;
        $classHeadNewEmail = $orderBy === NEW_EMAIL ? "fas fa-sort-$orderDirClass" : '';
                
        /* link and class for head token */
        $linkHeadToken = '/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE.'/'.ENABLER_TOKEN.'/';
        $linkHeadToken .= $orderBy === ENABLER_TOKEN ? $orderDirRev : DESC;
        $linkHeadToken .= $closeUrl;
        $classHeadToken = $orderBy === ENABLER_TOKEN ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadExpDate = '/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE.'/'.EXPIRE_DATETIME.'/';
        $linkHeadExpDate .= $orderBy === EXPIRE_DATETIME ? $orderDirRev : DESC;
        $linkHeadExpDate .= $closeUrl;
        $classHeadExpDate = $orderBy === EXPIRE_DATETIME ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $mailsForPage . $searchQuery;
        $baseLinkPagination = '/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE."/$orderBy/$orderDir/";
        
        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';
        
        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';
        
        /* return data */
        return [
            ORDER_BY => $orderBy,
            SEARCH => $search,
            SEARCH_QUERY => $searchQuery,
            PAGE => $page,
            ROWS_FOR_PAGE => $mailsForPage,
            TOT_PENDING_MAILS => $totEmails,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            LINK_HEAD.PENDING_EMAIL_ID => $linkHeadId,
            CLASS_HEAD.PENDING_EMAIL_ID => $classHeadId,
            LINK_HEAD.USERNAME => $linkHeadUsername,
            CLASS_HEAD.USERNAME => $classHeadUsername,
            LINK_HEAD.NEW_EMAIL => $linkHeadNewEmail,
            CLASS_HEAD.NEW_EMAIL => $classHeadNewEmail,
            LINK_HEAD.ENABLER_TOKEN => $linkHeadToken,
            CLASS_HEAD.ENABLER_TOKEN => $classHeadToken,
            LINK_HEAD.EXPIRE_DATETIME => $linkHeadExpDate,
            CLASS_HEAD.EXPIRE_DATETIME => $classHeadExpDate,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            EMAILS => $pendEmailModel->getPendingEmails($orderBy, $orderDir, $search, $start, $mailsForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl",
            SEND_EMAIL_LINK => getSendEmailLink($canSendEmails)
        ];
    }

    /* function to get data of roles list */
    public function getRolesListData(string $orderBy, string $orderDir, int $page, int $rolesForPage): array {
        /* init user model */
        $rolesModel = new Role($this->conn);
        
        /* count tot pending mails */
        $totRoles = $rolesModel->countRoles();
        
        /* calc users for page and n. pages */
        $rolesForPage = in_array($rolesForPage, ROWS_FOR_PAGE_LIST) ? $rolesForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totRoles/$rolesForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $rolesForPage * ($page - 1);
        $nlinkPagination = LINK_PAGINATION;
        
        /* calc start and stop page of pagination */
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
        
        /* get order by and order direction */
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : ASC;
        $orderBy = in_array($orderBy, ROLES_ORDER_BY_LIST) ? $orderBy : ROLE_ID;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        
//         /* set search query and the closer of url */
//         $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
//         $searchQuery";
        $closeUrl = "/$page/$rolesForPage";
        
        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.ROLE_ID.'/';
        $linkHeadId .= $orderBy === ROLE_ID ? $orderDirRev : ASC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === ROLE_ID ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head username */
        $linkHeadRoleName = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.ROLE.'/';
        $linkHeadRoleName .= $orderBy === ROLE ? $orderDirRev : DESC;
        $linkHeadRoleName .= $closeUrl;
        $classHeadRoleName = $orderBy === ROLE ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head new email */
        $linkHeadCanCreateUser ='/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_CREATE_USER.'/';
        $linkHeadCanCreateUser .= $orderBy === CAN_CREATE_USER ? $orderDirRev : DESC;
        $linkHeadCanCreateUser .= $closeUrl;
        $classHeadCanCreateUser = $orderBy === CAN_CREATE_USER ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanUpdateUser = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_UPDATE_USER.'/';
        $linkHeadCanUpdateUser .= $orderBy === CAN_UPDATE_USER ? $orderDirRev : DESC;
        $linkHeadCanUpdateUser .= $closeUrl;
        $classHeadCanUpdateUser = $orderBy === ENABLER_TOKEN ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanDeleteUser = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_DELETE_USER.'/';
        $linkHeadCanDeleteUser .= $orderBy === CAN_DELETE_USER ? $orderDirRev : DESC;
        $linkHeadCanDeleteUser .= $closeUrl;
        $classHeadCanDeleteUser = $orderBy === CAN_DELETE_USER ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanChangePassword = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_CHANGE_PASSWORD.'/';
        $linkHeadCanChangePassword .= $orderBy === CAN_CHANGE_PASSWORD ? $orderDirRev : DESC;
        $linkHeadCanChangePassword .= $closeUrl;
        $classHeadCanChangePassword = $orderBy === CAN_CHANGE_PASSWORD ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanGenerateRsa = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_GENERATE_RSA.'/';
        $linkHeadCanGenerateRsa .= $orderBy === CAN_GENERATE_RSA ? $orderDirRev : DESC;
        $linkHeadCanGenerateRsa .= $closeUrl;
        $classHeadCanGenerateRsa = $orderBy === CAN_GENERATE_RSA ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanGenerateSitemap = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_GENERATE_SITEMAP.'/';
        $linkHeadCanGenerateSitemap .= $orderBy === CAN_GENERATE_SITEMAP ? $orderDirRev : DESC;
        $linkHeadCanGenerateSitemap .= $closeUrl;
        $classHeadCanGenerateSitemap = $orderBy === CAN_GENERATE_SITEMAP ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanChangeSettings = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_CHANGE_SETTINGS.'/';
        $linkHeadCanChangeSettings .= $orderBy === CAN_CHANGE_SETTINGS ? $orderDirRev : DESC;
        $linkHeadCanChangeSettings .= $closeUrl;
        $classHeadCanChangesettings = $orderBy === CAN_CHANGE_SETTINGS ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanSendEmail = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_SEND_EMAIL.'/';
        $linkHeadCanSendEmail .= $orderBy === CAN_SEND_EMAIL ? $orderDirRev : DESC;
        $linkHeadCanSendEmail .= $closeUrl;
        $classHeadCanSendEmail = $orderBy === CAN_SEND_EMAIL ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadCanViewTables = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE.'/'.CAN_VIEW_TABLES.'/';
        $linkHeadCanViewTables .= $orderBy === CAN_VIEW_TABLES ? $orderDirRev : DESC;
        $linkHeadCanViewTables .= $closeUrl;
        $classHeadCanViewTables = $orderBy === CAN_VIEW_TABLES ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $rolesForPage;
        $baseLinkPagination = '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE."/$orderBy/$orderDir/";
        
        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';
        
        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';
        
        /* return data */
        return [
            ORDER_BY => $orderBy,
            PAGE => $page,
            ROWS_FOR_PAGE => $rolesForPage,
            TOT_ROLES => $totRoles,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            LINK_HEAD.ROLE_ID => $linkHeadId,
            CLASS_HEAD.ROLE_ID => $classHeadId,
            LINK_HEAD.ROLE => $linkHeadRoleName,
            CLASS_HEAD.ROLE => $classHeadRoleName,
            LINK_HEAD.CAN_CREATE_USER => $linkHeadCanCreateUser,
            CLASS_HEAD.CAN_CREATE_USER => $classHeadCanCreateUser,
            LINK_HEAD.CAN_UPDATE_USER => $linkHeadCanUpdateUser,
            CLASS_HEAD.CAN_UPDATE_USER => $classHeadCanUpdateUser,
            LINK_HEAD.CAN_DELETE_USER => $linkHeadCanDeleteUser,
            CLASS_HEAD.CAN_DELETE_USER => $classHeadCanDeleteUser,
            LINK_HEAD.CAN_CHANGE_PASSWORD => $linkHeadCanChangePassword,
            CLASS_HEAD.CAN_CHANGE_PASSWORD => $classHeadCanChangePassword,
            LINK_HEAD.CAN_GENERATE_RSA => $linkHeadCanGenerateRsa,
            CLASS_HEAD.CAN_GENERATE_RSA => $classHeadCanGenerateRsa,
            LINK_HEAD.CAN_GENERATE_SITEMAP => $linkHeadCanGenerateSitemap,
            CLASS_HEAD.CAN_GENERATE_SITEMAP => $classHeadCanGenerateSitemap,
            LINK_HEAD.CAN_CHANGE_SETTINGS => $linkHeadCanChangeSettings,
            CLASS_HEAD.CAN_CHANGE_SETTINGS => $classHeadCanChangesettings,
            LINK_HEAD.CAN_SEND_EMAIL => $linkHeadCanSendEmail,
            CLASS_HEAD.CAN_SEND_EMAIL => $classHeadCanSendEmail,
            LINK_HEAD.CAN_VIEW_TABLES => $linkHeadCanViewTables,
            CLASS_HEAD.CAN_VIEW_TABLES => $classHeadCanViewTables,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            ROLES => $rolesModel->getRoles($orderBy, $orderDir, $start, $rolesForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/"
        ];
    }

    /* function to get data of sessions list */
    public function geSessionsListData(string $orderBy, string $orderDir, int $page, int $sessionsForPage, string $search): array {
        /* init user model */
        $sessionModel = new Session($this->conn);
        
        /* count tot pending mails */
        $totSessions = $sessionModel->countAllSessions($search);
        
        /* calc users for page and n. pages */
        $sessionsForPage = in_array($sessionsForPage, ROWS_FOR_PAGE_LIST) ? $sessionsForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totSessions/$sessionsForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $sessionsForPage * ($page - 1);
        $nlinkPagination = LINK_PAGINATION;
        
        /* calc start and stop page of pagination */
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
        
        /* get order by and order direction */
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $orderBy = in_array($orderBy, SESSIONS_ORDER_BY_LIST) ? $orderBy : SESSION_ID;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        
        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
        $closeUrl = "/$page/$sessionsForPage$searchQuery";
        
        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE.'/'.SESSION_ID.'/';
        $linkHeadId .= $orderBy === SESSION_ID ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === SESSION_ID ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head username */
        $linkHeadUsername = '/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE.'/'.USERNAME.'/';
        $linkHeadUsername .= $orderBy === USERNAME ? $orderDirRev : DESC;
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === USERNAME ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head new email */
        $linkHeadIpAddr ='/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE.'/'.IP_ADDRESS.'/';
        $linkHeadIpAddr .= $orderBy === IP_ADDRESS ? $orderDirRev : DESC;
        $linkHeadIpAddr .= $closeUrl;
        $classHeadIpAddr = $orderBy === IP_ADDRESS ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head token */
        $linkHeadToken = '/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE.'/'.SESSION_TOKEN.'/';
        $linkHeadToken .= $orderBy === SESSION_TOKEN ? $orderDirRev : DESC;
        $linkHeadToken .= $closeUrl;
        $classHeadToken = $orderBy === SESSION_TOKEN ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadExpDate = '/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE.'/'.EXPIRE_DATETIME.'/';
        $linkHeadExpDate .= $orderBy === EXPIRE_DATETIME ? $orderDirRev : DESC;
        $linkHeadExpDate .= $closeUrl;
        $classHeadExpDate = $orderBy === EXPIRE_DATETIME ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $sessionsForPage . $searchQuery;
        $baseLinkPagination = '/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE."/$orderBy/$orderDir/";
        
        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';
        
        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';
        
        /* return data */
        return [
            ORDER_BY => $orderBy,
            SEARCH => $search,
            SEARCH_QUERY => $searchQuery,
            PAGE => $page,
            ROWS_FOR_PAGE => $sessionsForPage,
            TOT_SESSIONS => $totSessions,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            LINK_HEAD.SESSION_ID => $linkHeadId,
            CLASS_HEAD.SESSION_ID => $classHeadId,
            LINK_HEAD.USERNAME => $linkHeadUsername,
            CLASS_HEAD.USERNAME => $classHeadUsername,
            LINK_HEAD.IP_ADDRESS => $linkHeadIpAddr,
            CLASS_HEAD.IP_ADDRESS => $classHeadIpAddr,
            LINK_HEAD.SESSION_TOKEN => $linkHeadToken,
            CLASS_HEAD.SESSION_TOKEN => $classHeadToken,
            LINK_HEAD.EXPIRE_DATETIME => $linkHeadExpDate,
            CLASS_HEAD.EXPIRE_DATETIME => $classHeadExpDate,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            SESSIONS => $sessionModel->getSessions($orderBy, $orderDir, $search, $start, $sessionsForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl",
        ];
    }

    /* function to get data of password reset requests list */
    public function gePassResetReqListData(string $orderBy, string $orderDir, int $page, int $sessionsForPage, string $search): array {
        /* init user model */
        $passResReqModel = new PasswordResetRequest($this->conn);
        
        /* count tot pending mails */
        $totReq = $passResReqModel->countAllPasswordResetReq($search);
        
        /* calc users for page and n. pages */
        $sessionsForPage = in_array($sessionsForPage, ROWS_FOR_PAGE_LIST) ? $sessionsForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totReq/$sessionsForPage);
        $maxPages = $maxPages < 1 ? 1 : $maxPages;
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $sessionsForPage * ($page - 1);
        
        /* calc start and stop page of pagination */
        $startPage = $page - intdiv(LINK_PAGINATION, 2);
        $startPage = (int) $startPage > ($maxPages - LINK_PAGINATION) ? $maxPages - LINK_PAGINATION : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + LINK_PAGINATION;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
        
        /* get order by and order direction */
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $orderBy = in_array($orderBy, SESSIONS_ORDER_BY_LIST) ? $orderBy : PASSWORD_RESET_REQ_ID;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        
        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
        $closeUrl = "/$page/$sessionsForPage$searchQuery";
        
        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE.'/'.PASSWORD_RESET_REQ_ID.'/';
        $linkHeadId .= $orderBy === PASSWORD_RESET_REQ_ID ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === PASSWORD_RESET_REQ_ID ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head username */
        $linkHeadUsername = '/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE.'/'.USERNAME.'/';
        $linkHeadUsername .= $orderBy === USERNAME ? $orderDirRev : DESC;
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === USERNAME ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head new email */
        $linkHeadIpAddr ='/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE.'/'.IP_ADDRESS.'/';
        $linkHeadIpAddr .= $orderBy === IP_ADDRESS ? $orderDirRev : DESC;
        $linkHeadIpAddr .= $closeUrl;
        $classHeadIpAddr = $orderBy === IP_ADDRESS ? "fas fa-sort-$orderDirClass" : '';
        
        /* link and class for head token */
        $linkHeadToken = '/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE.'/'.PASSWORD_RESET_TOKEN.'/';
        $linkHeadToken .= $orderBy === PASSWORD_RESET_TOKEN ? $orderDirRev : DESC;
        $linkHeadToken .= $closeUrl;
        $classHeadToken = $orderBy === PASSWORD_RESET_TOKEN ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head token */
        $linkHeadExpDate = '/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE.'/'.EXPIRE_DATETIME.'/';
        $linkHeadExpDate .= $orderBy === EXPIRE_DATETIME ? $orderDirRev : DESC;
        $linkHeadExpDate .= $closeUrl;
        $classHeadExpDate = $orderBy === EXPIRE_DATETIME ? "fas fa-sort-$orderDirClass" : '';
        
        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $sessionsForPage . $searchQuery;
        $baseLinkPagination = '/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE."/$orderBy/$orderDir/";
        
        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? DISABLED : '';
        
        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? DISABLED : '';
        
        /* return data */
        return [
            ORDER_BY => $orderBy,
            SEARCH => $search,
            SEARCH_QUERY => $searchQuery,
            PAGE => $page,
            ROWS_FOR_PAGE => $sessionsForPage,
            TOT_REQ => $totReq,
            MAX_PAGES => $maxPages,
            START_PAGE => $startPage,
            STOP_PAGE => $stopPage,
            LINK_HEAD.PASSWORD_RESET_REQ_ID => $linkHeadId,
            CLASS_HEAD.PASSWORD_RESET_REQ_ID => $classHeadId,
            LINK_HEAD.USERNAME => $linkHeadUsername,
            CLASS_HEAD.USERNAME => $classHeadUsername,
            LINK_HEAD.IP_ADDRESS => $linkHeadIpAddr,
            CLASS_HEAD.IP_ADDRESS => $classHeadIpAddr,
            LINK_HEAD.PASSWORD_RESET_TOKEN => $linkHeadToken,
            CLASS_HEAD.PASSWORD_RESET_TOKEN => $classHeadToken,
            LINK_HEAD.EXPIRE_DATETIME => $linkHeadExpDate,
            CLASS_HEAD.EXPIRE_DATETIME => $classHeadExpDate,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            REQUESTS => $passResReqModel->getPassResetRequests($orderBy, $orderDir, $search, $start, $sessionsForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl",
        ];
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    private function getPaginationData() {
        
    }
}
