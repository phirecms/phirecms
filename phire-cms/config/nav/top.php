<?php
/**
 * Pop Web Bootstrap Application Framework main nav configuration
 */
return [
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
    'sessions' => [
        'name' => 'Sessions',
        'href' => '/sessions',
        'acl'  => [
            'resource'   => 'sessions',
            'permission' => 'index'
        ],
        'attributes' => [
            'class' => 'sessions-nav-icon'
        ],
        'children' => [
            'logins' => [
                'name' => 'Logins',
                'href' => 'logins',
                'acl'  => [
                    'resource'   => 'sessions',
                    'permission' => 'logins'
                ]
            ]
        ]
    ]
];