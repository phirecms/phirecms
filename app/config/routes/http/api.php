<?php

return [
    'options,get' => [
        APP_URI => [
            '/api/version[/]' => [
                'controller' => 'Phire\Http\Api\Controller\IndexController',
                'action'     => 'version'
            ],
            '/api/auth[/]' => [
                'controller' => 'Phire\Http\Api\Controller\IndexController',
                'action'     => 'authenticate'
            ]
        ]
    ]
];