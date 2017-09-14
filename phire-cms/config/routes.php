<?php
/**
 * HTTP Routes
 */
return [
    APP_URI => [
        '[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'index'
        ],
        '/login[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'login'
        ],
        '/logout[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'logout'
        ],
        '/forgot[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'forgot'
        ],
        '/profile[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'profile'
        ],
        '/verify/:id/:hash' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'verify'
        ],
        '/install[/]' => [
            'controller' => 'Phire\Controller\Install\IndexController',
            'action'     => 'index'
        ],
        '/install/config[/]' => [
            'controller' => 'Phire\Controller\Install\IndexController',
            'action'     => 'config'
        ],
        '/install/user[/]' => [
            'controller' => 'Phire\Controller\Install\IndexController',
            'action'     => 'user'
        ],
        '/modules[/]' => [
            'controller' => 'Phire\Controller\Modules\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'modules',
                'permission' => 'index'
            ]
        ],
        '/modules/install[/]' => [
            'controller' => 'Phire\Controller\Modules\IndexController',
            'action'     => 'install',
            'acl'        => [
                'resource'   => 'modules',
                'permission' => 'install'
            ]
        ],
        '/modules/upload[/]' => [
            'controller' => 'Phire\Controller\Modules\IndexController',
            'action'     => 'upload',
            'acl'        => [
                'resource'   => 'modules',
                'permission' => 'upload'
            ]
        ],
        '/modules/update/:id' => [
            'controller' => 'Phire\Controller\Modules\IndexController',
            'action'     => 'update',
            'acl'        => [
                'resource'   => 'modules',
                'permission' => 'update'
            ]
        ],
        '/modules/complete/:id' => [
            'controller' => 'Phire\Controller\Modules\IndexController',
            'action'     => 'complete',
            'acl'        => [
                'resource'   => 'modules',
                'permission' => 'update'
            ]
        ],
        '/modules/process[/]' => [
            'controller' => 'Phire\Controller\Modules\IndexController',
            'action'     => 'process',
            'acl'        => [
                'resource'   => 'modules',
                'permission' => 'process'
            ]
        ],
        '/users[/:rid]' => [
            'controller' => 'Phire\Controller\Users\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'users',
                'permission' => 'index'
            ]
        ],
        '/users/add[/:rid]' => [
            'controller' => 'Phire\Controller\Users\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'users',
                'permission' => 'add'
            ]
        ],
        '/users/edit/:id' => [
            'controller' => 'Phire\Controller\Users\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'users',
                'permission' => 'edit'
            ]
        ],
        '/users/process[/]' => [
            'controller' => 'Phire\Controller\Users\IndexController',
            'action'     => 'process',
            'acl'        => [
                'resource'   => 'users',
                'permission' => 'process'
            ]
        ],
        '/roles[/]' => [
            'controller' => 'Phire\Controller\Roles\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'roles',
                'permission' => 'index'
            ]
        ],
        '/roles/add[/]' => [
            'controller' => 'Phire\Controller\Roles\IndexController',
            'action'     => 'add',
            'acl'        => [
                'resource'   => 'roles',
                'permission' => 'add'
            ]
        ],
        '/roles/edit/:id' => [
            'controller' => 'Phire\Controller\Roles\IndexController',
            'action'     => 'edit',
            'acl'        => [
                'resource'   => 'roles',
                'permission' => 'edit'
            ]
        ],
        '/roles/json/:id' => [
            'controller' => 'Phire\Controller\Roles\IndexController',
            'action'     => 'json'
        ],
        '/roles/remove' => [
            'controller' => 'Phire\Controller\Roles\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'roles',
                'permission' => 'remove'
            ]
        ],
        '/config[/]' => [
            'controller' => 'Phire\Controller\Config\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'config',
                'permission' => 'index'
            ]
        ],
        '*' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'error'
        ]
    ]
];