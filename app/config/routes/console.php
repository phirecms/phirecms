<?php

return [
    'help' => [
        'controller' => 'Phire\Console\Controller\ConsoleController',
        'action'     => 'help'
    ],
    'config [<interface>] [<param>]' => [
        'controller' => 'Phire\Console\Controller\ConsoleController',
        'action'     => 'config'
    ],
    'migrate create' => [
        'controller' => 'Phire\Console\Controller\MigrationController',
        'action'     => 'create'
    ],
    'migrate run [<steps>]' => [
        'controller' => 'Phire\Console\Controller\MigrationController',
        'action'     => 'run'
    ],
    'migrate rollback [<steps>]' => [
        'controller' => 'Phire\Console\Controller\MigrationController',
        'action'     => 'rollback'
    ],
    'migrate clear' => [
        'controller' => 'Phire\Console\Controller\MigrationController',
        'action'     => 'clear'
    ],
    '*' => [
        'controller' => 'Phire\Console\Controller\ConsoleController',
        'action'     => 'error'
    ]
];