<?php

return [
    'GET' => [
        HOME_ROUTE => 'app\controllers\Controller@showHome',
        UMS_HOME_ROUTE => 'app\controllers\UMSBaseController@showUmsHome',
        UMS_TABLES_ROUTE => 'app\controllers\UMSTablesController@showUmsHome',
        UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/:table/:action' => 'app\controllers\UMSActionsController@switchShowAction',
        UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/:table/:action/:id' => 'app\controllers\UMSActionsController@switchShowAction',
        UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/:table/:id' => 'app\controllers\UMSTablesController@showRow',
        UMS_TABLES_ROUTE.'/:table' => 'app\controllers\UMSTablesController@showTable',
        UMS_TABLES_ROUTE.'/:table/:orderBy' => 'app\controllers\UMSTablesController@showTable',
        UMS_TABLES_ROUTE.'/:table/:orderBy/:orderDir' => 'app\controllers\UMSTablesController@showTable',
        UMS_TABLES_ROUTE.'/:table/:orderBy/:orderDir/:page' => 'app\controllers\UMSTablesController@showTable',
        UMS_TABLES_ROUTE.'/:table/:orderBy/:orderDir/:page/:nRow' => 'app\controllers\UMSTablesController@showTable',
        ADVANCE_SEARCH_ROUTE => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        ADVANCE_SEARCH_ROUTE.'/:orderBy' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        ADVANCE_SEARCH_ROUTE.'/:orderBy/:orderDir' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        ADVANCE_SEARCH_ROUTE.'/:orderBy/:orderDir/:page' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        ADVANCE_SEARCH_ROUTE.'/:orderBy/:orderDir/:page/:nRow' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        NEW_EMAIL_ROUTE => 'app\controllers\EmailController@showNewEmail',
        APP_SETTINGS_ROUTE => 'app\controllers\AppSettingsController@showAppSettings',
        APP_SETTINGS_ROUTE.'/:section' => 'app\controllers\AppSettingsController@showAppSettings',
        FAKE_USERS_ROUTE => 'app\controllers\FakeUsersController@showAddFakeUsers',
        RSA_GENERATOR_ROUTE => 'app\controllers\RSAKeyGeneratorController@showRSAKeyGenerator',
        SITE_MAP_GENERATOR_ROUTE => 'app\controllers\SiteMapGeneratorController@showSiteMapGenerator',
        SITE_MAP_UPDATE_ROUTE => 'app\controllers\SiteMapGeneratorController@showSiteMapUpdate',
        ACCOUNT_ENABLER_ROUTE.'/:token' => 'app\controllers\LoginController@enableAccount',
        EMAIL_ENABLER_ROUTE.'/:token' => 'app\controllers\LoginController@enableNewEmail',
        DOUBLE_LOGIN_ROUTE => 'app\controllers\Controller@showDoubleLogin',
        LOGIN_ROUTE => 'app\controllers\LoginController@showLogin',
        SIGNUP_ROUTE => 'app\controllers\LoginController@showSignup',
        SIGNUP_ROUTE.'/'.CONFIRM_ROUTE => 'app\controllers\LoginController@showSignupConfirm',
        PASS_RESET_REQ_ROUTE => 'app\controllers\LoginController@showPasswordResetRequest',
        PASS_RESET_ROUTE.'/:token' => 'app\controllers\LoginController@showPasswordReset',
        ACCOUNT_INFO_ROUTE => 'app\controllers\AccountController@showAccountInfo',
        ACCOUNT_SETTINGS_ROUTE => 'app\controllers\AccountController@showAccountSettings',
        ACCOUNT_SETTINGS_ROUTE.'/'.PASS_UPDATE_ROUTE => 'app\controllers\AccountController@showChangePassword',
        ACCOUNT_SETTINGS_ROUTE.'/'.SESSIONS_ROUTE => 'app\controllers\AccountController@showSessions',
        ACCOUNT_SETTINGS_ROUTE.'/'.DELETE_ROUTE.'/'.CONFIRM_ROUTE => 'app\controllers\AccountController@showDeleteAccount',
        GET_JSON_CONFIG_ROUTE => function () {
            sendJsonResponse([
                'minLengthName' => MIN_LENGTH_NAME,
                'maxLengthName' => MAX_LENGTH_NAME,
                'minLengthUsername' => MIN_LENGTH_USERNAME,
                'maxLengthUsername' => MAX_LENGTH_USERNAME,
                'minLengthEmail' => MIN_LENGTH_EMAIL,
                'maxLengthEmail' => MAX_LENGTH_EMAIL,
                'minLengthPassword' => MIN_LENGTH_PASS,
                'maxLengthPassword' => MAX_LENGTH_PASS,
                'useRegexName' => USE_REGEX_NAME,
                'regexName' => modifyRegexJS(REGEX_NAME),
                'useRegexUsername' => USE_REGEX_USERNAME,
                'regexUsername' => modifyRegexJS(REGEX_USERNAME),
                'useRegexEmail' => USE_REGEX_EMAIL,
                'regexEmail' => modifyRegexJS(REGEX_EMAIL),
                'useRegexPassword' => USE_REGEX_PASSWORD,
                'regexPassword' => modifyRegexJS(REGEX_PASSWORD)
            ]);
        }
    ],

    'POST' => [
        UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/:table/:action' => 'app\controllers\UMSActionsController@switchAction',
        APP_SETTINGS_ROUTE.'/:section/'.UPDATE_ROUTE => 'app\controllers\AppSettingsController@updateSettings',
        RSA_GENERATOR_ROUTE.'/'.SAVE_ROUTE => 'app\controllers\RSAKeyGeneratorController@generateSaveRsaKey',
        RSA_GENERATOR_ROUTE.'/'.GET_ROUTE => 'app\controllers\RSAKeyGeneratorController@generateRsaKey',
        SITE_MAP_GENERATOR_ROUTE => 'app\controllers\SiteMapGeneratorController@generateSiteMap',
        SEND_EMAIL_ROUTE => 'app\controllers\EmailController@sendEmail',
        FAKE_USERS_ROUTE => 'app\controllers\FakeUsersController@addFakeUsers',
        DOUBLE_LOGIN_ROUTE => 'app\controllers\LoginController@doubleLogin',
        LOGIN_ROUTE => 'app\controllers\LoginController@login',
        SIGNUP_ROUTE => 'app\controllers\LoginController@signup',
        SIGNUP_ROUTE.'/'.RESEND_EMAIL_ROUTE => 'app\controllers\LoginController@signupResendEmail',
        LOGOUT_ROUTE => 'app\controllers\LoginController@logout',
        PASS_RESET_REQ_ROUTE => 'app\controllers\LoginController@passwordResetRequest',
        PASS_RESET_ROUTE => 'app\controllers\LoginController@passwordReset',
        ACCOUNT_SETTINGS_ROUTE.'/'.SESSIONS_ROUTE.'/'.INVALIDATE_ROUTE => 'app\controllers\AccountController@removeSession',
        ACCOUNT_SETTINGS_ROUTE.'/'.UPDATE_ROUTE => 'app\controllers\AccountController@updateAccount',
        ACCOUNT_SETTINGS_ROUTE.'/'.PASS_UPDATE_ROUTE => 'app\controllers\AccountController@changePassword',
        ACCOUNT_SETTINGS_ROUTE.'/'.DELETE_ROUTE => 'app\controllers\AccountController@deleteAccount',
        ACCOUNT_SETTINGS_ROUTE.'/'.RESEND_EMAIL_ROUTE => 'app\controllers\AccountController@resendEmailEnabler',
        ACCOUNT_SETTINGS_ROUTE.'/'.DELETE_EMAIL_ROUTE => 'app\controllers\AccountController@deleteNewEmail',
        GET_JSON_KEY_ROUTE => 'app\controllers\Controller@showKeyJSON'
    ]
];
