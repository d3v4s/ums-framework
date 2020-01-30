<?php

/* ########### GENERIC CONSTANTS FOR APP ########### */

/* SET DEVELOPER MODE CONSTANTS */
define('DEV', TRUE);
define('FAKE_USERS', TRUE);
define('SHOW_MESSAGE_EXCEPTION', TRUE);

/* GENERIC CONTANTS */
define('DEFAULT_LANG', 'en');
define('LINK_PAGINATION', 6);
define('DEFAULT_PASSWORD', 'ums');
define('DEFAULT_ROWS_FOR_PAGE', 10);
define('PAGE_NOT_FOUND', 'error-404');
define('PAGE_EXCEPTION', 'error-exception');
define('MAX_TIME_UNCONNECTED_LOGIN_SESSION', '30 days');
define('MAX_WRONG_PASSWORDS', 5);
define('MAX_LOCKS', 3);
define('PASS_TRY_TIME', '5 minutes');
define('USER_LOCK_TIME', '2 minutes');
define('MIN_LENGTH_NAME', 4);
define('MAX_LENGTH_NAME', 100);
define('MIN_LENGTH_USERNAME', 3);
define('MAX_LENGTH_USERNAME', 64);
define('MIN_LENGTH_PASS', 8);
define('MAX_LENGTH_PASS', 255);
define('MIN_LENGTH_EMAIL', 5);
define('MAX_LENGTH_EMAIL', 64);
define('ENABLER_LINK_EXPIRE_TIME', '1 day');
define('PASS_RESET_EXPIRE_TIME', '3 hour');
define('RESEND_LOCK_EXPIRE_TIME', '3 minutes');
define('DOMAIN_LOGIN_SESSION_COOCKIE', 'localhost');
define('DEFAULT_SETTING_SECTION', 'app');
define('COOKIE_EXPIRE_DAYS', 30);
define('CSRF_TOKEN_EXPIRE_TIME', '10 minutes');
define('DATE_TIME_ZONE_DEFAULT', 'Europe/Rome');
define('DOUBLE_LOGIN_SESSION_EXPIRE_TIME', '3 minutes');
define('MAX_FAKE_USERS', 200);

/* TEMPLATE CONSTANTS */
define('SHOW_LINK_TEMPLATE', getPath(getViewsPath(), 'utils', 'show-link.tpl.php'));
define('SHOW_SESSION_MESSAGE_TEMPLATE', getPath(getViewsPath(), 'utils', 'show-session-message.tpl.php'));
define('PAGINATION_TEMPLATE', getPath(getViewsPath(), 'utils', 'pagination.tpl.php'));
define('MESSAGE_BOX_TEMPLATE', getPath(getViewsPath(), 'utils', 'message-box.tpl.php'));
define('NAVBAR_TEMPLATE', getPath(getViewsPath(), 'utils', 'navbar.tpl.php'));
define('UMS_NAVBAR_TEMPLATE', getPath(getViewsPath(), 'utils', 'ums-navbar.tpl.php'));
define('ACCOUNT_POPUP_NAVBAR_TEMPLATE', getPath(getViewsPath(), 'utils', 'account-popup-navbar.tpl.php'));
define('ROWS_FOR_PAGE_TEMPLATE', getPath(getViewsPath(), 'utils', 'rows-for-page.tpl.php'));

/* ROLES CONSTANTS */
define('ADMIN_ROLE_ID', '0');
define('EDITOR_ROLE_ID', '1');
define('USER_ROLE_ID', '2');
define('DEFAULT_ROLE', USER_ROLE_ID);

/* RSA CONSTANTS */
define('DIGEST_ALG', 'sha512');
define('PRIVATE_KEY_BITS', 4096);

/* REGEX CONSTATS */
define('USE_REGEX_NAME', TRUE);
define('REGEX_NAME', '/^[a-zA-Z\s]+$/');
define('USE_REGEX_USERNAME', TRUE);
define('REGEX_USERNAME', '/^[a-zA-Z\d._\-?&%$]+$/');
define('USE_REGEX_EMAIL', TRUE);
define('REGEX_EMAIL', '/^[a-zA-Z\d\-_%.]+@[a-zA-Z\d\-.]+\.[a-zA-Z]+$/');
define('USE_REGEX_PASSWORD', TRUE);
define('REGEX_PASSWORD', '/^((?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$@!%*?&._\-]))[A-Za-z0-9$@!%*?&._\-]{'.MIN_LENGTH_PASS.',}$/');

/* CONTANTS USED FOR HTML, JS AND OTHER SOURCES */ 
define('NAME_LAYOUT_DATA', 'name-layout-');
define('VAL_LAYOUT_DATA', 'val-layout-');
define('SITEMAP_ROUTE', 'route-');
define('SITEMAP_LASTMOD', 'lastmod-');
define('SITEMAP_PRIORITY', 'priority-');
define('SITEMAP_CHANGEFREQ', 'changefreq-');
define('NO_ESCAPE', '_noesc_');
define('SEARCH', 'search');
define('ASC', 'asc');
define('DESC', 'desc');
define('ORDER_ASC_CLASS', 'down');
define('ORDER_DESC_CLASS', 'up');
define('DISABLED', 'disabled');
define('CHECKED', 'checked="checked"');
define('OLD_PASS', 'old_pass');
define('CONFIRM_PASS', 'confirm_pass');
define('TOKEN', '_xf');
define('RESTORE_TOKEN', '_xf_rstr');
define('RSA_TOKEN', '_xf_rsa');
define('DELETE_TOKEN', '_xf_del');
define('UPDATE_TOKEN', '_xf_upd');
define('DELETE_NEW_EMAIL_TOKEN', '_xf_del_ml');
define('REMOVE_SESSION_TOKEN', '_xf_rmv_ssn');
define('RESEND_ENABLER_EMAIL_TOKEN', '_xf_res_ml');
define('INVALIDATE_TOKEN', '_xf_invldt');
define('LOCKS_USER_RESET_TOKEN', '_xf_lck_rst');
define('LOGOUT_TOKEN', '_xf_out');
define('GET_KEY_TOKEN', '_kxt');
define('NEW_TOKEN', 'ntk');
define('ERROR', 'error');
define('SUCCESS', 'success');
define('MESSAGE', 'message');
define('KEY_N', 'keyN');
define('KEY_E', 'keyE');
define('REDIRECT_TO', 'redirect_to');
define('DOUBLE_LOGIN_REQUIRE', 'dbl_lgn_rq');
define('MESSAGE_LANG_SOURCES', 'msg');
define('DATA_LANG_SOURCES', 'data');
define('TABLE', 'tbl');

/* ROUTES CONSTANTS */
define('HOME_ROUTE', '');
define('UMS_HOME_ROUTE', 'ums');
define('UMS_TABLES_ROUTE', 'ums/table');
define('ADVANCE_SEARCH_ROUTE', 'ums/search/advance');
define('NEW_EMAIL_ROUTE', 'ums/email/new');
define('SEND_EMAIL_ROUTE', 'ums/email/send');
define('APP_SETTINGS_ROUTE', 'ums/app/settings');
define('FAKE_USERS_ROUTE', 'ums/users/fake');
define('RSA_GENERATOR_ROUTE', 'ums/generator/rsa');
define('SITE_MAP_GENERATOR_ROUTE', 'ums/generator/site/map');
define('SITE_MAP_UPDATE_ROUTE', 'ums/generator/site/map/update');
define('ACCOUNT_ENABLER_ROUTE', 'account/enable');
define('EMAIL_ENABLER_ROUTE', 'validate/new/email');
define('DOUBLE_LOGIN_ROUTE', 'account/double_login');
define('LOGIN_ROUTE', 'auth/login');
define('SIGNUP_ROUTE', 'auth/signup');
define('LOGOUT_ROUTE', 'auth/logout');
define('RESEND_EMAIL_ROUTE', 'email/resend');
define('DELETE_EMAIL_ROUTE', 'email/delete');
define('PASS_RESET_REQ_ROUTE', 'auth/reset/password');
define('PASS_RESET_ROUTE', 'user/reset/password');
define('ACCOUNT_INFO_ROUTE', 'user/info');
define('ACCOUNT_SETTINGS_ROUTE', 'user/settings');
define('GET_JSON_CONFIG_ROUTE', 'app/config/get/json');
define('GET_JSON_KEY_ROUTE', 'app/config/get/key/json');
define('ACTION_ROUTE', 'action');
define('CONFIRM_ROUTE', 'confirm');
define('UPDATE_ROUTE', 'update');
define('DELETE_ROUTE', 'delete');
define('REMOVE_ROUTE', 'remove');
define('RESEND_ROUTE', 'resend');
define('RESET_ROUTE', 'reset');
define('NEW_ROUTE', 'new');
define('INVALIDATE_ROUTE', 'invalidate');
define('RESTORE_ROUTE', 'restore');
define('SAVE_ROUTE', 'save');
define('GET_ROUTE', 'get');
define('PASS_UPDATE_ROUTE', 'password_update');

/* CONSTANTS FOR SOURCES */
define('SOURCE', 'src');
define('INTEGRITY', 'integrity');
define('CROSSORIGIN', 'crossorigin');

/* CONSTANTS LISTS */
define('ROWS_FOR_PAGE_LIST', [5, 10, 20, 50, 100]);
define('USERS_ORDER_BY_LIST', [
    USER_ID,
    NAME,
    USERNAME,
    EMAIL,
    ROLE,
    ENABLED
]);
define('DELETED_USERS_ORDER_BY_LIST', [
    USER_ID,
    NAME,
    USERNAME,
    EMAIL,
    ROLE,
    REGISTRATION_DATETIME,
    DELETE_DATETIME
]);
define('PENDING_USERS_ORDER_BY_LIST', [
    PENDING_USER_ID,
    USER_ID_FRGN,
    USERNAME,
    NAME,
    EMAIL,
    ROLE,
    ENABLER_TOKEN,
    REGISTRATION_DATETIME,
    EXPIRE_DATETIME
]);
define('PENDING_EMAILS_ORDER_BY_LIST', [
    PENDING_EMAIL_ID,
    USERNAME,
    NEW_EMAIL,
    ENABLER_TOKEN,
    EXPIRE_DATETIME
]);
define('ROLES_ORDER_BY_LIST', [
    ROLE_ID,
    ROLE,
    CAN_CREATE_USER,
    CAN_UPDATE_USER,
    CAN_DELETE_USER,
    CAN_UNLOCK_USER,
    CAN_RESTORE_USER,
    CAN_CHANGE_PASSWORD,
    CAN_REMOVE_SESSION,
    CAN_REMOVE_ENABLER_TOKEN,
    CAN_GENERATE_RSA,
    CAN_GENERATE_SITEMAP,
    CAN_CHANGE_SETTINGS,
    CAN_SEND_EMAIL,
    CAN_VIEW_TABLES,
]);
define('SESSIONS_ORDER_BY_LIST', [
    SESSION_ID,
    USERNAME,
    IP_ADDRESS,
    SESSION_TOKEN,
    EXPIRE_DATETIME
]);
define('PASS_RESET_REQ_ORDER_BY_LIST', [
    PASSWORD_RESET_REQ_ID,
    USERNAME,
    IP_ADDRESS,
    PASSWORD_RESET_TOKEN,
    EXPIRE_DATETIME
]);
define('ORDER_DIR_LIST', [
    ASC,
    DESC
]);
define('CHANGE_FREQ_LIST', [
    'always',
    'hourly',
    'daily',
    'weekly',
    'monthly',
    'yearly',
    'never'
]);

define('ACCEPT_LANG_LIST', [
    'en',
    'it'
]);

define('SYSTEM_LAYOUT_LIST', [
    DEFAULT_LAYOUT,
    UMS_LAYOUT,
    UMS_TABLES_LAYOUT,
    SETTINGS_LAYOUT,
    EMAIL_LAYOUT,
    PASSWORD_RESET_EMAIL_LAYOUT,
    ENABLER_EMAIL_LAYOUT,
    RANDOM_PASSWORD_EMAIL_LAYOUT,
]);

define('UMS_TABLES_LIST', [
    USERS_TABLE => 'Users',
    DELETED_USER_TABLE => 'Deleted users',
    PENDING_USERS_TABLE => 'Pending users',
    PENDING_EMAILS_TABLE => 'Pending emails',
    ROLES_TABLE => 'Roles',
    SESSIONS_TABLE => 'Sessions',
    PASSWORD_RESET_REQ_TABLE => 'Password reset requests'
]);

/* CONSTATS FOR RESULT DATA */
define('DATA', 'dta');
define('LOCK', 'lck');
define('USER', 'usr');
define('LINK', 'lnk');
define('FAIL', 'fl');
define('EMPTY_CONTENT', 'empt_cnt');
define('GENERIC', 'gnrc');
define('SAVE_SETTINGS', 'sv_sttngs');
define('SAVE_USER', 'sv_usr');
define('LOCK_USER_RESET', 'lck_rst');
define('RESTORE_USER', 'rstr_usr');
define('REMOVE_SESSION', 'rmv_ssn');
define('INVALIDATE_PENDING_EMAIL', 'invl_pnd_eml');
define('INVALIDATE_PENDING_USER', 'invl_pnd_usr');
define('INVALIDATE_PASS_RES_REQ', 'invl_pss_rs_rq');
define('ALREADY_SET', 'alrd_set');
define('USER_UPDATE', 'usr_updt');
define('USER_DELETE', 'usr_dlt');
define('DOUBLE_LOGIN', 'dbl_lgn');
define('WRONG_USERNAME', 'wrng_usrnm');
define('WRONG_EMAIL', 'wrng_eml');
define('WRONG_PASSWORD', 'wrng_pssd');
define('INVALID_EMAIL', 'invld_eml');
define('INVALID_NAME', 'invld_nm');
define('INVALID_USERNAME', 'invld_usrnm');
define('INVALID_PASSWORD', 'invld_psswd');
define('INVALID_ROLETYPE', 'invld_rltp');
define('INVALID_DATE', 'invld_date');
define('INVALID_DATETIME', 'invld_datetime');
define('INVALID_FILEPATH', 'invld_flpth');
define('INVALID_SECTION', 'invld_sctn');
define('INVALID_TABLE', 'invld_tbl');
define('INVALID_LAYOUT', 'invld_lyot');
define('INVALID_DOMAIN', 'invld_dmn');
define('INVALID_NUMBER', 'invld_nmbr');
define('INVALID_ACTION', 'invld_actn');
define('INVALID_ID', 'invld_id');
define('OPERATION_SUCCESS', 'oprt_scss');
define('OPERATION_FAILED', 'oprt_fld');
define('NEW_EMAIL_DELETE', 'dlt_nweml');
define('PASS_MISMATCH', 'pss_mstmch');
define('USER_NOT_FOUND', 'usr_nt_fnd');
define('LINK_EXPIRE', 'lnk_expr');
define('RESEND_LOCK', 'rsnd_lck');
define('EMAIL_ALREADY_EXISTS', 'eml_alrd_exsts');
define('USERNAME_ALREADY_EXISTS', 'usrnm_alrd_exsts');
define('LOGIN', 'lgn');
define('SIGNUP', 'sgnp');
define('SEND_EMAIL', 'snd_eml');
define('CHANGE_PASS', 'chng_pss');
define('ENABLE_ACCOUNT', 'enbl_acc');
define('ENABLE_EMAIL', 'enbl_eml');
define('PASS_RESET_REQ', 'pss_rst_req');
define('LOGOUT', 'lgt');
define('SECTION', 'section');
define('DISABLE', 'disable');
define('PUBL_KEY', 'publ_key');
define('PRIV_KEY', 'priv_key');
define('KEY_PAIR', 'key_pair');
define('EXCEPTION', 'exception');
define('ERROR_INFO', 'error_info');
define('URL_SERVER', 'url_server');
define('CHANGED_EMAIL', 'chng_email');
define('REMOVE_TOKEN', 'remove_token');
define('GENERATE_TOKEN', 'gen_token');
define('PATH_PRIV_KEY', 'path_priv_key');
define('RESEND_LOCK_EXPIRE', 'rsnd_lck_expr_tm');
define('PENDING', 'pndng');
define('N_USERS', 'n_users');
define('SUBJETC', 'subject');
define('CONTENT', 'content');
define('REQUEST', 'rqst');
define('SESSION', 'sssn');
define('TO', 'to');
define('ROUTE', 'route');
define('ROUTES', 'routes');
define('LOCATION', 'loc');
define('LASTMOD', 'lastmod');
define('PRIORITY', 'priority');
define('CHANGEFREQ', 'changefreq');
define('SITE_MAP_EXISTS', 'site_map_exists');
define('ROLES', 'roles');
define('TO_STRING', 'to_string');
define('CODE', 'code');
define('FILE', 'file');
define('LINE', 'line');
define('PREVIOUS', 'previous');
define('TRACE', 'trace');
define('TRACE_STRING', 'trace_string');
define('ORDER_BY', 'order_by');
define('SEARCH_QUERY', 'search_query');
define('PAGE', 'page');
define('ROWS_FOR_PAGE', 'rows_for_page');
define('ENABLED_USERS', 'enbl_usrs');
define('PENDING_USERS', 'pend_usrs');
define('PENDING_EMAILS', 'pend_mls');
define('VALID_SESSIONS', 'vld_ssns');
define('TOT_ROWS', 'tot_rws');
define('TOT_USERS', 'tot_usrs');
define('TOT_DELETED_USERS', 'tot_del_usrs');
define('TOT_PENDING_USERS', 'tot_pen_usrs');
define('TOT_PENDING_MAILS', 'tot_pen_ml');
define('TOT_ROLES', 'tot_rls');
define('TOT_SESSIONS', 'tot_sssn');
define('TOT_REQ', 'tot_rq');
define('MAX_PAGES', 'max_pages');
define('START_PAGE', 'strt_pg');
define('STOP_PAGE', 'stp_pg');
define('LINK_HEAD', 'lnk_hd_');
define('HEAD_TABLE_LIST', 'hd_tbl_lst');
define('CLASS_HEAD', 'clss_hd_');
define('SEND_EMAIL_LINK', 'lnk_sndml');
define('BASE_LINK_ROWS_FOR_PAGE', 'bs_lnk_rfp');
define('SEARCH_ACTION', 'src_act');
define('USERS', 'usrs');
define('EMAILS', 'mls');
define('SESSIONS', 'sessns');
define('REQUESTS', 'rqsts');
define('MESSAGE_ENABLE_ACC', 'msg_enbl');
define('CLASS_ENABLE_ACC', 'clss_enbl');
define('IS_LOCK', 'is_lock');
define('IS_EXPIRED', 'is_expired');
define('IS_VALID', 'is_valid');
define('MESSAGE_LOCK_ACC', 'msg_lck_acc');
define('MESSAGE_EXPIRE', 'msg_expr');
define('WAIT_EMAIL_CONFIRM', 'wt_cnf_mail');
define('VIEW_ROLE', 'view_role');
define('DOUBLE_LOGIN_SESSION', 'dbl_lgn_ssn');
define('RESULT', 'rslt');
define('COLUMN_LIST', 'cmln_lst');
define('TABLES_LIST', 'tbls_lst');
define('SEARCH_PARAMS', 'srch_param');
define('PARAM_VALUES', 'prm_val');
define('VALUE', 'val');
define('TYPE', 'tp');
define('SELECT_LIST', 'slct_lst');

/* CONSTANTS FOR PAGINATION */
define('LINK_PAGIN_ARROW_LEFT', 'lnk_pgn_arlft');
define('CLASS_PAGIN_ARROW_LEFT', 'clss_pgn_arlft');
define('LINK_PAGIN_ARROW_RIGHT', 'lnk_pgn_arrgt');
define('CLASS_PAGIN_ARROW_RIGHT', 'clss_pgn_arrgt');
define('BASE_LINK_PAGIN', 'bs_lnk_pgn');
define('CLOSE_LINK_PAGIN', 'cls_ln_pgn');

/* CONSTANTS FOR COOKIES */
define('CK_LOGIN_SESSION', 'lstkn');
define('CK_LANG', 'lang');

/* CONSTANTS FOR CSRF TOKENS */
define('CSRF', 'XS-TKN');
define('CSRF_LOGOUT', 'XS-TKN-OUT');
define('CSRF_NEW_EMAIL', 'XS-TKN-NE');
define('CSRF_PASS_RESET', 'XS-TKN-PR');
define('CSRF_PASS_RESET_REQ', 'XS-TKN-PRRQ');
define('CSRF_SETTINGS', 'XS-TKN-STTNG');
define('CSRF_GEN_SITEMAP', 'XS-TKN-GSM');
define('CSRF_GEN_RSA', 'XS-TKN-GR');
define('CSRF_GEN_SAVE_RSA', 'XS-TNK-GSR');
define('CSRF_ADD_FAKE_USER', 'XS-TKN-FU');
define('CSRF_DOUBLE_LOGIN', 'XS-TKN-DBLLGN');
define('CSRF_LOGIN', 'XS-TKN-LGN');
define('CSRF_SIGNUP', 'XS-TKN-SGN');
define('CSRF_RESEND_ENABLER_ACC', 'XS-TKN-RSENACC');
define('CSRF_LOCK_USER_RESET', 'XS-TKN-LKUSRST');
define('CSRF_DELETE_USER', 'XS-TKN-DLTUSR');
define('CSRF_UPDATE_PASS', 'XS-TKN-UPDPSS');
define('CSRF_UPDATE_USER', 'XS-TKN-UPDUSR');
define('CSRF_RESTORE_USER', 'XS-TKN-RSTRUSR');
define('CSRF_INVALIDATE_SESSION', 'XS-TKN-RMVSSN');
define('CSRF_INVALIDATE_PENDING_USER', 'XS-TKN-PNDUSR');
define('CSRF_INVALIDATE_PENDING_EMAIL', 'XS-TKN-PNDEML');
define('CSRF_INVALIDATE_PASS_RES_REQ', 'XS-TKN-PSSRSTRQ');
define('CSRF_NEW_USER', 'XS-TKN-NWUSR');
define('CSRF_DELETE_ACCOUNT', 'XS-TKN-DLTACC');
define('CSRF_UPDATE_ACCOUNT', 'XS-TKN-UPDACC');
define('CSRF_DELETE_NEW_EMAIL', 'XS-TKN-DLML');
define('CSRF_RESEND_ENABLER_EMAIL', 'XS-TKN-RSENML');
define('CSRF_RESEND_PASS_RES_REQ', 'XS-TKN-PSSRESREQ');
define('CSRF_CHANGE_PASS', 'XS-TKN-CHNPSS');
define('CSRF_KEY_JSON', 'XS-TKN-KJ');
