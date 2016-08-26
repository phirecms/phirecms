<?php
/**
 * Pop Web Bootstrap Application Framework main nav configuration
 */
return [
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
    ]
];