<?php

return [
    APP_URI . '[/]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'index',
        'default'    => true
    ],
    APP_URI . '/install[/]' => [
        'controller' => 'Phire\Controller\Install\IndexController',
        'action'     => 'index'
    ],
    APP_URI . '/install/config[/]' => [
        'controller' => 'Phire\Controller\Install\IndexController',
        'action'     => 'config'
    ],
    APP_URI . '/install/user[/]' => [
        'controller' => 'Phire\Controller\Install\IndexController',
        'action'     => 'user'
    ],
    APP_URI . '/login[/]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'login'
    ],
    APP_URI . '/profile[/]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'profile'
    ],
    APP_URI . '/register/:id' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'register'
    ],
    APP_URI . '/verify/:id/:hash' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'verify'
    ],
    APP_URI . '/forgot[/]' => [
        'controller' => 'Phire\Controller\IndexController',
        'action'     => 'forgot'
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
        'controller' => 'Phire\Controller\Config\IndexController',
        'action'     => 'index'
    ],
    APP_URI . '/config/json/:format' => [
        'controller' => 'Phire\Controller\Config\IndexController',
        'action'     => 'json'
    ],
    APP_URI . '/modules[/]' => [
        'controller' => 'Phire\Controller\Modules\IndexController',
        'action'     => 'index'
    ],
    APP_URI . '/users[/]' => [
        'controller' => 'Phire\Controller\Users\IndexController',
        'action'     => 'index'
    ],
    APP_URI . '/users/add[/]' => [
        'controller' => 'Phire\Controller\Users\IndexController',
        'action'     => 'add'
    ],
    APP_URI . '/users/edit/:id' => [
        'controller' => 'Phire\Controller\Users\IndexController',
        'action'     => 'edit'
    ],
    APP_URI . '/users/remove' => [
        'controller' => 'Phire\Controller\Users\IndexController',
        'action'     => 'remove'
    ],
    APP_URI . '/users/roles[/]' => [
        'controller' => 'Phire\Controller\Users\RolesController',
        'action'     => 'index'
    ],
    APP_URI . '/users/roles/add[/]' => [
        'controller' => 'Phire\Controller\Users\RolesController',
        'action'     => 'add'
    ],
    APP_URI . '/users/roles/edit/:id' => [
        'controller' => 'Phire\Controller\Users\RolesController',
        'action'     => 'edit'
    ],
    APP_URI . '/users/roles/json/:id' => [
        'controller' => 'Phire\Controller\Users\RolesController',
        'action'     => 'json'
    ],
    APP_URI . '/users/roles/remove' => [
        'controller' => 'Phire\Controller\Users\RolesController',
        'action'     => 'remove'
    ]
];