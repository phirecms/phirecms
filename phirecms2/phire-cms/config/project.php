<?php
/**
 * Phire CMS 2.0 Project Config File
 */

$config = array(
    'base'    => realpath(__DIR__ . '/../'),
    'docroot' => realpath($_SERVER['DOCUMENT_ROOT']),
);

if ((DB_INTERFACE != '') && (DB_NAME != '')) {
    $config['databases'] = array(
        DB_NAME => \Pop\Db\Db::factory(DB_INTERFACE, array(
            'type'     => DB_TYPE,
            'database' => DB_NAME,
            'host'     => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS
        ))
    );
    $config['defaultDb'] = DB_NAME;

    // Merge any overriding project config values
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/project.php')) {
        $cfg = include $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/project.php';
        if (is_array($cfg) && (count($cfg) > 0)) {
            $config = array_merge($config, $cfg);
        }
    }
}

return new \Pop\Config($config);
