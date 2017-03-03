<?php
/**
 * Phire CMS main nav configuration
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
    ]
];