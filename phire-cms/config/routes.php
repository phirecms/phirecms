<?php

return [
    APP_URI . '[/]' => [
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
    ],
    APP_URI . '/config[/]' => [
        'controller' => 'Phire\Controller\ConfigController',
        'action'     => 'index'
    ],
    APP_URI . '/modules[/]' => [
        'controller' => 'Phire\Controller\ModulesController',
        'action'     => 'index'
    ],
    APP_URI . '/users[/]' => [
        'controller' => 'Phire\Controller\UsersController',
        'action'     => 'index'
    ],
    APP_URI . '/roles[/]' => [
        'controller' => 'Phire\Controller\UserRolesController',
        'action'     => 'index'
    ]
];