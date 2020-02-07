<?php
namespace app\controllers;

use app\controllers\data\UMSTablesDataFactory;
use \PDO;

/**
 * Class controller for manage the ums db tables
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSTablesController extends UMSTablesBaseController {

    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_TABLES_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## SHOW FUNCTIONS ########## */

    /* SWITCHER */

    /* functio to show request table */
    public function showTable(string $table=USERS_TABLE, string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $rowsForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        /* switch view */
        switch ($table) {
            case USERS_TABLE:
                $this->showUsersList($orderBy, $orderDir, $page, $rowsForPage);
                break;
            case DELETED_USER_TABLE:
                $this->showDeletedUsersList($orderBy, $orderDir, $page, $rowsForPage);
                break;
            case PENDING_USERS_TABLE:
                $this->showPendingUsersList($orderBy, $orderDir, $page, $rowsForPage);
                break;
            case PENDING_EMAILS_TABLE:
                $this->showPendingEmailsList($orderBy, $orderDir, $page, $rowsForPage);
                break;
            case ROLES_TABLE:
                $this->showRolesList($orderBy, $orderDir, $page, $rowsForPage);
                break;
            case SESSIONS_TABLE:
                $this->showSessionsList($orderBy, $orderDir, $page, $rowsForPage);
                break;
            case PASSWORD_RESET_REQ_TABLE:
                $this->showPassResetReqList($orderBy, $orderDir, $page, $rowsForPage);
                break;
            default:
                $this->showMessageAndExit('INVALID TABLE', TRUE);
                break;
        }
    }

    /* functio to show request row of table */
    public function showRow(string $table, string $id) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        /* switch view */
        switch ($table) {
            case USERS_TABLE:
                $this->showUser($id);
                break;
            case DELETED_USER_TABLE:
                $this->showDeletedUser($id);
                break;
            case SESSIONS_TABLE:
                $this->showSession($id);
                break;
            case USER_LOCK_TABLE:
                $this->showLockUser($id);
                break;
            case PENDING_EMAILS_TABLE:
                $this->showPendingEmail($id);
                break;
            case PENDING_USERS_TABLE:
                $this->showPendingUser($id);
                break;
            case PASSWORD_RESET_REQ_TABLE:
                $this->showPassResReq($id);
                break;
            default:
                $this->showMessageAndExit('INVALID TABLE', TRUE);
                break;
        }
    }

    /* TABLES */

    /* function to view the user list page */
    public function showUsersList(string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        $orderBy = $orderBy ?? USER_ID;
        $orderDir = $orderDir ?? DESC;

        /* set current location */
        $this->isUsersList = TRUE;
        $this->table = USERS_TABLE;

        /* get search query */
        $search = $_GET[SEARCH] ?? '';

        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getUsersListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canViewRole(), $this->canSendEmails());
        $this->content = view(getPath('tables','users'), $data);
    }

    /* function to view the deleted user list page */
    public function showDeletedUsersList(string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        $orderBy = $orderBy ?? USER_ID;
        $orderDir = $orderDir ?? DESC;

        /* set current location */
        $this->table = DELETED_USER_TABLE;

        /* get search query */
        $search = $_GET[SEARCH] ?? '';

        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getDeletedUsersListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canViewRole(), $this->canSendEmails());
        $this->content = view(getPath('tables','deleted-users'), $data);
    }

    /* function to view the pending user list page */
    public function showPendingUsersList(string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        $orderBy = $orderBy ?? PENDING_USER_ID;
        $orderDir = $orderDir ?? DESC;

        /* set current location */
        $this->table = PENDING_USERS_TABLE;

        /* get search query */
        $search = $_GET[SEARCH] ?? '';

        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getPendingUsersListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canViewRole(), $this->canSendEmails());
        $this->content = view(getPath('tables','pending-users'), $data);
    }

    /* function to view the pending mails list page */
    public function showPendingEmailsList(string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        $orderBy = $orderBy ?? PENDING_EMAIL_ID;
        $orderDir = $orderDir ?? DESC;

        /* set current location */
        $this->table = PENDING_EMAILS_TABLE;

        /* get search query */
        $search = $_GET[SEARCH] ?? '';

        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->conn)->getPendingEmailsListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canSendEmails());
        $this->content = view(getPath('tables','pending-emails'), $data);
    }

    /* function to view the roles list page */
    public function showRolesList(string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();
        
        $orderBy = $orderBy ?? ROLE_ID;
        $orderDir = $orderDir ?? ASC;
        
        /* set current location */
        $this->table = ROLES_TABLE;
        
        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getRolesListData($orderBy, $orderDir, $page, $usersForPage);
        $this->content = view(getPath('tables','roles'), $data);
    }

    /* function to view the sessions list page */
    public function showSessionsList(string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();
        
        $orderBy = $orderBy ?? SESSION_ID;
        $orderDir = $orderDir ?? DESC;

        /* set current location */
        $this->table = SESSIONS_TABLE;

        /* get search query */
        $search = $_GET[SEARCH] ?? '';
        
        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->geSessionsListData($orderBy, $orderDir, $page, $usersForPage, $search);
        $this->content = view(getPath('tables','sessions'), $data);
    }

    /* function to view the sessions list page */
    public function showPassResetReqList(string $orderBy=NULL, string $orderDir=NULL, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        $orderBy = $orderBy ?? PASSWORD_RESET_REQ_ID;
        $orderDir = $orderDir ?? DESC;

        /* set current location */
        $this->table = PASSWORD_RESET_REQ_TABLE;
        
        /* get search query */
        $search = $_GET[SEARCH] ?? '';
        
        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->gePassResetReqListData($orderBy, $orderDir, $page, $usersForPage, $search);
        $this->content = view(getPath('tables','pass-res-req'), $data);
    }

    /* ROWS */

    /* function to view a user page */
    public function showUser($username) {
        //* redirect */
        $this->sendFailIfCanNotViewTables();
        
        
        /* get data by data factory */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getUserData($username, $this->appConfig[APP][DATETIME_FORMAT], $this->canUpdateUser(), $this->canDeleteUser(), $this->canChangePassword(), $this->canViewRole(), $this->canSendEmails(), $this->canUnlockUser());
        
        /* if user not found, show error message */
        if (!$data[USER]) $this->showMessageAndExit('User not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/delete-user.js'],
            [SOURCE => '/js/utils/ums/locks-reset.js']
        );
        
        /* show page */
        $this->content = view(getPath('tables','user-info'), $data);
    }
    
    /* function to view a user page */
    public function showDeletedUser($id) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        /* get data by data factory  */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getDeletedUserData($id, $this->appConfig[APP][DATETIME_FORMAT], $this->canViewRole(), $this->canSendEmails(), $this->canRestoreUser());
        
        /* if user not found, show error message */
        if (!$data[USER]) $this->showMessageAndExit('Deleted user not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/restore-user.js']
        );
        
        /* show page */
        $this->content = view(getPath('tables','deleted-user-info'), $data);
    }
    
    /* function to view a user page */
    public function showSession($sessionId) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        /* get data by data factory  */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getSessionData($sessionId, $this->appConfig[APP][DATETIME_FORMAT], $this->canSendEmails(), $this->canRemoveSession());
        
        /* if user not found, show error message */
        if (!$data[SESSION]) $this->showMessageAndExit('Session not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/invalidate.js']
        );
        
        /* show page */
        $this->content = view(getPath('tables','session-info'), $data);
    }

    /* function to view a user page */
    public function showLockUser($userId) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();

        /* get data by data factory  */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getUserLocksData($userId, $this->appConfig[APP][DATETIME_FORMAT], $this->canUnlockUser());
        
        
        /* if user not found, show error message */
        if (!$data[USER]) $this->showMessageAndExit('User not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/locks-reset.js']
        );
        
        /* show page */
        $this->content = view(getPath('tables','locks-info'), $data);
    }

    /* function to view a user page */
    public function showPendingEmail($pendingMailId) {
        /* redirect */
        $this->sendFailIfCanNotViewTables();
        
        /* get data by data factory  */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getPendingEmailData($pendingMailId, $this->appConfig[APP][DATETIME_FORMAT], $this->canSendEmails(), $this->canRemoveEnablerToken());
        
        /* if user not found, show error message */
        if (!$data[PENDING]) $this->showMessageAndExit('Pending email not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/resend-enabler-email.js'],
            [SOURCE => '/js/utils/ums/invalidate.js']
        );
        
        /* show page */
        $this->content = view(getPath('tables','pending-email-info'), $data);
    }

    /* function to view a pending user page */
    public function showPendingUser($userId) {
        //* redirect */
        $this->sendFailIfCanNotViewTables();
        
        
        /* get data by data factory */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getPendingUserData($userId, $this->appConfig[APP][DATETIME_FORMAT],$this->canViewRole(), $this->canSendEmails(), $this->canRemoveEnablerToken());
        
        /* if user not found, show error message */
        if (!$data[USER]) $this->showMessageAndExit('User not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/resend-enabler-email.js'],
            [SOURCE => '/js/utils/ums/invalidate.js']
        );
        
        /* show page */
        $this->content = view(getPath('tables','pending-user-info'), $data);
    }

    /* function to view a user page */
    public function showPassResReq($passResReqId) {
        //* redirect */
        $this->sendFailIfCanNotViewTables();
        
        
        /* get data by data factory */
        $data = UMSTablesDataFactory::getInstance($this->lang[DATA], $this->conn)->getPassResReqData($passResReqId, $this->appConfig[APP][DATETIME_FORMAT],$this->canViewRole(), $this->canSendEmails(), $this->canRemoveEnablerToken());
        
        /* if user not found, show error message */
        if (!$data[REQUEST]) $this->showMessageAndExit('Password reset request not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/resend-enabler-email.js'],
            [SOURCE => '/js/utils/ums/invalidate.js']
        );
        
        /* show page */
        $this->content = view(getPath('tables','pass-res-req-info'), $data);
    }
}
