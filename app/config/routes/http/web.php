<?php

return [
    'get' => [
        APP_URI => [
            '[/]' => [
                'controller' => 'Phire\Http\Web\Controller\IndexController',
                'action'     => 'index'
            ],
            '/login[/]' => [
                'controller' => 'Phire\Http\Web\Controller\IndexController',
                'action'     => 'login'
            ]
        ]
    ]
];