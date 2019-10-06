<?php

return [
    'routes' => [
        'GET' => [
            '' => 'app\controllers\Controller@showHome',
            'ums/users' => 'app\controllers\UMSController@showUsers',
            'ums/users/:orderBy' => 'app\controllers\UMSController@showUsers',
            'ums/users/:orderBy/:orderDir' => 'app\controllers\UMSController@showUsers',
            'ums/users/:orderBy/:orderDir/:page' => 'app\controllers\UMSController@showUsers',
            'ums/users/:orderBy/:orderDir/:page/:nRow' => 'app\controllers\UMSController@showUsers',
            'ums/user/:user' => 'app\controllers\UMSController@showUser',
            'ums/user/:user/update' => 'app\controllers\UMSController@showUpdateUser',
            'ums/user/:user/update/pass' => 'app\controllers\UMSController@showUpdatePasswordUser',
            'ums/user/new' => 'app\controllers\UMSController@showNewUser',
            'ums/email/new' => 'app\controllers\UMSController@showNewEmail',
            'ums/users/fake' => 'app\controllers\FakeUsersController@showAddFakeUsers',
            'validate/email/:hash' => 'app\controllers\LoginController@validateEmail',
            'auth/login' => 'app\controllers\LoginController@showLogin',
            'auth/signup' => 'app\controllers\LoginController@showSignup'
//             'test/:data' => function ($data) {
//                 return "DENTRO CALLBACK ---- ID: $data";
//             }
        ],
        'POST' => [
            'ums/user/new' => 'app\controllers\UMSController@newUser',
            'ums/user/update' => 'app\controllers\UMSController@updateUser',
            'ums/user/update/pass' => 'app\controllers\UMSController@updatePasswordUser',
            'ums/user/delete' => 'app\controllers\UMSController@deleteUser',
            'ums/email/send' => 'app\controllers\UMSController@sendEmail',
            'ums/users/fake' => 'app\controllers\FakeUsersController@addFakeUsers',
            'auth/login' => 'app\controllers\LoginController@login',
            'auth/logout' => 'app\controllers\LoginController@logout',
            'auth/signup' => 'app\controllers\LoginController@signup'
        ]
    ],
    'layout' => [
        'default' => 'default',
        'email' => 'email',
        'technofriend' => 'technofriend',
        'ums' => 'default'
    ],
    'app' => [
        'usersForPageList' => [5, 10, 20, 50, 100],
        'linkPagination' => 7,
        'addFakeUsers' => true
    ]
    
];