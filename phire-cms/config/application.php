<?php

return [
    'routes'   => include 'routes.php',
    'services' => [
        'session' => [
            'call' => 'Pop\Web\Session::getInstance'
        ]
    ]
];