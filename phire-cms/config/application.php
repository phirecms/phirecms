<?php

return [
    'routes'   => include 'routes.php',
    'services' => [
        'session' => [
            'call' => 'Pop\Web\Session::getInstance'
        ],
        'database' => [
            'call'   => 'Pop\Db\Db::connect',
            'params' => [
                'adapter' => DB_INTERFACE,
                'options' => [
                    'database' => DB_NAME,
                    'username' => DB_USER,
                    'password' => DB_PASS,
                    'host'     => DB_HOST
                ]
            ]
        ]
    ]
];