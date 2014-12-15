<?php

return [
    APP_URI . '[/]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'index',
        'default'    => true
    ],
    APP_URI . '/login[/]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'login'
    ],
    APP_URI . '/register[/:id]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'register'
    ],
    APP_URI . '/verify/:id/:hash' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'verify'
    ],
    APP_URI . '/unsubscribe[/]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'unsubscribe'
    ],
    APP_URI . '/logout[/]' => [
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
    APP_URI . '/users/add[/]' => [
        'controller' => 'Phire\Controller\UsersController',
        'action'     => 'add'
    ],
    APP_URI . '/users/edit/:id' => [
        'controller' => 'Phire\Controller\UsersController',
        'action'     => 'edit'
    ],
    APP_URI . '/users/remove' => [
        'controller' => 'Phire\Controller\UsersController',
        'action'     => 'remove'
    ],
    APP_URI . '/roles[/]' => [
        'controller' => 'Phire\Controller\RolesController',
        'action'     => 'index'
    ],
    APP_URI . '/roles/add[/]' => [
        'controller' => 'Phire\Controller\RolesController',
        'action'     => 'add'
    ],
    APP_URI . '/roles/edit/:id' => [
        'controller' => 'Phire\Controller\RolesController',
        'action'     => 'edit'
    ],
    APP_URI . '/roles/json/:id' => [
        'controller' => 'Phire\Controller\RolesController',
        'action'     => 'json'
    ],
    APP_URI . '/roles/remove' => [
        'controller' => 'Phire\Controller\RolesController',
        'action'     => 'remove'
    ]
];