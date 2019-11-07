<?php

return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'charset' => 'utf8mb4',
    'user' => 'ums',
    'password' => 'ums',
    'database' => 'ums',
    'pdo' => [
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    ]
];