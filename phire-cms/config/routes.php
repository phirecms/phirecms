<?php
/**
 * HTTP Routes
 */
return [
    'get' => [
        APP_URI => [
            '[/]' => [
                'controller' => 'Phire\Controller\IndexController',
                'action'     => 'index'
            ],
            '/logout[/]' => [
                'controller' => 'Phire\Controller\IndexController',
                'action'     => 'logout'
            ]
        ]
    ],
    'get,put' => [],
    'get,post' => [
        APP_URI => [
            '/login[/]' => [
                'controller' => 'Phire\Controller\IndexController',
                'action'     => 'login'
            ],
            '/forgot[/]' => [
                'controller' => 'Phire\Controller\IndexController',
                'action'     => 'forgot'
            ]
        ]
    ],
    'put' => [],
    'post' => [],
    'delete' => [],
    '*' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'error'
    ]
];