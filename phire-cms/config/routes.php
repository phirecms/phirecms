<?php

return [
    APP_URI => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'index',
        'default'    => true
    ],
    APP_URI . '/login' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'login'
    ],
    APP_URI . '/logout' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'logout'
    ]
];