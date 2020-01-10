<?php

return [
//     'appSections' => [
//         'App' => 'app',
//         'RSA Key' => 'rsa',
//         'Layout' => 'layout'
//     ],

    'users_for_page_list' => [
        5, 10, 20, 50, 100
    ],

    'accepet_langs' => [
        'en',
        'it'
    ],

//     'time_units' => [
//         'minutes',
//         'hour',
//         'day'
//     ],

    'change_freq' => [
        'always',
        'hourly',
        'daily',
        'weekly',
        'monthly',
        'yearly',
        'never'
    ],

    'user_cols' => [
        USER_ID => 'ID',
        NAME => 'Name',
        USERNAME => 'Username',
        EMAIL => 'Email',
        ROLE => 'Roletype', 
        ENABLED => 'Enabled'
    ],

    'order_by' => [
        USER_ID,
        NAME,
        USERNAME,
        EMAIL,
        ROLE,
        ENABLED
    ],

    'order_dir' => [
        ASC,
        DESC
    ]
];