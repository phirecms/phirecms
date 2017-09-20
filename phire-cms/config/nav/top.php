<?php
/**
 * Phire CMS main top nav configuration
 */
return [
    'modules' => [
        'name' => 'Modules',
        'href' => '/modules',
        'acl'  => [
            'resource'   => 'modules',
            'permission' => 'index'
        ]
    ],
    'users' => [
        'name' => 'Users',
        'href' => '/users',
        'acl'  => [
            'resource'   => 'users',
            'permission' => 'index'
        ]
    ],
    'admin' => [
        'name' => 'Admin',
        'href' => '/admin',
        'acl'  => [
            'resource'   => 'admin',
            'permission' => 'index'
        ],
        'children' => [
            'roles' => [
                'name' => 'Roles',
                'href' => 'roles',
                'acl'  => [
                    'resource'   => 'roles',
                    'permission' => 'index'
                ]
            ],
            'config' => [
                'name' => 'Config',
                'href' => 'config',
                'acl'  => [
                    'resource'   => 'config',
                    'permission' => 'index'
                ]
            ]
        ]
    ]
];