<?php
namespace app\controllers;

use \PDO;
use app\controllers\data\AdvanceSearchDataFactory;

/**
 * Class controller for advance searchs 
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AdvanceSearchController extends UMSTablesBaseController {
    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_TABLES_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout=UMS_TABLES_LAYOUT);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ########## SHOW FUNCTIONS ########## */

    /* function to show the advance search */
    public function showAdvanceSearch(string $orderBy=NULL, string $orderDir=DESC, int $page=1, int $rowsForPage=DEFAULT_ROWS_FOR_PAGE) {
        /* redirect */
        $this->redirectOrFailIfCanNotViewTables();

        /* set location */
        $this->isAdvanceSearch = TRUE;
        /* get search params */
        $searchParam = $_GET ?? [];

        /* init data factory */
        $data = AdvanceSearchDataFactory::getInstance($this->conn)->getAdvanceSearchData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);
        
        array_push($this->jsSrcs, [SOURCE => '/js/utils/ums/advance-search.js']);
        /* get data from data factory and show page */
//         $data = UMSTablesDataFactory::getInstance($this->conn)->getUsersListData($orderBy, $orderDir, $page, $usersForPage, $search, $this->canViewRole(), $this->canSendEmails());
        $this->content = view(getPath('ums','advance-search'), $data);

        
    }
}
