<?php

return [
    'options,get' => [
        APP_URI => [
            '/api/version[/]' => [
                'controller' => 'Phire\Http\Api\Controller\IndexController',
                'action'     => 'version'
            ]
        ]
    ]
];