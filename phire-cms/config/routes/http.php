<?php

return array_merge(
    include 'http/api.php',
    include 'http/web.php',
    [
        '*' => [
            'controller' => 'App\Http\Controller\IndexController',
            'action'     => 'error'
        ]
    ]
);