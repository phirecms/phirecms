<?php

return [
    APP_URI => [
        'controller' => 'Phire\Controller\Phire\IndexController',
        'action'     => 'index',
        'default'    => true
    ],
    APP_URI . '/login' => [
        'controller' => 'Phire\Controller\Phire\IndexController',
        'action'     => 'login'
    ],
    APP_URI . '/logout' => [
        'controller' => 'Phire\Controller\Phire\IndexController',
        'action'     => 'logout'
    ]
];