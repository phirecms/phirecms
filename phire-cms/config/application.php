<?php

$config = [
    'routes'   => include 'routes.php',
    'services' => [
        'session' => [
            'call' => 'Pop\Web\Session::getInstance'
        ],
        'acl' => [
            'call' => 'Phire\Acl\Acl'
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
                        ],
                        'children' => [
                            [
                                'name' => 'Roles',
                                'href' => BASE_PATH . APP_URI . '/users/roles',
                                'acl'  => [
                                    'resource'   => BASE_PATH . APP_URI . '/users/roles[/]'
                                ]
                            ]
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

if ((DB_INTERFACE != '') && (DB_NAME != '')) {
    $config['services']['database'] = [
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
    ];
}

// Merge any custom/override config values
if (file_exists(__DIR__ . '/../..' . MODULE_PATH . '/phire/config/phire.php')) {
    $config = array_merge($config, include __DIR__ . '/../..' . MODULE_PATH . '/phire/config/phire.php');
}

return $config;
