<?php
namespace app\controllers\data;

use \PDO;
use \DateTime;
use \app\models\User;

/**
 * Class data factory, used for generate
 * and manage the data of response of user
 * management system
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSDataFactory extends DataFactory {
    protected $userRoles = [];
    protected $orderByList = [];
    protected $orderDirList = [];

    protected function __construct(array $appConfig, PDO $conn = NULL) {
        parent::__construct($appConfig, $conn);
        $this->userRoles = getList('userRoles');
        $this->orderByList = getList('orderBy');
        $this->orderDirList = getList('orderDir');
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to set user roles */
    public function setUserRoles(array $userRoles) {
        $this->userRoles = $userRoles;
    }

    /* function to set list of order by */
    public function setOrderByList(array $orderByList) {
        $this->orderByList = $orderByList;
    }

    /* function to set list of order directions */
    public function setOrderDirList(array $orderDirList) {
        $this->orderDirList = $orderDirList;
    }

    /* function to get data of users list */
    public function getUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search): array {
        /* get app config and init user model */
        $appConfig = $this->appConfig['app'];
        $user = new User($this->conn, $this->appConfig);

        /* count user */
        $totUsers = $user->countUsers($search);
        /* calc users for page and n. pages */
        $usersForPageList = explode(',', $appConfig['usersForPageList']);
        $usersForPage = in_array($usersForPage, $usersForPageList) ? $usersForPage : 10;
        $maxPages = (int) ceil($totUsers/$usersForPage);
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $usersForPage * ($page - 1);
        $nlinkPagination = $appConfig['linkPagination'] - 1;

        /* calc start and stop page of pagination */
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;

        /* get order by and order direction */
        $orderDir = strtoupper($orderDir);
        $orderDir = in_array($orderDir, $this->orderDirList) ? $orderDir : 'DESC';
        $orderBy = in_array($orderBy, $this->orderByList) ? $orderBy : 'id';
        $orderDirRev = $orderDir === 'ASC' ? 'desc' : 'asc';
        /* class of order direction */
        $orderDirClass = $orderDir === 'ASC' ? 'down' : 'up';

        /* set search query and the closer of url */
        $searchQuery = empty($search) ? '' : '?search=' . $search;
        $closeUrl = '/' . $page . '/' . $usersForPage . $searchQuery;

        /* link and class for head id */
        $linkHeadId = '/ums/users/id/';
        $linkHeadId .= $orderBy === 'id' ? $orderDirRev : 'desc';
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === 'id' ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head name */
        $linkHeadName = '/ums/users/name/';
        $linkHeadName .= $orderBy === 'name' ? $orderDirRev : 'desc';
        $linkHeadName .= $closeUrl;
        $classHeadName = $orderBy === 'name' ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head username */
        $linkHeadUsername = '/ums/users/username/';
        $linkHeadUsername .= $orderBy === 'username' ? $orderDirRev : 'desc';
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === 'username' ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head email */
        $linkHeadEmail = '/ums/users/email/';
        $linkHeadEmail .= $orderBy === 'email' ? $orderDirRev : 'desc';
        $linkHeadEmail .= $closeUrl;
        $classHeadEmail = $orderBy === 'email' ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head enabled */
        $linkHeadEnabled = '/ums/users/enabled/';
        $linkHeadEnabled .= $orderBy === 'enabled' ? $orderDirRev : 'desc';
        $linkHeadEnabled .= $closeUrl;
        $classHeadEnabled = $orderBy === 'enabled' ? "fas fa-sort-$orderDirClass" : '';

        /* link and class for head roletype */
        $linkHeadRole = '/ums/users/roletype/';
        $linkHeadRole .= $orderBy === 'roletype' ? $orderDirRev : 'desc';
        $linkHeadRole .= $closeUrl;
        $classHeadRole = $orderBy === 'roletype' ? "fas fa-sort-$orderDirClass" : '';

        /* set url closer and the base of pagination link */
        $closeUrl = '/' . $usersForPage . $searchQuery;
        $baseLinkPagination = '/ums/users/' . $orderBy . '/' . $orderDir . '/';

        /* set link and class of pagination arrow left */
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? 'disabled': '';

        /* set link and class of pagination arrow right */
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? 'disabled': '';

        /* return data */
        return [
            'orderBy' => $orderBy,
            'search' => $search,
            'searchQuery' => $searchQuery,
            'page' => $page,
            'usersForPage' => $usersForPage,
            'totUsers' => $totUsers,
            'maxPages' => $maxPages,
            'startPage' => $startPage,
            'stopPage' => $stopPage,
            'linkHeadId' => $linkHeadId,
            'classHeadId' => $classHeadId,
            'linkHeadName' => $linkHeadName,
            'classHeadName' => $classHeadName,
            'linkHeadUsername' => $linkHeadUsername,
            'classHeadUsername' => $classHeadUsername,
            'linkHeadEmail' => $linkHeadEmail,
            'classHeadEmail' => $classHeadEmail,
            'linkHeadEnabled' => $linkHeadEnabled,
            'classHeadEnabled' => $classHeadEnabled,
            'linkHeadRole' => $linkHeadRole,
            'classHeadRole' => $classHeadRole,
            'linkPaginationArrowLeft' => $linkPaginationArrowLeft,
            'classPaginationArrowLeft' => $classPaginationArrowLeft,
            'linkPaginationArrowRight' => $linkPaginationArrowRight,
            'classPaginationArrowRight' => $classPaginationArrowRight,
            'usersForPageList' => $usersForPageList,
            'baseLinkPagination' => $baseLinkPagination,
            'closeUrlPagination' => $closeUrl,
            'viewAddFakeUsers' => $appConfig['addFakeUsersPage'],
            'users' => $user->getUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            'baseLinkUfp' => $baseLinkPagination . $page . '/',
            'searchAction' => $baseLinkPagination . '1' . $closeUrl
        ];
    }

    /* function to get data of user */
    public function getUserData($user): array {
        /* new email vars */
        $messageNewEmail = '';
        $viewNewEmail = FALSE;
        /* if is set a new email, then show with message */
        if (isset($user->new_email)) {
            $viewNewEmail = TRUE;
            $messageNewEmail = isset($user->token_confirm_email) ? '<br>Wait confirmed' : '';
        }

        /* enable account class */
        $classEnabledAccount = 'text-';
        if ($user->enabled) {
            /* if account is enable */
            $classEnabledAccount .= 'success';
            $messageEnable = 'ENABLED';
        } else {
            /* if account is disabled */
            $classEnabledAccount .= 'danger';
            $messageEnable = 'DISABLED';
            if (isset($user->token_account_enabler)) $messageEnable .= '<br>Wait confirmed';
        }

        /* wrong passwords vars */
        $messageWrongPass = '';
        $viewDateTimeResetWrongPass = FALSE;
        /* if user have wrong passwords, then show the message */
        if (isset($user->datetime_reset_wrong_password)) {
            $viewDateTimeResetWrongPass = TRUE;
            if (new DateTime($user->datetime_reset_wrong_password) < new DateTime()) $messageWrongPass = '<br>Wrong passwords expired';
            $user->datetime_reset_wrong_password = date($this->appConfig['app']['datetimeFormat'], strtotime($user->datetime_reset_wrong_password));
        }

        /* lock user vars */
        $messageLockUser = $user->n_locks >= $this->appConfig['app']['maxLocks'] ? '<br>Max limits of locks reached' : '';
        $messageUnlockUser = '';
        $viewDateTimeUnlockUser = FALSE;
        /* if user has a lock, then show the message */
        if (isset($user->datetime_unlock_user)) {
            $viewDateTimeUnlockUser = TRUE;
            $messageUnlockUser .= (new DateTime($user->datetime_unlock_user) < new DateTime()) ? '<br>Unlocked' : '<br>Temporarily locked';
            $user->datetime_unlock_user = date($this->appConfig['app']['datetimeFormat'], strtotime($user->datetime_unlock_user));
        }

        /* format the registration date */
        $user->registration_day = date($this->appConfig['app']['dateFormat'], strtotime($user->registration_day));

        /* return data */
        return [
            'user' => $user,
            'tokenDeleteNewEmail' => generateToken('csrfDeleteNewEmail'),
            'tokenResetWrongPass' => generateToken('csrfResetWrongPass'),
            'tokenResetLockUser' => generateToken('csrfResetLockUser'),
            'tokenDeleteUser' => generateToken('csrfDeleteUser'),
            'classEnabledAccount' => $classEnabledAccount,
            '_messageEnable' => $messageEnable,
            'viewNewEmail' => $viewNewEmail,
            '_messageNewEmail' => $messageNewEmail,
            'viewDateTimeResetWrongPass' => $viewDateTimeResetWrongPass,
            '_messageWrongPassword' => $messageWrongPass,
            'viewDateTimeUnlockUser' => $viewDateTimeUnlockUser,
            '_messageLockUser' => $messageLockUser,
            'maxWrongPass' => $this->appConfig['app']['maxWrongPassword'],
            '_messageUnlockUser' => $messageUnlockUser
        ];
    }

    /* function to get data for update user */
    public function getUpdateUserData($username): array {
        /* init user model and get user */
        $user = new User($this->conn, $this->appConfig);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);

        /* return data */
        return [
            'user' => $usr,
            'token' => generateToken('csrfUMSUpdateUser'),
            'userRoles' => $this->userRoles,
            '_checkedEnableAccount' => ($usr->enabled) ? 'checked="checked"' : ''
        ];
    }

    /* function to get data for new user */
    public function getNewUserData(): array {
        /* return data */
        return [
            'token' => generateToken(),
            'userRoles' => $this->userRoles
        ];
    }
}

