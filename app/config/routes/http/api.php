<?php

return [
    'options,get' => [
        '/api/version[/]' => [
            'controller' => 'Phire\Http\Controller\Api\IndexController',
            'action'     => 'version'
        ]
    ]
];