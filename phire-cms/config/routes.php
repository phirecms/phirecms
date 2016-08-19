<?php
/**
 * Pop Web Bootstrap Application Framework routes
 */
return [
    '/' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'index'
    ],
    '/side' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'side'
    ],
    '/static' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'staticSide'
    ],
    '/login' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'login'
    ],
    '/logout' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'logout'
    ],
    '/forgot' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'forgot'
    ],
    '/profile' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'profile'
    ],
    '/verify/:id/:hash' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'verify'
    ],
    '/users[/:rid]' => [
        'controller' => 'App\Controller\Users\IndexController',
        'action'     => 'index',
        'acl'        => [
            'resource'   => 'users',
            'permission' => 'index'
        ]
    ],
    '/users/add[/:rid]' => [
        'controller' => 'App\Controller\Users\IndexController',
        'action'     => 'add',
        'acl'        => [
            'resource'   => 'users',
            'permission' => 'add'
        ]
    ],
    '/users/edit/:id' => [
        'controller' => 'App\Controller\Users\IndexController',
        'action'     => 'edit',
        'acl'        => [
            'resource'   => 'users',
            'permission' => 'edit'
        ]
    ],
    '/users/process' => [
        'controller' => 'App\Controller\Users\IndexController',
        'action'     => 'process',
        'acl'        => [
            'resource'   => 'users',
            'permission' => 'process'
        ]
    ],
    '/roles[/]' => [
        'controller' => 'App\Controller\Roles\IndexController',
        'action'     => 'index',
        'acl'        => [
            'resource'   => 'roles',
            'permission' => 'index'
        ]
    ],
    '/roles/add[/]' => [
        'controller' => 'App\Controller\Roles\IndexController',
        'action'     => 'add',
        'acl'        => [
            'resource'   => 'roles',
            'permission' => 'add'
        ]
    ],
    '/roles/edit/:id' => [
        'controller' => 'App\Controller\Roles\IndexController',
        'action'     => 'edit',
        'acl'        => [
            'resource'   => 'roles',
            'permission' => 'edit'
        ]
    ],
    '/roles/json/:id' => [
        'controller' => 'App\Controller\Roles\IndexController',
        'action'     => 'json'
    ],
    '/roles/remove' => [
        'controller' => 'App\Controller\Roles\IndexController',
        'action'     => 'remove',
        'acl'        => [
            'resource'   => 'roles',
            'permission' => 'remove'
        ]
    ],
    '/sessions[/]' => [
        'controller' => 'App\Controller\Sessions\IndexController',
        'action'     => 'index',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'index'
        ]
    ],
    '/sessions/remove' => [
        'controller' => 'App\Controller\Sessions\IndexController',
        'action'     => 'remove',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'remove'
        ]
    ],
    '/sessions/logins' => [
        'controller' => 'App\Controller\Sessions\IndexController',
        'action'     => 'logins',
        'acl'        => [
            'resource'   => 'sessions',
            'permission' => 'logins'
        ]
    ],
    '*' => [
        'controller' => 'App\Controller\IndexController',
        'action'     => 'error'
    ]
];