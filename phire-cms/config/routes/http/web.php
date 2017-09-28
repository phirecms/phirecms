<?php

return [
    'get' => [
        APP_URI => [
            '[/]' => [
                'controller' => 'Phire\Http\Web\Controller\IndexController',
                'action'     => 'index'
            ],
            '/logout[/]' => [
                'controller' => 'Phire\Http\Web\Controller\IndexController',
                'action'     => 'logout'
            ]
        ]
    ],
    'get,post' => [
        APP_URI => [
            '/login[/]' => [
                'controller' => 'Phire\Http\Web\Controller\IndexController',
                'action'     => 'login'
            ]
        ]
    ]
];