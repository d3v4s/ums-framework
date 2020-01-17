<?php
namespace app\controllers;

use app\controllers\data\UMSTablesDataFactory;
use \PDO;

/**
 * Class controller for manage the ums db tables
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSTablesController extends UMSBaseController {
    protected $table;

    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_TABLES_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## SHOW FUNCTIONS ########## */

    public function showTable(string $table=USERS_TABLE, string $orderBy=NULL, string $orderDir=DESC, int $page=1, int $rowsForPage=DEFAULT_ROWS_FOR_PAGE) {
        $this->redirectOrFailIfCanNotViewTables();

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
            default:
                $this->showMessageAndExit('INVALID TABLE', TRUE);
                break;
        }

        
        
    }
    /* function to view the user list page */
    public function showUsersList(string $orderBy=NULL, string $orderDir=DESC, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->redirectOrFailIfCanNotUpdateUser();

        $orderBy = $orderBy ?? USER_ID;

        /* set current location */
        $this->isUsersList = TRUE;
        $this->table = USERS_TABLE;

        /* get search query */
        $search = $_GET[SEARCH] ?? '';

        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->conn)->getUsersListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canViewRole());
        $this->content = view(getPath('tables','users'), $data);
    }

    /* function to view the deleted user list page */
    public function showDeletedUsersList(string $orderBy=NULL, string $orderDir=DESC, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->redirectOrFailIfCanNotUpdateUser();

        $orderBy = $orderBy ?? USER_ID_FRGN;
        /* set current location */
        $this->table = DELETED_USER_TABLE;
        
        /* get search query */
        $search = $_GET[SEARCH] ?? '';
        
        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->conn)->getDeletedUsersListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canViewRole());
        $this->content = view(getPath('tables','deleted-users'), $data);
    }

    /* function to view the pending user list page */
    public function showPendingUsersList(string $orderBy=NULL, string $orderDir=DESC, int $page=1, int $usersForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->redirectOrFailIfCanNotUpdateUser();
        
        $orderBy = $orderBy ?? PENDING_USER_ID;
        /* set current location */
        $this->table = PENDING_USERS_TABLE;
        
        /* get search query */
        $search = $_GET[SEARCH] ?? '';
        
        /* get data from data factory and show page */
        $data = UMSTablesDataFactory::getInstance($this->conn)->getPendingUsersListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canViewRole());
        $this->content = view(getPath('tables','pending-users'), $data);
    }
    
    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    private function redirectOrFailIfCanNotViewTables() {
        if (!$this->canViewTables()) $this->switchFailResponse();
    }
}
