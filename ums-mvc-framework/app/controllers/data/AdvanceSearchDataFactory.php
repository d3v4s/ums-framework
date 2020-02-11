<?php

namespace app\controllers\data;

use app\models\PasswordResetRequest;
use app\models\PendingEmail;
use app\models\DeletedUser;
use app\models\PendingUser;
use app\models\Session;
use app\models\User;
use \PDO;
use app\core\Router;

/**
 * Class data factory, used for generate
 * and manage the data of response of user
 * management system
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AdvanceSearchDataFactory extends PaginationDataFactory {

    protected function __construct(array $langData, PDO $conn=NULL) {
        parent::__construct($langData, $conn);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    public function getAdvanceSearchData(string $orderBy=NULL, string $orderDir, int $page, int $rowsForPage, array $searchParam): array {
        /* check if is set table */ 
        if (isset($searchParam[TABLE]) && !empty($searchParam[TABLE])) {
            /* switch view */
            switch ($searchParam[TABLE]) {
                case USERS_TABLE:
                    $data = $this->getUsersData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);
                    break;
                case DELETED_USER_TABLE:
                    $data = $this->getDeletedUsersData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);
                    break;
                case PENDING_USERS_TABLE:
                    $data = $this->getPendingUsersData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);
                    break;
                case PENDING_EMAILS_TABLE:
                    $data = $this->getPendingEmailsData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);
                    break;
                case SESSIONS_TABLE:
                    $data = $this->getSessionsData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);
                    break;
                case PASSWORD_RESET_REQ_TABLE:
                    $data = $this->getPassResetReqData($orderBy, $orderDir, $page, $rowsForPage, $searchParam);
                    break;
                default:
                    $this->showMessageAndExit('INVALID TABLE', TRUE);
                    break;
            }
        } else $data = $this->getDefaultData();

        return $data;
    }

    /* ########## TABLES FUNCTIONS ########## */

    /* fuction to get default data */
    public function getDefaultData(): array {
        return array_merge($this->getPaginationDefaultData(),[
            SEARCH_ACTION => Router::getRoute('AdvanceSearchController', 'showAdvanceSearch'),
            TOT_ROWS => 0,
            COLUMN_LIST => [],
            RESULT => [],
            TABLES_LIST => $this->getTablesList(),
            SEARCH_PARAMS => $this->getSearchParam(),
            PARAM_VALUES => [],
            TABLE => ''
            
        ]);
    }

    /* function to get data of users list */
    public function getUsersData(string $orderBy=NULL, string $orderDir, int $page, int $usersForPage, array $searchParam): array {
        /* init user model and get order by list */
        $userModel = new User($this->conn);
        $orderByList = $userModel->getColList();
        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, $orderByList, USER_ID);

        /* set search data */
        $searchData = [
            USER_ID => $searchParam[USER_ID] ?? NULL,
            NAME => $searchParam[NAME] ?? NULL,
            USERNAME => $searchParam[USERNAME] ?? NULL,
            EMAIL => $searchParam[EMAIL] ?? NULL,
            ROLE_ID_FRGN => $searchParam[ROLE_ID_FRGN] ?? NULL,
            ENABLED => $searchParam[ENABLED] ?? NULL,
            REGISTRATION_DATETIME => $searchParam[REGISTRATION_DATETIME] ?? NULL,
            EXPIRE_LOCK => $searchParam[EXPIRE_LOCK] ?? NULL
        ];
        /* filter null value */
        $searchData = filterNullVal($searchData);
        /* count users */
        $totUsers = $userModel->countAdvanceSearchUsers($searchData);

        /* get search query */
        $dataQuery = $searchData;
        $dataQuery[TABLE] = USERS_TABLE;
        $searchQuery = $this->getAdvanceSearchQuery($dataQuery);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, Router::getRoute('AdvanceSearchController', 'showAdvanceSearch'), $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], $orderByList, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($searchQuery, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[RESULT] = $userModel->getUsersAdvanceSearch($orderBy, $orderDir, $start, $data[ROWS_FOR_PAGE], $searchData);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[COLUMN_LIST] = $orderByList;
        $data[TABLES_LIST] = $this->getTablesList();
        $data[SEARCH_PARAMS] = $this->getSearchParam();
        $data[PARAM_VALUES] = $searchData;
        $data[TOT_ROWS] = $totUsers;
        /* return data */
        return $data;
    }

    /* function to get data of deleted users list */
    public function getDeletedUsersData(string $orderBy=NULL, string $orderDir, int $page, int $usersForPage, array $searchParam): array {
        /* init deleted user model and get order by list */
        $delUserModel = new DeletedUser($this->conn);
        $orderByList = $delUserModel->getColList();

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, $orderByList, USER_ID);

        /* set search data */
        $searchData = [
            DELETED_USER_ID => $searchParam[DELETED_USER_ID] ?? NULL,
            USER_ID => $searchParam[USER_ID] ?? NULL,
            NAME => $searchParam[NAME] ?? NULL,
            USERNAME => $searchParam[USERNAME] ?? NULL,
            EMAIL => $searchParam[EMAIL] ?? NULL,
            ROLE_ID_FRGN => $searchParam[ROLE_ID_FRGN] ?? NULL,
            REGISTRATION_DATETIME => $searchParam[REGISTRATION_DATETIME] ?? NULL,
            DELETE_DATETIME => $searchParam[DELETE_DATETIME] ?? NULL
        ];
        /* filter null value */
        $searchData = filterNullVal($searchData);

        /* count users */
        $totUsers = $delUserModel->countAdvanceSearchDeletedUsers($searchData);

        /* get search query */
        $dataQuery = $searchData;
        $dataQuery[TABLE] = DELETED_USER_TABLE;
        $searchQuery = $this->getAdvanceSearchQuery($dataQuery);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, Router::getRoute('AdvanceSearchController', 'showAdvanceSearch'), $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(DELETED_USER_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], $orderByList, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($searchQuery, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[RESULT] = $delUserModel->getDeletedUsersAdvanceSearch($orderBy, $orderDir, $start, $data[ROWS_FOR_PAGE], $searchData);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[COLUMN_LIST] = $orderByList;
        $data[TABLES_LIST] = $this->getTablesList();
        $data[SEARCH_PARAMS] = $this->getSearchParam();
        $data[PARAM_VALUES] = $searchData;
        $data[TOT_ROWS] = $totUsers;
        return $data;
    }

    /* function to get data of deleted users list */
    public function getPendingUsersData(string $orderBy=NULL, string $orderDir, int $page, int $usersForPage, array $searchParam): array {
        /* init pending user model and get order by list */
        $pendUserModel = new PendingUser($this->conn);
        $orderByList = $pendUserModel->getColList();

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, $orderByList, PENDING_USER_ID);

        /* set search data */
        $searchData = [
            PENDING_USER_ID => $searchParam[PENDING_USER_ID] ?? NULL,
            USER_ID_FRGN => $searchParam[USER_ID_FRGN] ?? NULL,
            NAME => $searchParam[NAME] ?? NULL,
            USERNAME => $searchParam[USERNAME] ?? NULL,
            EMAIL => $searchParam[EMAIL] ?? NULL,
            ROLE_ID_FRGN => $searchParam[ROLE_ID_FRGN] ?? NULL,
            REGISTRATION_DATETIME => $searchParam[REGISTRATION_DATETIME] ?? NULL,
            EXPIRE_DATETIME => $searchParam[EXPIRE_DATETIME] ?? NULL
        ];
        /* filter null value */
        $searchData = filterNullVal($searchData);
        /* count user */
        $totUsers = $pendUserModel->countAdvanceSearchPendingUsers($searchData);
        
        /* get search query */
        $queryData = $searchData;
        $queryData[TABLE] = PENDING_USERS_TABLE;
        $searchQuery = $this->getAdvanceSearchQuery($queryData);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, Router::getRoute('AdvanceSearchController', 'showAdvanceSearch'), $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], $orderByList, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($searchQuery, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[RESULT] = $pendUserModel->getPendingUsersAdvanceSearch($orderBy, $orderDir, $start, $data[ROWS_FOR_PAGE], $searchData);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[COLUMN_LIST] = $orderByList;
        $data[TABLES_LIST] = $this->getTablesList();
        $data[SEARCH_PARAMS] = $this->getSearchParam();
        $data[PARAM_VALUES] = $searchData;
        $data[TOT_ROWS] = $totUsers;
        return $data;
    }

    /* function to get data of pending emails list */
    public function getPendingEmailsData(string $orderBy=NULL, string $orderDir, int $page, int $mailsForPage, array $searchParam): array {
        /* init user model */
        $pendEmailModel = new PendingEmail($this->conn);
        $orderByList = $pendEmailModel->getColList();

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, $orderByList, PENDING_EMAIL_ID);

        /* set search data */
        $searchData = [
            PENDING_EMAIL_ID => $searchParam[PENDING_EMAIL_ID] ?? NULL,
            USER_ID_FRGN => $searchParam[USER_ID_FRGN] ?? NULL,
            NEW_EMAIL => $searchParam[NEW_EMAIL] ?? NULL,
            EXPIRE_DATETIME => $searchParam[EXPIRE_DATETIME] ?? NULL
        ];
        /* filter null value */
        $searchData = filterNullVal($searchData);

        /* count tot pending mails */
        $totEmails = $pendEmailModel->countAdvanceSearchPendingEmails($searchData);

        /* get search query */
        $queryData = $searchData;
        $queryData[TABLE] = PENDING_EMAILS_TABLE;
        $searchQuery = $this->getAdvanceSearchQuery($queryData);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $mailsForPage, $totEmails, Router::getRoute('AdvanceSearchController', 'showAdvanceSearch'), $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_EMAILS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], $orderByList, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($searchQuery, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[RESULT] = $pendEmailModel->getPendingEmailsAdvanceSearch($orderBy, $orderDir,$start, $data[ROWS_FOR_PAGE], $searchData);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[COLUMN_LIST] = $orderByList;
        $data[TABLES_LIST] = $this->getTablesList();
        $data[SEARCH_PARAMS] = $this->getSearchParam();
        $data[PARAM_VALUES] = $searchData;
        $data[TOT_ROWS] = $totEmails;
        return $data;
    }

    /* function to get data of sessions list */
    public function getSessionsData(string $orderBy=NULL, string $orderDir, int $page, int $sessionsForPage, array $searchParam): array {
        /* init user model */
        $sessionModel = new Session($this->conn);
        $orderByList = $sessionModel->getColList();

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, $orderByList, SESSION_ID);

        /* set search data */
        $searchData = [
            SESSION_ID => $searchParam[PENDING_EMAIL_ID] ?? NULL,
            USER_ID_FRGN => $searchParam[USER_ID_FRGN] ?? NULL,
            IP_ADDRESS => $searchParam[IP_ADDRESS] ?? NULL,
            EXPIRE_DATETIME => $searchParam[EXPIRE_DATETIME] ?? NULL
        ];
        /* filter null value */
        $searchData = filterNullVal($searchData);

        /* count tot pending mails */
        $totSessions = $sessionModel->countAdvanceSearchSessions($searchData);

        /* get search query */
        $queryData = $searchData;
        $queryData[TABLE] = SESSIONS_TABLE;
        $searchQuery = $this->getAdvanceSearchQuery($queryData);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $sessionsForPage, $totSessions, Router::getRoute('AdvanceSearchController', 'showAdvanceSearch'), $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(SESSIONS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], $orderByList, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($searchQuery, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[RESULT] = $sessionModel->getSessionsAdvanceSearch($orderBy, $orderDir, $start, $data[ROWS_FOR_PAGE], $searchData);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[COLUMN_LIST] = $orderByList;
        $data[TABLES_LIST] = $this->getTablesList();
        $data[SEARCH_PARAMS] = $this->getSearchParam();
        $data[PARAM_VALUES] = $searchData;
        $data[TOT_ROWS] = $totSessions;
        return $data;
    }

    /* function to get data of password reset requests list */
    public function getPassResetReqData(string $orderBy=NULL, string $orderDir, int $page, int $requestsForPage, array $searchParam): array {
        /* init user model */
        $passResReqModel = new PasswordResetRequest($this->conn);
        $orderByList = $passResReqModel->getColList();
        
        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, $orderByList, PASSWORD_RESET_REQ_ID);

        /* set search data */
        $searchData = [
            PASSWORD_RESET_REQ_ID => $searchParam[PASSWORD_RESET_REQ_ID] ?? NULL,
            USER_ID_FRGN => $searchParam[USER_ID_FRGN] ?? NULL,
            IP_ADDRESS => $searchParam[IP_ADDRESS] ?? NULL,
            EXPIRE_DATETIME => $searchParam[EXPIRE_DATETIME] ?? NULL
        ];
        /* filter null value */
        $searchData = filterNullVal($searchData);

        /* count tot pending mails */
        $totReq = $passResReqModel->countAdvanceSearchPassResReq($searchData);

        /* get search query */
        $queryData = $searchData;
        $queryData[TABLE] = PASSWORD_RESET_REQ_TABLE;
        $searchQuery = $this->getAdvanceSearchQuery($queryData);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $requestsForPage, $totReq, Router::getRoute('AdvanceSearchController', 'showAdvanceSearch'), $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PASSWORD_RESET_REQ_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], $orderByList, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($searchQuery, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $data[ROWS_FOR_PAGE] * ($page - 1);
        $data[RESULT] = $passResReqModel->getPassResReqAdvanceSearch($orderBy, $orderDir, $start, $data[ROWS_FOR_PAGE], $searchData);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[COLUMN_LIST] = $orderByList;
        $data[TABLES_LIST] = $this->getTablesList();
        $data[SEARCH_PARAMS] = $this->getSearchParam();
        $data[PARAM_VALUES] = $searchData;
        $data[TOT_ROWS] = $totReq;
        return $data;
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to get advance search data */
    protected function getSearchData(string $searchQuery, string $baseLinkPagin, string $closeUrl): array {
        return [
            SEARCH_QUERY => $searchQuery,
            SEARCH_PARAMS => $searchQuery,
            SEARCH_ACTION => "{$baseLinkPagin}1$closeUrl"
        ];
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get link and class for head of table */
    private function getLinkAndClassHeadTable(string $table, string $orderBy, string $orderDir, int $page, int $rowsForPage, array $columnList, string $searchQuery=''): array {
        /* get direction reverse and class */
        $orderDirRev = $orderDir === ASC ? DESC : ASC;
        $orderDirClass = $orderDir === ASC ? ORDER_ASC_CLASS : ORDER_DESC_CLASS;
        $closeUrl = "/$page/$rowsForPage$searchQuery";
        $data = [
            TABLE => $table
        ];
        foreach ($columnList as $col) {
            $data[LINK_HEAD.$col] = "/ums/search/advance/$col/";
            $data[LINK_HEAD.$col] .= $orderBy === $col ? $orderDirRev : DESC;
            $data[LINK_HEAD.$col] .= $closeUrl;
            $data[CLASS_HEAD.$col] = $orderBy === $col ? "fas fa-sort-$orderDirClass" : '';
        }
        return $data;
    }

    /* function to get a advance search query */
    private function getAdvanceSearchQuery(array $searchData): string {
        /* filter data and calc the needly ands */
        $searchData = array_filter($searchData);
        $and = count($searchData)-1;
        /* init query string */
        $query = empty($searchData) ? '' : '?';
        /* iterate search data and create query */
        foreach ($searchData as $key => $val) {
            $query .= "$key=$val";
            if ($and-- > 0) $query .= '&';
        }
        return $query;
    }

    /* function to get search param */
    private function getTablesList(): array {
        return [
            USERS_TABLE => 'Users',
            DELETED_USER_TABLE => 'Deleted users',
            PENDING_USERS_TABLE => 'Pending users',
            PENDING_EMAILS_TABLE => 'Pending emails',
            SESSIONS_TABLE => 'Session',
            PASSWORD_RESET_REQ_TABLE => 'Password reset request'
        ];
    }

    /* function to get search params */
    private function getSearchParam(): array {
        return [
            USERS_TABLE => [
                USER_ID => [
                    VALUE => 'User id',
                    TYPE => 'text'
                ],
                NAME => [
                    VALUE => 'Name',
                    TYPE => 'text'
                ],
                USERNAME => [
                    VALUE => 'Username',
                    TYPE => 'text'
                ],
                EMAIL => [
                    VALUE => 'Email',
                    TYPE => 'text'
                ],
                ROLE_ID_FRGN => [
                    VALUE => 'Role',
                    TYPE => 'select',
                    SELECT_LIST => [
                        USER_ROLE_ID => 'User',
                        EDITOR_ROLE_ID => 'Editor',
                        ADMIN_ROLE_ID => 'Admin'
                    ]
                ],
                ENABLED => [
                    VALUE => 'Enabled',
                    TYPE => 'select',
                    SELECT_LIST => [
                        1 => 'Enabled',
                        0 => 'Disabled'
                    ]
                ],
                REGISTRATION_DATETIME => [
                    VALUE => 'Registration datetime',
                    TYPE => 'date'
                ],
                EXPIRE_LOCK => [
                    VALUE => 'Expire lock datetime',
                    TYPE => 'date'
                ]
            ],
            DELETED_USER_TABLE => [
                DELETED_USER_ID => [
                    VALUE => 'Deleted user id',
                    TYPE => 'text'
                ],
                USER_ID => [
                    VALUE => 'User id',
                    TYPE => 'text'
                ],
                NAME => [
                    VALUE => 'Name',
                    TYPE => 'text'
                ],
                USERNAME => [
                    VALUE => 'Username',
                    TYPE => 'text'
                ],
                EMAIL => [
                    VALUE => 'Email',
                    TYPE => 'text'
                ],
                ROLE_ID_FRGN => [
                    VALUE => 'Role',
                    TYPE => 'select',
                    SELECT_LIST => [
                        USER_ROLE_ID => 'User',
                        EDITOR_ROLE_ID => 'Editor',
                        ADMIN_ROLE_ID => 'Admin'
                    ]
                ],
                REGISTRATION_DATETIME => [
                    VALUE => 'Registration datetime',
                    TYPE => 'date'
                ],
                DELETE_DATETIME => [
                    VALUE => 'Delete datetime',
                    TYPE => 'date'
                ]
            ],
            PENDING_USERS_TABLE => [
                PENDING_USER_ID => [
                    VALUE => 'Pending user id',
                    TYPE => 'text'
                ],
                USER_ID_FRGN => [
                    VALUE => 'User id',
                    TYPE => 'text'
                ],
                NAME => [
                    VALUE => 'Name',
                    TYPE => 'text'
                ],
                USERNAME => [
                    VALUE => 'Username',
                    TYPE => 'text'
                ],
                EMAIL => [
                    VALUE => 'Email',
                    TYPE => 'text'
                ],
                ROLE_ID_FRGN => [
                    VALUE => 'Role',
                    TYPE => 'select',
                    SELECT_LIST => [
                        USER_ROLE_ID => 'User',
                        EDITOR_ROLE_ID => 'Editor',
                        ADMIN_ROLE_ID => 'Admin'
                    ]
                ],
                REGISTRATION_DATETIME => [
                    VALUE => 'Registration datetime',
                    TYPE => 'date'
                ],
                EXPIRE_DATETIME => [
                    VALUE => 'Expire datetime',
                    TYPE => 'date'
                ]
            ],
            PENDING_EMAILS_TABLE => [
                PENDING_EMAIL_ID => [
                    VALUE => 'Pending email id',
                    TYPE => 'text'
                ],
                USER_ID_FRGN => [
                    VALUE => 'User id',
                    TYPE => 'text'
                ],
                NEW_EMAIL => [
                    VALUE => 'New email',
                    TYPE => 'text'
                ],
                EXPIRE_DATETIME => [
                    VALUE => 'Expire datetime',
                    TYPE => 'date'
                ]
            ],
            SESSIONS_TABLE => [
                SESSION_ID => [
                    VALUE => 'Session id',
                    TYPE => 'text'
                ],
                USER_ID_FRGN => [
                    VALUE => 'User id',
                    TYPE => 'text'
                ],
                IP_ADDRESS => [
                    VALUE => 'IP address',
                    TYPE => 'text'
                ],
                EXPIRE_DATETIME => [
                    VALUE => 'Expire datetime',
                    TYPE => 'date'
                ]
            ],
            PASSWORD_RESET_REQ_TABLE => [
                PASSWORD_RESET_REQ_ID => [
                    VALUE => 'Password reset request id',
                    TYPE => 'text'
                ],
                USER_ID_FRGN => [
                    VALUE => ' User id',
                    TYPE => 'text'
                ],
                IP_ADDRESS => [
                    VALUE => 'IP address',
                    TYPE => 'text'
                ],
                EXPIRE_DATETIME => [
                    VALUE => 'Expire datetime',
                    TYPE => 'date'
                ]
            ]
        ];
    }
}
