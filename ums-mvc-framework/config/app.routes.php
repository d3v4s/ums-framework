<?php

return [
    'GET' => [
        '' => 'app\controllers\Controller@showHome',
        'ums/users' => 'app\controllers\UMSController@showUsersList',
        'ums/users/:orderBy' => 'app\controllers\UMSController@showUsersList',
        'ums/users/:orderBy/:orderDir' => 'app\controllers\UMSController@showUsersList',
        'ums/users/:orderBy/:orderDir/:page' => 'app\controllers\UMSController@showUsersList',
        'ums/users/:orderBy/:orderDir/:page/:nRow' => 'app\controllers\UMSController@showUsersList',
        'ums/user/:user' => 'app\controllers\UMSController@showUser',
        'ums/user/:user/delete' => 'app\controllers\UMSController@showDeleteUser',
        'ums/user/:user/update' => 'app\controllers\UMSController@showUpdateUser',
        'ums/user/:user/update/pass' => 'app\controllers\UMSController@showUpdatePasswordUser',
        'ums/user/new' => 'app\controllers\UMSController@showNewUser',
        'ums/app/settings' => 'app\controllers\AppSettingsController@showAppSettings',
        'ums/app/settings/:section' => 'app\controllers\AppSettingsController@showAppSettings',
        'ums/email/new' => 'app\controllers\EmailController@showNewEmail',
        'ums/users/fake' => 'app\controllers\FakeUsersController@showAddFakeUsers',
        'ums/generator/rsa' => 'app\controllers\RSAKeyGeneratorController@showRSAKeyGenerator',
        'ums/generator/site/map' => 'app\controllers\SiteMapGeneratorController@showSiteMapGenerator',
        'ums/generator/site/map/update' => 'app\controllers\SiteMapGeneratorController@showSiteMapUpdate',
        'account/enable/:token' => 'app\controllers\LoginController@enableAccount',
        'validate/new/email/:token' => 'app\controllers\LoginController@validateNewEmail',
        'auth/login' => 'app\controllers\LoginController@showLogin',
        'auth/signup' => 'app\controllers\LoginController@showSignup',
        'auth/signup/confirm' => 'app\controllers\LoginController@showSignupConfirm',
        'auth/reset/password/req' => 'app\controllers\LoginController@showResetPasswordRequest',
        'user/reset/password/:token' => 'app\controllers\LoginController@showResetPassword',
        'user/settings' => 'app\controllers\UserController@showUserSettings',
        'user/settings/pass' => 'app\controllers\UserController@showChangePassword',
        'user/settings/delete' => 'app\controllers\UserController@showDeleteAccount',
        'app/config/get/json' => function () {
            $confApp = getConfig('app');
            
            if ($confApp['useRegex']) {
                modifyRegexJS($confApp['regexName']);
                modifyRegexJS($confApp['regexUsername']);
                modifyRegexJS($confApp['regexPassword']);
            }

            if ($confApp['useRegexEmail']) modifyRegexJS($confApp['regexEmail']);

            $resConfJSON = [
                'minLengthName' => $confApp['minLengthName'],
                'maxLengthName' => $confApp['maxLengthName'],
                'minLengthUsername' => $confApp['minLengthUsername'],
                'maxLengthUsername' => $confApp['maxLengthUsername'],
                'minLengthPassword' => $confApp['minLengthPassword'],
                'maxLengthPassword' => $confApp['maxLengthPassword'],
                'checkMaxLengthPassword' => $confApp['checkMaxLengthPassword'],
                'requireHardPassword' => $confApp['requireHardPassword'],
                'useRegex' => $confApp['useRegex'],
                'regexName' => $confApp['regexName'],
                'regexUsername' => $confApp['regexUsername'],
                'regexPassword' => $confApp['regexPassword'],
                'useRegexEmail' => $confApp['useRegexEmail'],
                'regexEmail' => $confApp['regexEmail']
            ];
            sendJsonResponse($resConfJSON);
//             echo json_encode($resConfJSON);
//             exit;
        }
    ],

    'POST' => [
        'ums/user/new' => 'app\controllers\UMSController@newUser',
        'ums/user/update' => 'app\controllers\UMSController@updateUser',
        'ums/user/update/pass' => 'app\controllers\UMSController@updatePasswordUser',
        'ums/user/update/reset/wrong/pass' => 'app\controllers\UMSController@resetWrongPasswords',
        'ums/user/update/reset/lock' => 'app\controllers\UMSController@resetLockUser',
        'ums/user/delete/confirm' => 'app\controllers\UMSController@deleteUser',
        'ums/user/delete/new/email' => 'app\controllers\UMSController@deleteNewEmail',
        'ums/app/settings/:section/update' => 'app\controllers\AppSettingsController@updateSettings',
        'ums/generator/rsa/save' => 'app\controllers\RSAKeyGeneratorController@rsaKeyGenerateSave',
        'ums/generator/rsa/get' => 'app\controllers\RSAKeyGeneratorController@rsaKeyGenerate',
        'ums/generator/site/map' => 'app\controllers\SiteMapGeneratorController@siteMapGenerate',
        'ums/email/send' => 'app\controllers\EmailController@sendEmail',
        'ums/users/fake' => 'app\controllers\FakeUsersController@addFakeUsers',
        'auth/login' => 'app\controllers\LoginController@login',
        'auth/logout' => 'app\controllers\LoginController@logout',
        'auth/signup' => 'app\controllers\LoginController@signup',
        'auth/signup/confirm/email/resend' => 'app\controllers\LoginController@signupResendEmail',
        'auth/reset/password' => 'app\controllers\LoginController@resetPasswordRequest',
        'user/reset/password' => 'app\controllers\LoginController@resetPassword',
        'user/settings/update' => 'app\controllers\UserController@updateUser',
        'user/settings/pass/update' => 'app\controllers\UserController@changePassword',
        'user/settings/delete/confirm' => 'app\controllers\UserController@deleteAccount',
        'user/settings/new/email/resend/validation' => 'app\controllers\UserController@resendNewEmailValidation',
        'user/settings/new/email/delete' => 'app\controllers\UserController@deleteNewEmail',
        'app/config/get/key/json' => 'app\controllers\Controller@showKeyJSON'
    ]
];
