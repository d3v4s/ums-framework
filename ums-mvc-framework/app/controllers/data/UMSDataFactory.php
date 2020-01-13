<?php
namespace app\controllers\data;

use app\models\User;
use app\models\Role;
use \DateTime;
use \PDO;

/**
 * Class data factory, used for generate
 * and manage the data of response of user
 * management system
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSDataFactory extends DataFactory {

    protected function __construct(PDO $conn = NULL) {
        parent::__construct($conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to get data of users list */
    public function getUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search): array {
        /* get app config and init user model */
//         $appConfig = $this->appConfig['app'];
        $user = new User($this->conn);

        /* count user */
        $totUsers = $user->countUsers($search);
//         $usersForPageList = USERS_FOR_PAGE_LIST;
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
            TOT_USER => $totUsers,
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
//             'usersForPageList' => $usersForPageList,
            BASE_LINK_PAGIN => $baseLinkPagination,
            CLOSE_LINK_PAGIN => $closeUrl,
//             'viewAddFakeUsers' => $appConfig['addFakeUsersPage'],
            USERS => $user->getUsersAndRole($orderBy, $orderDir, $search, $start, $usersForPage),
            BASE_LINK_USER_FOR_PAGE => "$baseLinkPagination$page/",
            SEARCH_ACTION => "{$baseLinkPagination}1$closeUrl"
        ];
    }

    /* function to get data of user */
    public function getUserData($user): array {
//         /* new email vars */
//         $messageNewEmail = '';
//         $viewNewEmail = FALSE;
//         /* if is set a new email, then show with message */
//         if (isset($user->new_email)) {
//             $viewNewEmail = TRUE;
//             $messageNewEmail = isset($user->token_confirm_email) ? '<br>Wait confirmed' : '';
//         }

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
//             if (isset($user->token_account_enabler)) $messageEnable .= '<br>Wait confirmed';
        }

//         /* wrong passwords vars */
//         $messageWrongPass = '';
//         $viewDateTimeResetWrongPass = FALSE;
//         /* if user have wrong passwords, then show the message */
//         if (isset($user->datetime_reset_wrong_password)) {
//             $viewDateTimeResetWrongPass = TRUE;
//             if (new DateTime($user->datetime_reset_wrong_password) < new DateTime()) $messageWrongPass = '<br>Wrong passwords expired';
//             $user->datetime_reset_wrong_password = date($this->appConfig['app']['datetimeFormat'], strtotime($user->datetime_reset_wrong_password));
//         }
//         $messageLockUser = $user->n_locks >= $this->appConfig['app']['maxLocks'] ? '<br>Max limits of locks reached' : '';

        /* init message */
        $messageLockUser = '';
        /* if user has a lock, then set message and format the date */
        if (($isLock = (isset($user->{EXPIRE_LOCK}) && new DateTime($user->{EXPIRE_LOCK}) > new DateTime()))) {
            $user->{EXPIRE_LOCK} = date($this->appConfig[APP][DATETIME_FORMAT], strtotime($user->{EXPIRE_LOCK}));
            $messageLockUser = '<br>Temporarily locked';
        }

        /* format the date */
        $user->{REGISTRATION_DATETIME} = date($this->appConfig[APP][DATETIME_FORMAT], strtotime($user->{REGISTRATION_DATETIME}));

        /* return data */
        return [
            USER => $user,
            CSRF_UNLOCK_USER => generateToken(CSRF_UNLOCK_USER),
            CSRF_DELETE_USER => generateToken(CSRF_DELETE_USER),
            CLASS_ENABLE_ACC => $classEnabledAccount,
            NO_ESCAPE.MESSAGE_ENABLE_ACC => $messageEnable,
            IS_LOCK => $isLock,
            NO_ESCAPE.MESSAGE_LOCK_ACC => $messageLockUser
//             'tokenDeleteNewEmail' => generateToken('csrfDeleteNewEmail'),
//             'tokenResetWrongPass' => generateToken('csrfResetWrongPass'),
//             'viewNewEmail' => $viewNewEmail,
//             '_messageNewEmail' => $messageNewEmail,
//             'viewDateTimeResetWrongPass' => $viewDateTimeResetWrongPass,
//             '_messageWrongPassword' => $messageWrongPass,
//             'maxWrongPass' => $this->appConfig['app']['maxWrongPassword'],
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
            TOKEN => generateToken(CSRF_UPDATE_USER),
            ROLES => $role->getNameAndIdRoles(),
            NO_ESCAPE.ENABLED => ($usr->enabled) ? CHECKED : ''
        ];
    }

    /* function to get data for new user */
    public function getNewUserData(): array {
        /* init role model */
        $role = new Role($this->conn);
        /* return data */
        return [
            TOKEN => generateToken(CSRF_NEW_USER),
            ROLES => $role->getNameAndIdRoles()
        ];
    }
}

