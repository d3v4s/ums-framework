<?php

/* ########### GENERIC CONSTANTS FOR APP ########### */

/* SET DEVELOPER MODE CONSTANTS */
define('DEV', TRUE);
define('FAKE_USERS', TRUE);

/* GENERIC CONTANTS */ 
define('DEFAULT_LANG', 'en');
define('NAME_LAYOUT_DATA', 'name-layout-');
define('VAL_LAYOUT_DATA', 'val-layout-');
define('NO_ESCAPE', '_');

/* ROUTES CONSTANTS */
define('LOGIN_ROUTE', 'auth/login');
define('SIGNUP_ROUTE', 'auth/signup');
define('PASS_RESET_ROUTE', 'user/reset/password');
define('CONFIRM_SIGNUP_ROUTE', 'auth/signup/confirm');

/* CONSTANTS FOR SOURCES */
define('SOURCE', 'src');
define('INTEGRITY', 'integrity');
define('CROSSORIGIN', 'crossorigin');

/* CONSTANTS FOR LISTS */
define('ORDER_BY_LIST', 'order_by');
define('ORDER_DIR_LIST', 'order_dir');
define('TIME_UNIT_LIST', 'time_units');
define('CHANGE_FREQ_LIST', 'change_freq');
define('ACCEPT_LANG_LIST', 'accepet_langs');
define('USERS_FOR_PAGE_LIST', 'users_for_page_list');

/* CONSTATS FOR RESULT DATA */
define('DATA', 'data');
define('LOCK', 'lock');
define('USER', 'user');
define('ERROR', 'error');
define('TOKEN', 'token');
define('NEW_TOKEN', 'ntk');
define('SIGNUP', 'signup');
define('DISABLE', 'disable');
define('MESSAGE', 'message');
define('SECTION', 'section');
define('SUCCESS', 'success');
define('PUBL_KEY', 'publ_key');
define('PRIV_KEY', 'priv_key');
define('KEY_N', 'keyN');
define('KEY_E', 'keyE');
define('KEY_PAIR', 'key_pair');
define('CONFIRM_PASS', 'cpass');
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
define('CSRF_GEN_RSA', 'XS-TKN-RSAG');
define('CSRF_GEN_SAVE_RSA', 'XS-TNK-GSR');
define('CSRF_ADD_FAKE_USER', 'XS-TKN-FU');
define('CSRF_LOGIN', 'XS-TKN-LGN');
define('CSRF_SIGNUP', 'XS-TKN-SGN');
define('CSRF_RESEND_ENABLER_ACC', 'XS-TKN-RSENACC');
