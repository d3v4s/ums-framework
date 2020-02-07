<?php
namespace app\controllers;

use app\controllers\data\AdvanceSearchDataFactory;
use \PDO;

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
        $this->sendFailIfCanNotViewTables();

        /* set location */
        $this->isAdvanceSearch = TRUE;
        /* get search params */
        $searchParam = $_GET ?? [];

        /* get data from data factory */
        $data = AdvanceSearchDataFactory::getInstance($this->lang[DATA], $this->conn)->getAdvanceSearchData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);

        array_push($this->jsSrcs, [SOURCE => '/js/utils/ums/advance-search.js']);
        /* show page */
        $this->content = view(getPath('ums','advance-search'), $data);
    }
}
