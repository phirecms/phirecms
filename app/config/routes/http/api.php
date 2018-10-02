<?php

return [
    'options,get' => [
        '/api/version[/]' => [
            'controller' => 'Phire\Http\Api\Controller\IndexController',
            'action'     => 'version'
        ]
    ]
];