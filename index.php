<?php

require_once __DIR__  . '/config.php';
$autoloader = require __DIR__  . APP_PATH . '/vendor/autoload.php';

try {
    $app = new Phire\Application(
        $autoloader,
        include __DIR__ . APP_PATH . '/config/application.php'
    );
    $app->run();
} catch (Exception $exception) {
    echo $exception->getMessage();
}
