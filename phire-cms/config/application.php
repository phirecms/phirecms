<?php
/**
 * Phire configuration
 */

$config = [
    'routes'    => include 'routes.php',
    'resources' => include 'resources.php',
    'forms'     => include 'forms.php',
    'services'  => [
        'session' => 'Pop\Session\Session::getInstance',
        'acl'     => 'Pop\Acl\Acl',
        'nav.top' => [
            'call'   => 'Pop\Nav\Nav',
            'params' => [
                'tree' => include 'nav/top.php',
                'config' => [
                    'baseUrl' => BASE_PATH . APP_URI,
                    'top'     => [
                        'id'    => 'phire-nav',
                        'node'  => 'ul',
                        'class' => 'nav navbar-nav'
                    ],
                    'parent' => [
                        'node' => 'ul'
                    ],
                    'child'  => [
                        'node' => 'li'
                    ],
                    'indent' => '    '
                ]
            ]
        ],
        'nav.fluid' => [
            'call'   => 'Pop\Nav\Nav',
            'params' => [
                'tree' => include 'nav/fluid.php',
                'config' => [
                    'baseUrl' => BASE_PATH . APP_URI,
                    'top'     => [
                        'id'    => 'phire-fluid-nav',
                        'node'  => 'ul',
                        'class' => 'nav nav-sidebar'
                    ],
                    'parent' => [
                        'node' => 'ul'
                    ],
                    'child'  => [
                        'node' => 'li'
                    ],
                    'indent' => '    '
                ]
            ]
        ],
        'nav.static' => [
            'call'   => 'Pop\Nav\Nav',
            'params' => [
                'tree' => include 'nav/static.php',
                'config' => [
                    'baseUrl' => BASE_PATH . APP_URI,
                    'top'     => [
                        'id'    => 'phire-static-nav',
                        'node'  => 'ul'
                    ],
                    'parent' => [
                        'node' => 'ul'
                    ],
                    'child'  => [
                        'node' => 'li'
                    ],
                    'indent' => '    '
                ]
            ]
        ]
    ],
    'application_title' => 'Phire CMS',
    'force_ssl'         => false,
    'pagination'        => 25,
    'multiple_sessions' => true,
    'login_attempts'    => 0
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

return $config;
