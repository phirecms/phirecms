<?php

return [
    'options,post' => [
        APP_URI => [
            '/api/auth[/]' => [
                'controller' => 'Phire\Http\Api\Controller\AuthController',
                'action'     => 'auth'
            ],
            '/api/auth/token[/]' => [
                'controller' => 'Phire\Http\Api\Controller\TokenController',
                'action'     => 'token'
            ],
            '/api/auth/token/refresh[/]' => [
                'controller' => 'Phire\Http\Api\Controller\TokenController',
                'action'     => 'refresh'
            ],
            '/api/auth/token/revoke[/]' => [
                'controller' => 'Phire\Http\Api\Controller\TokenController',
                'action'     => 'revoke'
            ]
        ]
    ]
];