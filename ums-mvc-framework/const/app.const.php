<?php

/* ########### GENERIC CONSTANTS FOR APP ########### */

/* SET DEVELOPER MODE CONSTANTS */
define('DEV', TRUE);
define('FAKE_USERS', TRUE);
define('SHOW_MESSAGE_EXCEPTION', TRUE);

/* GENERIC CONTANTS */
define('DEFAULT_LANG', 'en');
define('LINK_PAGINATION', 7);
define('DEFAULT_PASSWORD', 'ums');
define('DEFAULT_USERS_FOR_PAGE', 10);
define('PAGE_NOT_FOUND', 'error-404');
define('PAGE_EXCEPTION', 'error-exception');
define('MAX_TIME_UNCONNECTED_LOGIN_SESSION', '30 minutes');
define('PASS_TRY_TIME', '5 minutes');
define('USER_LOCK_TIME', '15 minutes');
define('MIN_LENGTH_NAME', 4);
define('MAX_LENGTH_NAME', 100);
define('MIN_LENGTH_USERNAME', 3);
define('MAX_LENGTH_USERNAME', 64);
define('MIN_LENGTH_PASS', 8);
define('MAX_LENGTH_PASS', 255);
define('ENABLER_LINK_EXPIRE_TIME', '1 day');
define('PASS_RESET_EXPIRE_TIME', '3 hour');
define('DOMAIN_LOGIN_SESSION_COOCKIE', getServerUrl());
define('DEFAULT_SETTING_SECTION', 'app');
define('COOKIE_EXPIRE_DAYS', 30);

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
define('REGEX_PASSWORD', '/^((?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@!%*?&._\-]))[A-Za-z\d$@!%*?&._\-]{8,}$/');

/* CONTANTS USED FOR HTML DATA */ 
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

/* ROUTES CONSTANTS */
define('HOME_ROUTE', '');
define('UMS_HOME_ROUTE', 'ums');
define('USERS_LIST_ROUTE', 'ums/users');
define('USER_ROUTE', 'ums/user');
define('NEW_USER_ROUTE', 'ums/user/new');
define('NEW_EMAIL_ROUTE', 'ums/email/new');
define('SEND_EMAIL_ROUTE', 'ums/email/send');
define('APP_SETTINGS_ROUTE', 'ums/app/settings');
define('FAKE_USERS_ROUTE', 'ums/users/fake');
define('RSA_GENERATOR_ROUTE', 'ums/generator/rsa');
define('SITE_MAP_GENERATOR_ROUTE', 'ums/generator/site/map');
define('SITE_MAP_UPDATE_ROUTE', 'ums/generator/site/map/update');
define('ACCOUNT_ENABLER_ROUTE', 'account/enable');
define('EMAIL_ENABLER_ROUTE', 'validate/new/email');
define('LOGIN_ROUTE', 'auth/login');
define('SIGNUP_ROUTE', 'auth/signup');
define('LOGOUT_ROUTE', 'auth/logout');
define('CONFIRM_SIGNUP_ROUTE', SIGNUP_ROUTE.'/confirm');
define('RESEND_EMAIL_ROUTE', 'email/resend');
define('DELETE_EMAIL_ROUTE', 'email/delete');
define('PASS_RESET_REQ_ROUTE', 'auth/reset/password');
define('PASS_RESET_ROUTE', 'user/reset/password');
define('ACCOUNT_SETTINGS_ROUTE', 'user/settings');
define('GET_JSON_CONFIG_ROUTE', 'app/config/get/json');
define('GET_JSON_KEY_ROUTE', 'app/config/get/key/json');
define('UPDATE_ROUTE', 'update');
define('DELETE_ROUTE', 'delete');
define('SAVE_ROUTE', 'save');
define('GET_ROUTE', 'get');
define('PASS_UPDATE_ROUTE', UPDATE_ROUTE.'/password');
define('RESET_LOCK_COUNTERS_ROUTE', UPDATE_ROUTE.'/reset/locks');

/* CONSTANTS FOR SOURCES */
define('SOURCE', 'src');
define('INTEGRITY', 'integrity');
define('CROSSORIGIN', 'crossorigin');

/* CONSTANTS LISTS */
define('USERS_FOR_PAGE_LIST', [5, 10, 20, 50, 100]);
define('ORDER_BY_LIST', [
    USER_ID,
    NAME,
    USERNAME,
    EMAIL,
    ROLE,
    ENABLED
]);
define('ORDER_DIR_LIST', [
    ASC,
    DESC
]);
define('USER_COL_LIST', [
    'ID' => USER_ID,
    'Name' => NAME,
    'Username' => USERNAME,
    'Email' => EMAIL,
    'Roletype' => ROLE,
    'Enabled' => ENABLED
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

/* CONSTATS FOR RESULT DATA */
define('DATA', 'data');
define('LOCK', 'lock');
define('USER', 'user');
define('ERROR', 'error');
define('TOKEN', 'tkn');
define('NEW_TOKEN', 'ntk');
define('TOKEN_RSA', 'tkn_rsa');
define('TOKEN_DELETE', 'tkn_dlt');
define('TOKEN_UPDATE', 'tkn_upd');
define('TOKEN_RESEND_ENABLER_EMAIL', 'tkn_rsnd_eml');
define('TOKEN_DELETE_NEW_EMAIL', 'tkn_rsnd_eml');
define('LINK', 'link');
define('SIGNUP', 'signup');
define('MESSAGE', 'message');
define('SECTION', 'section');
define('SUCCESS', 'success');
define('DISABLE', 'disable');
define('PUBL_KEY', 'publ_key');
define('PRIV_KEY', 'priv_key');
define('KEY_N', 'keyN');
define('KEY_E', 'keyE');
define('KEY_PAIR', 'key_pair');
define('EXCEPTION', 'exception');
define('ERROR_INFO', 'error_info');
define('URL_SERVER', 'url_server');
define('CHANGED_EMAIL', 'chng_email');
define('GENERATE_TOKEN', 'gen_token');
define('REMOVE_TOKEN', 'remove_token');
define('PATH_PRIV_KEY', 'path_priv_key');
define('WRONG_PASSWORD', 'wrong_password');
define('LAST_RESEND_REQ', 'last_res_req');
define('TIME_UNIT', 'time_unit_');
define('PENDING', 'pending');
define('N_USERS', 'n_users');
define('SUBJETC', 'subject');
define('CONTENT', 'content');
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
define('USERS_FOR_PAGE', 'users_for_page');
define('TOT_USER', 'tot_user');
define('MAX_PAGES', 'max_pages');
define('START_PAGE', 'strt_pg');
define('STOP_PAGE', 'stp_pg');
define('LINK_HEAD_ID', 'lnk_hd_id');
define('CLASS_HEAD_ID', 'clss_hd_id');
define('LINK_HEAD_NAME', 'lnk_hd_nm');
define('CLASS_HEAD_NAME', 'clss_hd_nm');
define('LINK_HEAD_USERNAME', 'lnk_hd_unm');
define('CLASS_HEAD_USERNAME', 'clss_hd_unm');
define('LINK_HEAD_EMAIL', 'lnk_hd_ml');
define('CLASS_HEAD_EMAIL', 'clss_hd_ml');
define('LINK_HEAD_ENABLED', 'lnk_hd_enbl');
define('CLASS_HEAD_ENABLED', 'clss_hd_enbl');
define('LINK_HEAD_ROLE', 'lnk_hd_rl');
define('CLASS_HEAD_ROLE', 'clss_hd_id');
define('BASE_LINK_USER_FOR_PAGE', 'bs_lnk_ufp');
define('SEARCH_ACTION', 'src_act');
define('USERS', 'usrs');
define('MESSAGE_ENABLE_ACC', 'msg_enbl');
define('CLASS_ENABLE_ACC', 'clss_enbl');
define('IS_LOCK', 'is_lock');
define('MESSAGE_LOCK_ACC', 'msg_lck_acc');
define('WAIT_EMAIL_CONFIRM', 'wt_cnf_mail');

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
define('CSRF_LOGIN', 'XS-TKN-LGN');
define('CSRF_SIGNUP', 'XS-TKN-SGN');
define('CSRF_RESEND_ENABLER_ACC', 'XS-TKN-RSENACC');
define('CSRF_UNLOCK_USER', 'XS-TKN-UNLUSR');
define('CSRF_DELETE_USER', 'XS-TKN-DLTUSR');
define('CSRF_UPDATE_PASS', 'XS-TKN-UPDPSS');
define('CSRF_UPDATE_USER', 'XS-TKN-UPDUSR');
define('CSRF_NEW_USER', 'XS-TKN-NWUSR');
define('CSRF_DELETE_ACCOUNT', 'XS-TKN-DLTACC');
define('CSRF_UPDATE_ACCOUNT', 'XS-TKN-UPDACC');
define('CSRF_DELETE_NEW_EMAIL', 'XS-TKN-DLML');
define('CSRF_RESEND_ENABLER_EMAIL', 'XS-TKN-RSENML');
define('CSRF_CHANGE_PASS', 'XS-TKN-CHNPSS');
define('CSRF_KEY_JSON', 'XS-TKN-KJ');
