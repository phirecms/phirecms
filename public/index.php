<?php
/**
 * Phire CMS HTTP Application
 */

$autoloader = include __DIR__ . '/../vendor/autoload.php';

include __DIR__ . '/../app/config/app.config.php';

try {
    $app = new Popcorn\Pop($autoloader, include __DIR__ . '/../app/config/app.http.php');
    $app->register(new Phire\Module());
    $app->run();
} catch (\Exception $exception) {
    $app = new Phire\Module(include __DIR__ . '/../app/config/app.http.php');
    $app->httpError($exception);
}