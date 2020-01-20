<?php

/* ########### CONSTANTS FOR DATABASE ########### */

/* CONSTANTS FOR DELETED USERS TABLE */
define('DELETED_USER_TABLE', 'deleted_users');
define('DELETED_USER_ID', 'id_deleted_user');
define('DELETE_DATETIME', 'delete_datetime');

/* CONSTANTS FOR PASSWORD RESET REQUESTS TABLE */
define('PASSWORD_RESET_REQ_TABLE', 'password_reset_requests');
define('PASSWORD_RESET_REQ_ID', 'id_password_reset_request');
define('PASSWORD_RESET_TOKEN', 'password_reset_token');

/* CONSTANTS FOR PENDING EMAILS TABLE */
define('PENDING_EMAILS_TABLE', 'pending_emails');
define('PENDING_EMAIL_ID', 'id_pending_email');
define('NEW_EMAIL', 'new_email');

/* CONSTANTS FOR PENDING USERS TABLE */
define('PENDING_USERS_TABLE', 'pending_users');
define('PENDING_USER_ID', 'id_pending_user');

/* CONSTANTS FOR USER ROLES TABLE */
define('ROLES_TABLE', 'roles');
define('ROLE_ID', 'id_role');
define('ROLE', 'role');
define('CAN_CREATE_USER', 'create_user');
define('CAN_UPDATE_USER', 'update_user');
define('CAN_DELETE_USER', 'delete_user');
define('CAN_UNLOCK_USER', 'unlock_user');
define('CAN_RESTORE_USER', 'restore_user');
define('CAN_CHANGE_PASSWORD', 'change_pass');
define('CAN_REMOVE_SESSION', 'remove_session');
define('CAN_GENERATE_RSA', 'gen_rsa');
define('CAN_GENERATE_SITEMAP', 'gen_sitemap');
define('CAN_CHANGE_SETTINGS', 'change_settings');
define('CAN_SEND_EMAIL', 'send_email');
define('CAN_VIEW_TABLES', 'view_tables');

/* CONSTANTS FOR SESSIONS TABLE */
define('SESSIONS_TABLE', 'sessions');
define('SESSION_ID', 'id_session');
define('SESSION_TOKEN', 'session_token');

/* CONSTANTS FOR USERS TABLE */
define('USERS_TABLE', 'users');
define('USER_ID', 'id_user');
define('NAME', 'name');
define('USERNAME', 'username');
define('EMAIL', 'email');
define('PASSWORD', 'password');
define('ENABLED', 'enabled');
define('EXPIRE_LOCK', 'expire_lock');

/* CONSTATS FOR USER LOCK TABLE */
define('USER_LOCK_TABLE', 'user_locks');
define('USER_LOCK_ID', 'id_user_lock');
define('COUNT_WRONG_PASSWORDS', 'count_wrong_password');
define('EXPIRE_TIME_WRONG_PASSWORD', 'expire_wrong_password');
define('COUNT_LOCKS', 'count_locks');

/* CONSTANTS FOR FOREIGN KEY */
define('ROLE_ID_FRGN', 'role_id');
define('USER_ID_FRGN', 'user_id');

/* GENERIC CONSTANTS */
define('IP_ADDRESS', 'ip_address');
define('ENABLER_TOKEN', 'enabler_token');
define('EXPIRE_DATETIME', 'expire_datetime');
define('REGISTRATION_DATETIME', 'registration_datetime');
