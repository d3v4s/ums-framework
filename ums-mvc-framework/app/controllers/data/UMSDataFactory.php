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

    public function setUserRoles(array $userRoles) {
        $this->userRoles = $userRoles;
    }

    public function setOrderByList(array $orderByList) {
        $this->orderByList = $orderByList;
    }

    public function setOrderDirList(array $orderDirList) {
        $this->orderDirList = $orderDirList;
    }

    public function getUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search): array {
        $appConfig = $this->appConfig['app'];
        $user = new User($this->conn, $this->appConfig);

        $totUsers = $user->countUsers($search);
        $usersForPageList = explode(',', $appConfig['usersForPageList']);
        $usersForPage = in_array($usersForPage, $usersForPageList) ? $usersForPage : 10;
        $maxPages = (int) ceil($totUsers/$usersForPage);
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $usersForPage * ($page - 1);
        $nlinkPagination = $appConfig['linkPagination'] - 1;

        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
        
        $orderDir = strtoupper($orderDir);
        $orderDir = in_array($orderDir, $this->orderDirList) ? $orderDir : 'DESC';
        $orderBy = in_array($orderBy, $this->orderByList) ? $orderBy : 'id';
        $orderDirRev = $orderDir === 'ASC' ? 'desc' : 'asc';
        
        $orderDirClass = $orderDir === 'ASC' ? 'down' : 'up';
        $searchQuery = empty($search) ? '' : '?search=' . $search;
        $closeUrl = '/' . $page . '/' . $usersForPage . $searchQuery;
        
        $linkHeadId = '/ums/users/id/';
        $linkHeadId .= $orderBy === 'id' ? $orderDirRev : 'desc';
        $linkHeadId .= $closeUrl;
        $classHeadId = $orderBy === 'id' ? "fas fa-sort-$orderDirClass" : '';
        
        $linkHeadName = '/ums/users/name/';
        $linkHeadName .= $orderBy === 'name' ? $orderDirRev : 'desc';
        $linkHeadName .= $closeUrl;
        $classHeadName = $orderBy === 'name' ? "fas fa-sort-$orderDirClass" : '';
        
        $linkHeadUsername = '/ums/users/username/';
        $linkHeadUsername .= $orderBy === 'username' ? $orderDirRev : 'desc';
        $linkHeadUsername .= $closeUrl;
        $classHeadUsername = $orderBy === 'username' ? "fas fa-sort-$orderDirClass" : '';
        
        $linkHeadEmail = '/ums/users/email/';
        $linkHeadEmail .= $orderBy === 'email' ? $orderDirRev : 'desc';
        $linkHeadEmail .= $closeUrl;
        $classHeadEmail = $orderBy === 'email' ? "fas fa-sort-$orderDirClass" : '';
        
        $linkHeadEnabled = '/ums/users/enabled/';
        $linkHeadEnabled .= $orderBy === 'enabled' ? $orderDirRev : 'desc';
        $linkHeadEnabled .= $closeUrl;
        $classHeadEnabled = $orderBy === 'enabled' ? "fas fa-sort-$orderDirClass" : '';
        
        $linkHeadRole = '/ums/users/roletype/';
        $linkHeadRole .= $orderBy === 'roletype' ? $orderDirRev : 'desc';
        $linkHeadRole .= $closeUrl;
        $classHeadRole = $orderBy === 'roletype' ? "fas fa-sort-$orderDirClass" : '';
        
        $closeUrl = '/' . $usersForPage . $searchQuery;
        $baseLinkPagination = '/ums/users/' . $orderBy . '/' . $orderDir . '/';
        
        $linkPaginationArrowLeft = $baseLinkPagination . ($page-1) . $closeUrl;
        $classPaginationArrowLeft = $page === 1 ? 'disabled': '';
        
        $linkPaginationArrowRight = $baseLinkPagination . ($page+1) . $closeUrl;
        $classPaginationArrowRight = $page === $maxPages ? 'disabled': '';
        
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

    public function getUserData($user): array {
        $messageNewEmail = '';
        $viewNewEmail = FALSE;
        if (isset($user->new_email)) {
            $viewNewEmail = TRUE;
            $messageNewEmail = isset($user->token_confirm_email) ? '<br>Wait confirmed' : '';
        }
        $classEnabledAccount = 'text-';
        if ($user->enabled) {
            $classEnabledAccount .= 'success';
            $messageEnable = 'ENABLED';
        } else {
            $classEnabledAccount .= 'danger';
            $messageEnable = 'DISABLED';
            if (isset($user->token_account_enabler)) $messageEnable .= '<br>Wait confirmed';
        }
        $messageWrongPass = '';
        $viewDateTimeResetWrongPass = FALSE;
        if (isset($user->datetime_reset_wrong_password)) {
            $viewDateTimeResetWrongPass = TRUE;
            if (new DateTime($user->datetime_reset_wrong_password) < new DateTime()) $messageWrongPass = '<br>Wrong passwords expired';
            $user->datetime_reset_wrong_password = date($this->appConfig['app']['datetimeFormat'], strtotime($user->datetime_reset_wrong_password));
        }
        $messageLockUser = $user->n_locks >= $this->appConfig['app']['maxLocks'] ? '<br>Max limits of locks reached' : '';
        $messageUnlockUser = '';
        $viewDateTimeUnlockUser = FALSE;
        if (isset($user->datetime_unlock_user)) {
            $viewDateTimeUnlockUser = TRUE;
            $messageUnlockUser .= (new DateTime($user->datetime_unlock_user) < new DateTime()) ? '<br>Unlocked' : '<br>Temporarily locked';
            $user->datetime_unlock_user = date($this->appConfig['app']['datetimeFormat'], strtotime($user->datetime_unlock_user));
        }
        $user->registration_day = date($this->appConfig['app']['dateFormat'], strtotime($user->registration_day));

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

    public function getUpdateUserData($username): array {
        $user = new User($this->conn, $this->appConfig);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);

        return [
            'user' => $usr,
            'token' => generateToken('csrfUMSUpdateUser'),
            'userRoles' => $this->userRoles,
            '_checkedEnableAccount' => ($usr->enabled) ? 'checked="checked"' : ''
        ];
    }

    public function getNewUserData(): array {
        return [
            'token' => generateToken(),
            'userRoles' => $this->userRoles
        ];
    }
}

