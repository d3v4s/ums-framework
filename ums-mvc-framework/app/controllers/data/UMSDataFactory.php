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
        $totUsers = $userModel->countUsers();

        /* init deleted user model and count deleted users */
        $delUserModel = new DeletedUser($this->conn);
        $totDeleted = $delUserModel->countDeletedUsers();

        /* init pending user model and count pending users */
        $pendingUserModel = new PendingUser($this->conn);
        $totPendUsers = $pendingUserModel->countPendingUsers();

        /* init pending mails modele and count all pending mails */
        $pendMailModel = new PendingEmail($this->conn);
        $totPendMails = $pendMailModel->countPendingMails();

        /* init session model and count sessions */
        $sessionModel = new Session($this->conn);
        $totSessions = $sessionModel->countSessions();

        /* return data */
        return [
            TOT_USERS => $totUsers,
            TOT_DELETED_USERS => $totDeleted,
            TOT_PENDING_USERS => $totPendUsers,
            TOT_PENDING_MAILS => $totPendMails,
            TOT_SESSIONS => $totSessions
        ];
    }

    /* function to get data of users list */
    public function getUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search): array {
        /* init user model */
        $userModel = new User($this->conn);

        /* count user */
        $totUsers = $userModel->countUsers($search);
        /* calc users for page and n. pages */
        $usersForPage = in_array($usersForPage, USERS_FOR_PAGE_LIST) ? $usersForPage : DEFAULT_USERS_FOR_PAGE;
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
        $linkHeadId = '/'.USERS_LIST_ROUTE.'/'.USER_ID.'/';
        $linkHeadId .= $orderBy === USER_ID ? $orderDirRev : DESC;
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === USER_ID ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head name */
        $linkHeadName = '/'.USERS_LIST_ROUTE.'/'.NAME.'/';
        $linkHeadName .= $orderBy === NAME ? $orderDirRev : DESC;
        $linkHeadName .= $closeUrl;
        $classHeadName = $orderBy === NAME ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head username */
        $linkHeadUsername = '/'.USERS_LIST_ROUTE.'/'.USERNAME.'/';
        $linkHeadUsername .= $orderBy === USERNAME ? $orderDirRev : DESC;
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === USERNAME ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head email */
        $linkHeadEmail ='/'.USERS_LIST_ROUTE.'/'.EMAIL.'/';
        $linkHeadEmail .= $orderBy === EMAIL ? $orderDirRev : DESC;
        $linkHeadEmail .= $closeUrl;
        $classHeadEmail = $orderBy === EMAIL ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head enabled */
        $linkHeadEnabled = '/'.USERS_LIST_ROUTE.'/'.ENABLED.'/';
        $linkHeadEnabled .= $orderBy === ENABLED ? $orderDirRev : DESC;
        $linkHeadEnabled .= $closeUrl;
        $classHeadEnabled = $orderBy === ENABLED ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head roletype */
        $linkHeadRole = '/'.USERS_LIST_ROUTE.'/'.ROLE.'/';
        $linkHeadRole .= $orderBy === ROLE ? $orderDirRev : DESC;
        $linkHeadRole .= $closeUrl;
        $classHeadRole = $orderBy === ROLE ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $usersForPage . $searchQuery;
        $baseLinkPagination = '/'.USERS_LIST_ROUTE."/$orderBy/$orderDir/";

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
            USERS_FOR_PAGE => $usersForPage,
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
            USERS => $userModel->getUsersAndRole($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_USER_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl"
        ];
    }

    /* function to get data of user */
    public function getUserData($user, $datetimeFormat): array {
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

        /* return data */
        return [
            USER => $user,
            IS_LOCK => $isLock,
            TOKEN => generateToken(CSRF_DELETE_USER),
            CLASS_ENABLE_ACC => $classEnabledAccount,
            NO_ESCAPE.MESSAGE_ENABLE_ACC => $messageEnable,
            NO_ESCAPE.MESSAGE_LOCK_ACC => $messageLockUser
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

