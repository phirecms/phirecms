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
                    'modules' => [
                        'name' => 'Modules',
                        'href' => '/modules',
                        'acl'  => [
                            'resource'   => 'modules',
                            'permission' => 'index'
                        ],
                        'attributes' => [
                            'class' => 'modules-nav-icon'
                        ]
                    ],
                    'users' => [
                        'name' => 'Users',
                        'href' => '/users',
                        'acl'  => [
                            'resource'   => 'users',
                            'permission' => 'index'
                        ],
                        'attributes' => [
                            'class' => 'users-nav-icon'
                        ],
                        'children' => [
                            'roles' => [
                                'name' => 'Roles',
                                'href' => '/users/roles',
                                'acl'  => [
                                    'resource'   => 'roles',
                                    'permission' => 'index'
                                ]
                            ]
                        ]
                    ],
                    'config' => [
                        'name' => 'Config',
                        'href' => '/config',
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
                    'baseUrl' => BASE_PATH . APP_URI,
                    'top'     => [
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
if (file_exists(getcwd() . MODULE_PATH . '/phire/config/phire.php')) {
    $config = array_merge($config, include getcwd() . MODULE_PATH . '/phire/config/phire.php');
}

return $config;
