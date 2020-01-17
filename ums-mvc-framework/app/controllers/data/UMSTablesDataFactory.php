<?php

namespace app\controllers\data;

use app\models\User;
use \PDO;
use app\models\DeletedUser;
use app\models\PendingUser;

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
    public function getUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole): array {
        /* init user model */
        $userModel = new User($this->conn);

        /* count user */
        $totUsers = $userModel->countUsers($search);
        /* calc users for page and n. pages */
        $usersForPage = in_array($usersForPage, ROWS_FOR_PAGE_LIST) ? $usersForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totUsers/$usersForPage);
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
            LINK_HEAD_ID => $linkHeadId,
            CLASS_HEAD_ID => $classHeadId,
            LINK_HEAD_NAME => $linkHeadName,
            CLASS_HEAD_NAME => $classHeadName,
            LINK_HEAD_USERNAME => $linkHeadUsername,
            CLASS_HEAD_USERNAME => $classHeadUsername,
            LINK_HEAD_EMAIL => $linkHeadEmail,
            CLASS_HEAD_EMAIL => $classHeadEmail,
            LINK_HEAD_ENABLED => $linkHeadEnabled,
            CLASS_HEAD_ENABLED => $classHeadEnabled,
            LINK_HEAD_ROLE => $linkHeadRole,
            CLASS_HEAD_ROLE => $classHeadRole,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            USERS => $userModel->getUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl",
            VIEW_ROLE => $canViewRole
        ];
    }

    /* function to get data of deleted users list */
    public function getDeletedUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole): array {
        /* init user model */
        $delUserModel = new DeletedUser($this->conn);
        
        /* count user */
        $totUsers = $delUserModel->countDeletedUsers($search);
        /* calc users for page and n. pages */
        $usersForPage = in_array($usersForPage, ROWS_FOR_PAGE_LIST) ? $usersForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totUsers/$usersForPage);
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
        $orderBy = in_array($orderBy, DELETED_USERS_ORDER_BY_LIST) ? $orderBy : USER_ID_FRGN;
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        /* class of order direction */
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        
        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?'.SEARCH."=$search";
        $closeUrl = "/$page/$usersForPage$searchQuery";
        
        /* link and class for head id */
        $linkHeadId = '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE.'/'.USER_ID_FRGN.'/';
        $linkHeadId .= $orderBy === USER_ID_FRGN ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === USER_ID_FRGN ? "fas fa-sort-$orderDirClass" : '';
        
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
            LINK_HEAD_ID => $linkHeadId,
            CLASS_HEAD_ID => $classHeadId,
            LINK_HEAD_NAME => $linkHeadName,
            CLASS_HEAD_NAME => $classHeadName,
            LINK_HEAD_USERNAME => $linkHeadUsername,
            CLASS_HEAD_USERNAME => $classHeadUsername,
            LINK_HEAD_EMAIL => $linkHeadEmail,
            CLASS_HEAD_EMAIL => $classHeadEmail,
            LINK_HEAD_ROLE => $linkHeadRole,
            CLASS_HEAD_ROLE => $classHeadRole,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            USERS => $delUserModel->getDeletedUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl", 
            VIEW_ROLE => $canViewRole
        ];
    }

    /* function to get data of deleted users list */
    public function getPendingUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole): array {
        /* init user model */
        $pendUserModel = new PendingUser($this->conn);
        
        /* count user */
        $totUsers = $pendUserModel->countAllPendingUsers($search);
        /* calc users for page and n. pages */
        $usersForPage = in_array($usersForPage, ROWS_FOR_PAGE_LIST) ? $usersForPage : DEFAULT_ROWS_FOR_PAGE;
        $maxPages = (int) ceil($totUsers/$usersForPage);
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

        /* link and class for head roletype */
        $linkHeadToken = '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE.'/'.ENABLER_TOKEN.'/';
        $linkHeadToken .= $orderBy === ENABLER_TOKEN ? $orderDirRev : DESC;
        $linkHeadToken .= $closeUrl;
        $classHeadToken = $orderBy === ENABLER_TOKEN ? "fas fa-sort-$orderDirClass" : '';

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
            LINK_HEAD_ID => $linkHeadId,
            CLASS_HEAD_ID => $classHeadId,
            LINK_HEAD_NAME => $linkHeadName,
            CLASS_HEAD_NAME => $classHeadName,
            LINK_HEAD_USERNAME => $linkHeadUsername,
            CLASS_HEAD_USERNAME => $classHeadUsername,
            LINK_HEAD_EMAIL => $linkHeadEmail,
            CLASS_HEAD_EMAIL => $classHeadEmail,
            LINK_HEAD_ROLE => $linkHeadRole,
            CLASS_HEAD_ROLE => $classHeadRole,
            LINK_HEAD_TOKEN => $linkHeadToken,
            CLASS_HEAD_TOKEN => $classHeadToken,
            LINK_PAGIN_ARROW_LEFT => $linkPaginationArrowLeft,
            CLASS_PAGIN_ARROW_LEFT => $classPaginationArrowLeft,
            LINK_PAGIN_ARROW_RIGHT => $linkPaginationArrowRight,
            CLASS_PAGIN_ARROW_RIGHT => $classPaginationArrowRight,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
            USERS => $pendUserModel->getPendingUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_ROWS_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl",
            VIEW_ROLE => $canViewRole
        ];
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    private function getPaginationData() {
        
    }
}
