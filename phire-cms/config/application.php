<?php

$config = [
    'routes'    => include 'routes.php',
    'resources' => include 'resources.php',
    'services' => [
        'session' => [
            'call' => 'Pop\Web\Session::getInstance'
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
                            'resource'   => 'modules',
                            'permission' => 'index'
                        ],
                        'attributes' => [
                            'class' => 'modules-nav-icon'
                        ]
                    ],
                    [
                        'name' => 'Users',
                        'href' => BASE_PATH . APP_URI . '/users',
                        'acl'  => [
                            'resource'   => 'users',
                            'permission' => 'index'
                        ],
                        'attributes' => [
                            'class' => 'users-nav-icon'
                        ],
                        'children' => [
                            [
                                'name' => 'Roles',
                                'href' => BASE_PATH . APP_URI . '/users/roles',
                                'acl'  => [
                                    'resource'   => 'roles',
                                    'permission' => 'index'
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'Config',
                        'href' => BASE_PATH . APP_URI . '/config',
                        'acl'  => [
                            'resource'   => 'config',
                            'permission' => 'index'
                        ],
                        'attributes' => [
                            'class' => 'config-nav-icon'
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
                'host'     => DB_HOST,
                'type'     => DB_TYPE
            ]
        ]
    ];
}

// Merge any custom/override config values
if (file_exists(__DIR__ . '/../..' . MODULE_PATH . '/phire/config/phire.php')) {
    $config = array_merge($config, include __DIR__ . '/../..' . MODULE_PATH . '/phire/config/phire.php');
}

return $config;
