<?php
/**
 * Phire CMS 2.0 Index File
 */
try {
    require_once 'bootstrap.php';
    $project->load($autoloader)
            ->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
