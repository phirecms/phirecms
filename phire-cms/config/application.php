<?php
/**
 * Phire configuration
 */
$config = [
    'routes'    => include 'routes.php',
    'resources' => include 'resources.php',
    'forms'     => include 'forms.php',
    'services' => [
        'session'   => 'Pop\Web\Session::getInstance',
        'acl'       => 'Pop\Acl\Acl',
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
                        ]
                    ],
                    'roles' => [
                        'name' => 'Roles',
                        'href' => '/roles',
                        'acl'  => [
                            'resource'   => 'roles',
                            'permission' => 'index'
                        ],
                        'attributes' => [
                            'class' => 'roles-nav-icon'
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
    ],
    'headers'              => [],
    'dashboard'            => [],
    'dashboard_side'       => [],
    'footers'              => [],
    'system_title'         => 'Phire CMS',
    'updates'              => true,
    'force_ssl'            => false,
    'registration_captcha' => false,
    'registration_csrf'    => false
];

// If the database has been configuration, set up the database service
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
$dir = ((stripos(php_sapi_name(), 'cli') !== false) && (stripos(php_sapi_name(), 'server') === false)) ?
    getcwd() : $_SERVER['DOCUMENT_ROOT'];
if (file_exists($dir . MODULES_PATH . '/phire/config/phire.php')) {
    $config = array_merge($config, include $dir . MODULES_PATH . '/phire/config/phire.php');
}

return $config;
