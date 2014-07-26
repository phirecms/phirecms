<?php
/**
 * Phire CMS 2.0 Module Config File
 */
return array(
    'Phire' => new \Pop\Config(array(
        'base'   => realpath(__DIR__ . '/../'),
        'config' => realpath(__DIR__ . '/../config'),
        'data'   => realpath(__DIR__ . '/../data'),
        'src'    => realpath(__DIR__ . '/../src'),
        //'view'   => realpath(__DIR__ . '/../view'),
        // Main Phire Routes
        'routes' => array(
            APP_URI => array(
                '/'         => 'Phire\Controller\Phire\IndexController',
                '/install'  => 'Phire\Controller\Phire\Install\IndexController',
                '/structure'  => array(
                    '/'           => 'Phire\Controller\Phire\Structure\IndexController',
                    '/fields'     => 'Phire\Controller\Phire\Structure\FieldsController',
                    '/groups'     => 'Phire\Controller\Phire\Structure\GroupsController'
                ),
                '/extensions' => array(
                    '/' => 'Phire\Controller\Phire\Extensions\IndexController'
                ),
                '/users' => array(
                    '/'         => 'Phire\Controller\Phire\User\IndexController',
                    '/roles'    => 'Phire\Controller\Phire\User\RolesController',
                    '/types'    => 'Phire\Controller\Phire\User\TypesController',
                    '/sessions' => 'Phire\Controller\Phire\User\SessionsController'
                ),
                '/config' => array(
                    '/'      => 'Phire\Controller\Phire\Config\IndexController',
                    '/sites' => 'Phire\Controller\Phire\Config\SitesController'
                )
            )
        ),
        // Main Phire Navigation
        'nav'    => array(
            array(
                'name' => 'Structure',
                'href' => BASE_PATH . APP_URI . '/structure',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\Structure\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Fields',
                        'href' => 'fields',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Structure\FieldsController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Field Groups',
                        'href' => 'groups',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Structure\GroupsController',
                            'permission' => 'index'
                        )
                    )
                )
            ),
            array(
                'name' => 'Extensions',
                'href' => BASE_PATH . APP_URI . '/extensions',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\Extensions\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Modules',
                        'href' => 'modules',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Extensions\IndexController',
                            'permission' => 'modules'
                        )
                    )
                )
            ),
            array(
                'name' => 'Users',
                'href' => BASE_PATH . APP_URI . '/users',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\User\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Users',
                        'href' => '',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\IndexController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'User Roles',
                        'href' => 'roles',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\RolesController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'User Types',
                        'href' => 'types',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\TypesController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'User Sessions',
                        'href' => 'sessions',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\SessionsController',
                            'permission' => 'index'
                        )
                    )
                )
            ),
            array(
                'name'     => 'Configuration',
                'href'     => BASE_PATH . APP_URI . '/config',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\Config\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Sites',
                        'href' => 'sites',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Config\SitesController',
                            'permission' => 'index'
                        )
                    )
                )
            )
        ),
        // Exclude parameter for excluding user-specific resources (controllers) and permissions (actions)
        'exclude_controllers' => array(
            'Phire\Controller\Phire\Install\IndexController'
        ),
        // Exclude parameter for excluding model objects from field assignment
        'exclude_models' => array(
            'Phire\Model\Media',
            'Phire\Model\Extension',
            'Phire\Model\Field',
            'Phire\Model\FieldGroup',
            'Phire\Model\FieldValue',
            'Phire\Model\Install',
            'Phire\Model\UserSession',
            '*\Model\Phire'
        ),
        // Customize the user view columns
        'user_view' => array(),
        // Encryption options for whichever encryption method you choose
        'encryptionOptions' => array(),
        // Amount of revision history to store
        'history' => 10,
        // CAPTCHA settings
        'captcha' => array(
            'expire'      => 300,
            'length'      => 4,
            'width'       => 71,
            'height'      => 26,
            'lineSpacing' => 5,
            'lineColor'   => array(175, 175, 175),
            'textColor'   => array(0, 0, 0),
            'font'        => null,
            'size'        => 0,
            'rotate'      => 0
        )
    ))
);

