<?php

return [
    'GET' => [
        '' => 'app\controllers\Controller@showHome',
        'ums' => 'app\controllers\UMSBaseController@showUmsHome',
        'ums/table' => 'app\controllers\UMSTablesController@showUmsHome',
        'ums/table/action/:table/:action' => 'app\controllers\UMSActionsController@switchShowAction',
        'ums/table/action/:table/:action/:id' => 'app\controllers\UMSActionsController@switchShowAction',
        'ums/table/get/:table/:id' => 'app\controllers\UMSTablesController@showRow',
        'ums/table/:table' => 'app\controllers\UMSTablesController@showTable',
        'ums/table/:table/:orderBy' => 'app\controllers\UMSTablesController@showTable',
        'ums/table/:table/:orderBy/:orderDir' => 'app\controllers\UMSTablesController@showTable',
        'ums/table/:table/:orderBy/:orderDir/:page' => 'app\controllers\UMSTablesController@showTable',
        'ums/table/:table/:orderBy/:orderDir/:page/:nRow' => 'app\controllers\UMSTablesController@showTable',
        'ums/search/advance' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        'ums/search/advance/:orderBy' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        'ums/search/advance/:orderBy/:orderDir' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        'ums/search/advance/:orderBy/:orderDir/:page' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        'ums/search/advance/:orderBy/:orderDir/:page/:nRow' => 'app\controllers\AdvanceSearchController@showAdvanceSearch',
        'ums/email/new' => 'app\controllers\EmailController@showNewEmail',
        'ums/app/settings' => 'app\controllers\AppSettingsController@showAppSettings',
        'ums/app/settings/:section' => 'app\controllers\AppSettingsController@showAppSettings',
        'ums/users/fake' => 'app\controllers\FakeUsersController@showAddFakeUsers',
        'ums/generator/rsa' => 'app\controllers\RSAKeyGeneratorController@showRSAKeyGenerator',
        'ums/generator/site_map' => 'app\controllers\SiteMapGeneratorController@showSiteMapGenerator',
        'ums/generator/site_map/update' => 'app\controllers\SiteMapGeneratorController@showSiteMapUpdate',
        'account/enable/:token' => 'app\controllers\LoginController@enableAccount',
        'validate/new/email/:token' => 'app\controllers\LoginController@enableNewEmail',
        'account/double_login' => 'app\controllers\Controller@showDoubleLogin',
        'auth/login' => 'app\controllers\LoginController@showLogin',
        'auth/signup' => 'app\controllers\LoginController@showSignup',
        'auth/signup/confirm' => 'app\controllers\LoginController@showSignupConfirm',
        'auth/reset/password' => 'app\controllers\LoginController@showPasswordResetRequest',
        'user/reset/password/:token' => 'app\controllers\LoginController@showPasswordReset',
        'user/info' => 'app\controllers\AccountController@showAccountInfo',
        'user/settings' => 'app\controllers\AccountController@showAccountSettings',
        'user/settings/password_update' => 'app\controllers\AccountController@showChangePassword',
        'user/settings/sessions' => 'app\controllers\AccountController@showSessions',
        'user/settings/delete/confirm' => 'app\controllers\AccountController@showDeleteAccount',
        'app/config/get/json' => function () {
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
        'ums/table/action/:table/:action' => 'app\controllers\UMSActionsController@switchAction',
        'ums/app/settings/:section/update' => 'app\controllers\AppSettingsController@updateSettings',
        'ums/generator/rsa/save' => 'app\controllers\RSAKeyGeneratorController@generateSaveRsaKey',
        'ums/generator/rsa/get' => 'app\controllers\RSAKeyGeneratorController@generateRsaKey',
        'ums/generator/site_map' => 'app\controllers\SiteMapGeneratorController@generateSiteMap',
        'ums/email/send' => 'app\controllers\EmailController@sendEmail',
        'ums/users/fake' => 'app\controllers\FakeUsersController@addFakeUsers',
        'account/double_login' => 'app\controllers\LoginController@doubleLogin',
        'auth/login' => 'app\controllers\LoginController@login',
        'auth/signup' => 'app\controllers\LoginController@signup',
        'auth/signup/email/resend' => 'app\controllers\LoginController@signupResendEmail',
        'auth/logout' => 'app\controllers\LoginController@logout',
        'auth/reset/password' => 'app\controllers\LoginController@passwordResetRequest',
        'user/reset/password' => 'app\controllers\LoginController@passwordReset',
        'user/settings/sessions/invalidate' => 'app\controllers\AccountController@removeSession',
        'user/settings/update' => 'app\controllers\AccountController@updateAccount',
        'user/settings/password_update' => 'app\controllers\AccountController@changePassword',
        'user/settings/delete' => 'app\controllers\AccountController@deleteAccount',
        'user/settings/email/resend' => 'app\controllers\AccountController@resendEmailEnabler',
        'user/settings/email/delete' => 'app\controllers\AccountController@deleteNewEmail',
        'app/config/get/key/json' => 'app\controllers\Controller@showKeyJSON'
    ]
];
