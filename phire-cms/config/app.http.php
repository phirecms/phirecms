<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */

/**
 * Phire CMS HTTP Configuration File
 */
return [
    'routes'    => include 'routes.php',
    'resources' => include 'resources.php',
    'forms'     => include 'forms.php',
    'database'  => [
        'adapter'  => DB_ADAPTER,
        'database' => DB_NAME,
        'username' => DB_USER,
        'password' => DB_PASS,
        'host'     => DB_HOST,
        'type'     => DB_TYPE
    ],
    'headers'        => [],
    'dashboard'      => [],
    'dashboard_side' => [],
    'footers'        => [],
    'force_ssl'      => false,
    'pagination'     => 25,
    'services'       => [
        'acl'        => 'Pop\Acl\Acl',
        'session'    => 'Pop\Session\Session::getInstance',
        'cookie'     => [
            'call'   => 'Pop\Cookie\Cookie::getInstance',
            'params' => ['options' => ['path' => '/']]
        ],
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
        'nav.side' => [
            'call'   => 'Pop\Nav\Nav',
            'params' => [
                'tree' => include 'nav/side.php',
                'config' => [
                    'baseUrl' => BASE_PATH . APP_URI,
                    'top'     => [
                        'id'    => 'phire-side-nav',
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
    ]
];
