<?php

/* ########### CONSTANTS FOR THE CONFIGURATION INI FILE ########### */

/* SECTIONS */
define('APP', 'app');
define('LAYOUT', 'layout');
define('RSA ', 'rsa');
define('SECURITY', 'security');
define('UMS', 'ums');

/* APP SECTION */
define('PAGE_NOT_FOUND', 'page_not_found');
define('SHOW_MESSAGE_EXCEPTION', 'show_message_exception');
define('PAGE_EXCEPTION', 'page_exception');
define('SEND_EMAIL_FROM', 'send_email_from');
define('DATE_FORMAT', 'date_format');
define('DATETIME_FORMAT', 'datetime_format');

/* LAYOUT SECTION */
define('DEFAULT_LAYOUT', 'default');
define('UMS_LAYOUT', 'ums');
define('EMAIL_LAYOUT', 'email');
define('PASSWORD_RESET_EMAIL_LAYOUT', 'password_reset_email');
define('ENABLER_EMAIL_LAYOUT', 'enabler_email');

/* RSA SECTION */
define('DIGEST_ALG', 'digest_alg');
define('PRIVATE_KEY_BITS', 'private_key_bits');
define('STATIC_RSA_KEY', 'static_rsa_key');
define('RSA_PRIV_KEY_FILE', 'rsa_priv_key_file');

/* SECURITY SECTION */
define('ONLY_HTTPS', 'only_https');
define('BLOCK_CHANGE_IP', 'block_change_ip');
// define('EXPIRE_LOGIN_SESSION', 'expire_login_session'); // checkConnectTimeLoginSession
define('MAX_TIME_UNCONNECTED_LOGIN_SESSION', 'max_time_unconnected_login_session');
define('MAX_WRONG_PASSWORDS', 'max_wrong_passwords');
define('PASSWORD_TRY_TIME', 'password_try_time');
define('USER_LOCK_TIME', 'user_lock_time');
define('MAX_LOCKS', 'max_locks');

/* UMS SECTION */
define('DEFAULT_USER', 'defautl_user');
define('MIN_LENGHT_NAME', 'min_length_name');
define('MAX_LENGTH_NAME', 'max_length_name');
define('MIN_LENGHT_USERNAME', 'min_length_username');
define('MAX_LENGTH_USERNAME', 'max_length_username');
define('MIN_LENGHT_PASS', 'min_length_password');
define('CHECK_MAX_LENGTH_PASS', 'check_max_length_password');
define('MAX_LENGTH_PASS', 'max_length_password');
define('REQUIRE_HARD_PASS', 'require_hard_password');
define('PASS_DEFAULT', 'pass_default');
define('USE_REGEX', 'use_regex');
define('REGEX_NAME', 'regex_name');
define('REGEX_USERNAME', 'regex_username');
define('REGEX_PASSWORD', 'regex_password');
define('USE_REGEX_EMAIL', 'use_regex_email');
define('REGEX_EMAIL', 'regex_email');
define('ADD_FAKE_USER_PAGE', 'add_fake_users_page');
define('USER_FOR_PAGE_LIST', 'users_for_page_list');
define('LINK_PAGINATION', 'link_pagination');
define('REQUIRE_CONFIRM_EMAIL', 'require_confirm_email');
define('ENABLER_EMAIL_FROM', 'enabler_email_from');
// define('USE_SERVER_DOMAIN_VALIDATION_EMAIL_LINK', 'use_server_domain_validation_email_link');
define('DOMAIN_URL_LINK', 'domain_url_link');
define('PASS_RESET_EMAIL_FROM', 'pass_reset_email_from');
// define('USE_SERVER_DOMAIN_PASS_RESET_LINK', 'use_server_domain_pass_reset_link');
// define('DOMAIN_URL_PASS_RESET_LINK', 'domain_url_pass_reset_link');
define('PASS_RESET_EXPIRE_TIME', 'pass_reset_expire_time');
