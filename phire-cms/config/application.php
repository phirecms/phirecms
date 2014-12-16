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
        ],
        'acl' => [
            'call' => 'Pop\Acl\Acl'
        ],
        'nav.phire' => [
            'call'   => 'Pop\Nav\Nav',
            'params' => [
                'tree' => [
                    [
                        'name' => 'Modules',
                        'href' => BASE_PATH . APP_URI . '/modules',
                        'acl'  => [
                            'resource'   => BASE_PATH . APP_URI . '/modules[/]'
                        ]
                    ],
                    [
                        'name' => 'Users',
                        'href' => BASE_PATH . APP_URI . '/users',
                        'acl'  => [
                            'resource'   => BASE_PATH . APP_URI . '/users[/]'
                        ]
                    ],
                    [
                        'name' => 'Roles',
                        'href' => BASE_PATH . APP_URI . '/roles',
                        'acl'  => [
                            'resource'   => BASE_PATH . APP_URI . '/roles[/]'
                        ]
                    ],
                    [
                        'name' => 'Config',
                        'href' => BASE_PATH . APP_URI . '/config',
                        'acl'  => [
                            'resource'   => BASE_PATH . APP_URI . '/config[/]'
                        ]
                    ]
                ],
                'config' => [
                    'top'    => [
                        'id'   => 'phire-nav',
                        'node' => 'nav'
                    ],
                    'parent' => [
                        'node' => 'nav'
                    ],
                    'child'  => [
                        'node' => 'nav'
                    ],
                    'indent' => '    '
                ]
            ]
        ]
    ]
];