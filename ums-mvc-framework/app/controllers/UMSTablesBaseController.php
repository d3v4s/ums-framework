<?php
namespace app\controllers;

use \PDO;

/**
 * Class controller for manage the ums db tables
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSTablesBaseController extends UMSBaseController {
    protected $table;
    protected $isAdvanceSearch = FALSE;

    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_TABLES_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    protected function redirectOrFailIfCanNotViewTables() {
        if (!$this->canViewTables()) $this->switchFailResponse();
    }
}
