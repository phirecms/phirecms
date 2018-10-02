<?php

return array_merge(
    include 'http/api.php',
    include 'http/web.php',
    [
        '*' => [
            'controller' => 'Phire\Http\Controller\IndexController',
            'action'     => 'route'
    ]
]);