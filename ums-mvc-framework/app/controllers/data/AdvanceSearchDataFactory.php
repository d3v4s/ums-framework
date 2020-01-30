<?php

namespace app\controllers\data;

use app\models\PasswordResetRequest;
use app\models\PendingEmail;
use app\models\DeletedUser;
use app\models\PendingUser;
use app\models\Session;
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
class AdvanceSearchDataFactory extends PaginationDataFactory {

    protected function __construct(PDO $conn=NULL) {
        parent::__construct($conn);
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
        } else $data = $this->getDefaultData();

        return $data;
    }

    /* ########## TABLES FUNCTIONS ########## */

    /* fuction to get default data */
    public function getDefaultData(): array {
        return array_merge($this->getPaginationDefaultData(),[
            SEARCH_ACTION => '/'.ADVANCE_SEARCH_ROUTE,
            TOT_ROWS => 0,
            HEAD_TABLE_LIST => [],
            RESULT => [],
            TABLES_LIST => $this->getTablesList(),
            SEARCH_PARAMS => $this->getSearchParam(),
            PARAM_VALUES => [],
            TABLE => ''
            
        ]);
    }

    /* function to get data of users list */
    public function getUsersData(string $orderBy=NULL, string $orderDir, int $page, int $usersForPage, array $searchParam): array {
        /* get order by and order direction */
        $usersOrderByList = [
            USER_ID,
            NAME,
            USERNAME,
            EMAIL,
            ROLE,
            ENABLED,
            REGISTRATION_DATETIME,
            EXPIRE_LOCK
        ];
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, $usersOrderByList, USER_ID);

        $searchData = [
            USER_ID => $searchParam[USER_ID] ?? NULL,
            NAME => $searchParam[NAME] ?? NULL,
            USERNAME => $searchParam[USERNAME] ?? NULL,
            EMAIL => $searchParam[EMAIL] ?? NULL,
            ROLE => $searchParam[ROLE] ?? NULL,
            ENABLED => $searchParam[ENABLED] ?? NULL,
            REGISTRATION_DATETIME => $searchParam[REGISTRATION_DATETIME] ?? NULL,
            EXPIRE_LOCK => $searchParam[EXPIRE_LOCK] ?? NULL
        ];

        /* init user model and count users */
        $userModel = new User($this->conn);
        $totUsers = $userModel->countAdvanceSearchUsers($searchData);

        /* get search query */
        $searchQuery = $this->getAdvanceSearchQuery($searchData);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, '/'.ADVANCE_SEARCH_ROUTE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], $usersOrderByList, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($searchQuery, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $usersForPage * ($page - 1);
        $data[USERS] = $userModel->getUsers($orderBy, $orderDir, $search, $start, $usersForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_USERS] = $totUsers;
        $data[VIEW_ROLE] = $canViewRole;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        /* return data */
        return $data;
    }

    /* function to get data of deleted users list */
    public function getDeletedUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model and count users */
        $delUserModel = new DeletedUser($this->conn);
        $totUsers = $delUserModel->countDeletedUsers($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, DELETED_USERS_ORDER_BY_LIST, USER_ID);

        /* get search query */
        $searchQuery = $this->getSearchQuery($search);

        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, '/'.UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(DELETED_USER_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], DELETED_USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $usersForPage * ($page - 1);
        $data[USERS] = $delUserModel->getDeletedUsers($orderBy, $orderDir, $search, $start, $usersForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_USERS] = $totUsers;
        $data[VIEW_ROLE] = $canViewRole;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        return $data;
    }

    /* function to get data of deleted users list */
    public function getPendingUsersListData(string $orderBy, string $orderDir, int $page, int $usersForPage, string $search, bool $canViewRole, bool $canSendEmails): array {
        /* init user model */
        $pendUserModel = new PendingUser($this->conn);

        /* count user */
        $totUsers = $pendUserModel->countAllPendingUsers($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, PENDING_USERS_ORDER_BY_LIST, PENDING_USER_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $usersForPage, $totUsers, '/'.UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_USERS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PENDING_USERS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $usersForPage * ($page - 1);
        $data[USERS] = $pendUserModel->getPendingUsers($orderBy, $orderDir, $search, $start, $usersForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_USERS] = $totUsers;
        $data[VIEW_ROLE] = $canViewRole;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        return $data;
    }

    /* function to get data of pending emails list */
    public function getPendingEmailsListData(string $orderBy, string $orderDir, int $page, int $mailsForPage, string $search, bool $canSendEmails): array {
        /* init user model */
        $pendEmailModel = new PendingEmail($this->conn);
        
        /* count tot pending mails */
        $totEmails = $pendEmailModel->countAllPendingEmails($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, PENDING_EMAILS_ORDER_BY_LIST, PENDING_EMAIL_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $mailsForPage, $totEmails, '/'.UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PENDING_EMAILS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PENDING_EMAILS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $mailsForPage * ($page - 1);
        $data[EMAILS] = $pendEmailModel->getPendingEmails($orderBy, $orderDir, $search, $start, $mailsForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_PENDING_MAILS] = $totEmails;
        $data[SEND_EMAIL_LINK] = getSendEmailLink($canSendEmails);
        return $data;
    }

    /* function to get data of roles list */
    public function getRolesListData(string $orderBy, string $orderDir, int $page, int $rolesForPage): array {
        /* init user model */
        $rolesModel = new Role($this->conn);
        
        /* count tot pending mails */
        $totRoles = $rolesModel->countRoles();

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, ROLES_ORDER_BY_LIST, ROLE_ID);
        
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $rolesForPage, $totRoles, '/'.UMS_TABLES_ROUTE.'/'.ROLES_TABLE);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(ROLES_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], ROLES_ORDER_BY_LIST));
        /* add user data */
        $start = $rolesForPage * ($page - 1);
        $data[ROLES] = $rolesModel->getRoles($orderBy, $orderDir, $start, $rolesForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_ROLES] = $totRoles;
        return $data;
    }

    /* function to get data of sessions list */
    public function geSessionsListData(string $orderBy, string $orderDir, int $page, int $sessionsForPage, string $search): array {
        /* init user model */
        $sessionModel = new Session($this->conn);
        
        /* count tot pending mails */
        $totSessions = $sessionModel->countAllSessions($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, SESSIONS_ORDER_BY_LIST, SESSION_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $sessionsForPage, $totSessions, '/'.UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(SESSIONS_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], SESSIONS_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $sessionsForPage * ($page - 1);
        $data[SESSIONS] = $sessionModel->getSessions($orderBy, $orderDir, $search, $start, $sessionsForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_SESSIONS] = $totSessions;
        return $data;
    }

    /* function to get data of password reset requests list */
    public function gePassResetReqListData(string $orderBy, string $orderDir, int $page, int $requestsForPage, string $search): array {
        /* init user model */
        $passResReqModel = new PasswordResetRequest($this->conn);
        
        /* count tot pending mails */
        $totReq = $passResReqModel->countAllPasswordResetReq($search);

        /* get order by and order direction */
        $orderDir = $this->getOrderDirection($orderDir);
        $orderBy = $this->getOrderBy($orderBy, PASS_RESET_REQ_ORDER_BY_LIST, PASSWORD_RESET_REQ_ID);
        
        /* get search query */
        $searchQuery = $this->getSearchQuery($search);
        
        /* get pagination data */
        $data = $this->getPaginationData($orderBy, $orderDir, $page, $requestsForPage, $totReq, '/'.UMS_TABLES_ROUTE.'/'.PASSWORD_RESET_REQ_TABLE, $searchQuery);
        /* get and merge table head data */
        $data = array_merge($data, $this->getLinkAndClassHeadTable(PASSWORD_RESET_REQ_TABLE, $orderBy, $orderDir, $page, $data[ROWS_FOR_PAGE], PASS_RESET_REQ_ORDER_BY_LIST, $searchQuery));
        /* get and merge search data */
        $data = array_merge($data, $this->getSearchData($search, $data[BASE_LINK_PAGIN], $data[CLOSE_LINK_PAGIN]));
        /* add user data */
        $start = $requestsForPage * ($page - 1);
        $data[REQUESTS] = $passResReqModel->getPassResetRequests($orderBy, $orderDir, $search, $start, $requestsForPage);
        /* add other property and return data */
        $data[ORDER_BY] = $orderBy;
        $data[TOT_REQ] = $totReq;
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
        $data = [];
        foreach ($columnList as $col) {
            $data[LINK_HEAD.$col] = '/'.ADVANCE_SEARCH_ROUTE."/$col/";
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
                ROLE => [
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
                    TYPE => 'datetime'
                ],
                EXPIRE_LOCK => [
                    VALUE => 'Expire lock datetime',
                    TYPE => 'datetime'
                ]
            ],
            DELETED_USER_TABLE => [
                DELETED_USER_ID => [
                    VALUE => 'Deleted user id',
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
                ROLE => [
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
                    TYPE => 'datetime'
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
                ROLE => [
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
                    TYPE => 'datetime'
                ],
                EXPIRE_DATETIME => [
                    VALUE => 'Expire datetime',
                    TYPE => 'datetime'
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
                    TYPE => 'datetime'
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
                    TYPE => 'datetime'
                ]
            ],
            PASSWORD_RESET_REQ_TABLE => [
                PASSWORD_RESET_REQ_TABLE => [
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
                    TYPE => 'datetime'
                ]
            ]
        ];
    }
}
