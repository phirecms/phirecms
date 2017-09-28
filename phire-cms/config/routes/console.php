<?php

return [
    'help' => [
        'controller' => 'Phire\Console\Controller\ConsoleController',
        'action'     => 'help'
    ],
    '*' => [
        'controller' => 'Phire\Console\Controller\ConsoleController',
        'action'     => 'error'
    ]
];