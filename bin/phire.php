#!/usr/bin/php
<?php
/**
 * Phire CMS 2.0 BASH CLI script
 */

set_time_limit(0);

require_once __DIR__  . '/../vendor/autoload.php';

$cli = new \Phire\Cli($argv);
