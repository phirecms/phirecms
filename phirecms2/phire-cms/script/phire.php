#!/usr/bin/php
<?php
/**
 * Phire CMS 2.0 BASH CLI script
 */

set_time_limit(0);

define('PH_CLI_ROOT', __DIR__ . '/../..');

require_once PH_CLI_ROOT . '/bootstrap.php';

if (isset($argv[1]) && ($argv[1] == 'post')) {
    \Phire\Cli::postInstall();
} else {
    // Write header
    echo PHP_EOL;
    echo 'Phire CMS 2 CLI' . PHP_EOL;
    echo '===============' . PHP_EOL . PHP_EOL;

    $cli = new \Phire\Cli($argv);
}
