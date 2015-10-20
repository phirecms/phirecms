<?php

return [
    APP_URI => [
        '*' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'error'
        ],
        '[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'index'
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
        '/login[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'login',
            'acl'        => [
                'resource'   => 'login'
            ]
        ],
        '/profile[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'profile',
            'acl'        => [
                'resource'   => 'profile'
            ]
        ],
        '/register/:id' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'register',
            'acl'        => [
                'resource'   => 'register'
            ]
        ],
        '/verify/:id/:hash' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'verify'
        ],
        '/forgot[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'forgot'
        ],
        '/unsubscribe[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'unsubscribe',
            'acl'        => [
                'resource'   => 'unsubscribe'
            ]
        ],
        '/logout[/]' => [
            'controller' => 'Phire\Controller\IndexController',
            'action'     => 'logout'
        ],
        '/config[/]' => [
            'controller' => 'Phire\Controller\Config\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'config'
            ]
        ],
        '/config/json/:format' => [
            'controller' => 'Phire\Controller\Config\IndexController',
            'action'     => 'json'
        ],
        '/update[/]' => [
            'controller' => 'Phire\Controller\Update\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'update'
            ]
        ],
        '/update/complete[/]' => [
            'controller' => 'Phire\Controller\Update\IndexController',
            'action'     => 'complete',
            'acl'        => [
                'resource'   => 'update'
            ]
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
        '/users[/:id]' => [
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
        '/users/remove' => [
            'controller' => 'Phire\Controller\Users\IndexController',
            'action'     => 'remove',
            'acl'        => [
                'resource'   => 'users',
                'permission' => 'remove'
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
        ]
    ]
];