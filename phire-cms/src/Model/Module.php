<?php

namespace Phire\Model;

use Phire\Table;
use Pop\Archive\Archive;
use Pop\Db\Db;
use Pop\File\Dir;
use Pop\Nav\Nav;
use Pop\Web\Session;

class Module extends AbstractModel
{

    public function getAll($moduleConfigs, $acl, $limit = null, $page = null, $sort = null)
    {
        $order = $this->getSortOrder($sort, $page);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $modules = Table\Modules::findAll(null, [
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            $modules = Table\Modules::findAll(null, [
                'order'  => $order
            ])->rows();
        }

        $sess = Session::getInstance();
        foreach ($modules as $module) {

            if (isset($moduleConfigs[$module->folder]) && isset($moduleConfigs[$module->folder]['nav.module'])) {
                $module->nav = new Nav(
                    $moduleConfigs[$module->folder]['nav.module'], ['top' => ['class' => 'module-nav']]
                );
                $module->nav->setAcl($acl);
                $module->nav->setRole($acl->getRole($sess->user->role_name));
                $module->nav->setIndent('                    ');
            } else {
                $module->nav = null;
            }
        }

        return $modules;
    }

    public function detectNew($modulePath = null, $count = true)
    {
        if ((null !== $modulePath) && file_exists($modulePath)) {
            if (substr($modulePath, -1) != DIRECTORY_SEPARATOR) {
                $modulePath .= DIRECTORY_SEPARATOR;
            }
        } else {
            $modulePath = __DIR__ . '/../../..' . CONTENT_PATH . '/modules/';
        }

        $modules    = Table\Modules::findAll();
        $installed  = [];
        $newModules = [];

        foreach ($modules->rows() as $module) {
            $installed[] = $module->file;
        }

        $dir = new Dir($modulePath, false, false, false);
        foreach ($dir->getFiles() as $file) {
            if (((substr($file, -4) == '.zip') || (substr($file, -4) == '.tgz') || (substr($file, -7) == '.tar.gz')) &&
                (!in_array($file, $installed))) {
                $newModules[] = $file;
            }
        }

        return ($count) ? count($newModules) : $newModules;
    }

    public function hasPages($limit)
    {
        return (Table\Modules::findAll()->count() > $limit);
    }

    public function getCount()
    {
        return Table\Modules::findAll()->count();
    }

    public function install($services, $modulePath = null)
    {
        if ((null !== $modulePath) && file_exists($modulePath)) {
            if (substr($modulePath, -1) != DIRECTORY_SEPARATOR) {
                $modulePath .= DIRECTORY_SEPARATOR;
            }
        } else {
            $modulePath = __DIR__ . '/../../..' . CONTENT_PATH . '/modules/';
        }

        $modules = $this->detectNew($modulePath, false);

        if (!is_writable($modulePath)) {
            throw new \Phire\Exception('Error: The module folder is not writable.');
        }

        $formats = Archive::getFormats();

        foreach ($modules as $module) {
            if (file_exists($modulePath . $module)) {
                $ext  = null;
                $name = null;
                if (substr($module, -4) == '.zip') {
                    $ext  = 'zip';
                    $name = substr($module, 0, -4);
                } else if (substr($module, -4) == '.tgz') {
                    $ext = 'tgz';
                    $name = substr($module, 0, -4);
                } else if (substr($module, -7) == '.tar.gz') {
                    $ext = 'tar.gz';
                    $name = substr($module, 0, -7);
                }

                if ((null !== $ext) && (null !== $name) && array_key_exists($ext, $formats)) {
                    $archive = new Archive($modulePath . $module);
                    $archive->extract($modulePath);
                    if ((stripos($module, 'gz') !== false) && (file_exists($modulePath . $name . '.tar'))) {
                        unlink($modulePath . $name . '.tar');
                    }

                    if (file_exists($modulePath . $name) && file_exists($modulePath . $name . '/config/module.php')) {
                        // Get SQL, if exists
                        $sqlType = strtolower(((DB_INTERFACE == 'pdo') ? DB_TYPE : DB_INTERFACE));
                        $sqlFile = $modulePath . $name . '/data/' . strtolower($name) . '.' . $sqlType . '.sql';
                        if (!file_exists($sqlFile)) {
                            $sqlFile = null;
                        }

                        // Get module info from config file
                        $info = $this->getInfo(file_get_contents($modulePath . $name . '/config/module.php'));

                        // Get any tables required and created by this module
                        $tables = (null !== $sqlFile) ? $this->getTables(file_get_contents($sqlFile)) : [];

                        // Save module in the database
                        $mod = new Table\Modules([
                            'file'   => $module,
                            'folder' => $name,
                            'active' => 1,
                            'assets' => serialize([
                                'tables' => $tables,
                                'info'   => $info
                            ])
                        ]);
                        $mod->save();

                        // Execute any SQL that came with the module
                        if (null !== $sqlFile) {
                            Db::install($sqlFile, [
                                'database' => DB_NAME,
                                'username' => DB_USER,
                                'password' => DB_PASS,
                                'host'     => DB_HOST,
                                'prefix'   => DB_PREFIX,
                                'type'     => DB_TYPE
                            ], ucfirst(strtolower(DB_INTERFACE)));
                        }

                        // Run any install functions
                        $config = include $modulePath . $name . '/config/module.php';
                        if (!empty($config[$name]['install'])) {
                            call_user_func_array($config[$name]['install'], [$services]);
                        }
                    }
                }
            }
        }
    }

    public function process($post, $services, $modulePath = null)
    {
        foreach ($post as $key => $value) {
            if (strpos($key, 'active_') !== false) {
                $id     = substr($key, (strrpos($key, '_') + 1));
                $module = Table\Modules::findById((int)$id);
                if (isset($module->id)) {
                    $module->active = (int)$value;
                    $module->save();
                }
            }
        }

        if (isset($post['rm_modules']) && (count($post['rm_modules']) > 0)) {
            $this->uninstall($post['rm_modules'], $services, $modulePath);
        }
    }

    public function uninstall($ids, $services, $modulePath = null)
    {
        if ((null !== $modulePath) && file_exists($modulePath)) {
            if (substr($modulePath, -1) != DIRECTORY_SEPARATOR) {
                $modulePath .= DIRECTORY_SEPARATOR;
            }
        } else {
            $modulePath = __DIR__ . '/../../..' . CONTENT_PATH . '/modules/';
        }

        foreach ($ids as $id) {
            $module = Table\Modules::findById((int)$id);
            if (isset($module->id)) {
                $assets = unserialize($module->assets);
                if (isset($assets['tables']) && (count($assets['tables']) > 0)) {
                    $db = $services['database'];
                    if ((DB_INTERFACE == 'mysql') || (DB_TYPE == 'mysql')) {
                        $db->query('SET foreign_key_checks = 0;');
                        foreach ($assets['tables'] as $table) {
                            $db->query('DROP TABLE ' . $table);
                        }
                        $db->query('SET foreign_key_checks = 1;');
                    } else if ((DB_INTERFACE == 'pgsql') || (DB_TYPE == 'pgsql')) {
                        foreach ($assets['tables'] as $table) {
                            $db->query('DROP TABLE ' . $table . ' CASCADE');
                        }
                    } else {
                        foreach ($assets['tables'] as $table) {
                            $db->query('DROP TABLE ' . $table);
                        }
                    }
                }

                // Run any uninstall functions
                $config = include $modulePath . $module->folder . '/config/module.php';
                if (!empty($config[$module->folder]['uninstall'])) {
                    call_user_func_array($config[$module->folder]['uninstall'], [$services]);
                }

                // Remove the module folder and files
                if (file_exists($modulePath . $module->folder)) {
                    $dir = new Dir($modulePath . $module->folder);
                    $dir->emptyDir(true);
                }

                // Remove the module file
                if (file_exists($modulePath . $module->file) &&
                    is_writable($modulePath . $module->file)) {
                    unlink($modulePath . $module->file);
                }

                // Remove any assets
                if (file_exists(__DIR__ . '/../../..' . CONTENT_PATH . '/assets/' . strtolower($module->folder))) {
                    $dir = new Dir(__DIR__ . '/../../..' . CONTENT_PATH . '/assets/' . strtolower($module->folder));
                    $dir->emptyDir(true);
                }
                $module->delete();
            }
        }
    }

    protected function getInfo($config)
    {
        $info = [];
        if (strpos($config, '*/') !== false) {
            $configHeader = substr($config, 0, strpos($config, '*/'));
            $configHeader = substr($configHeader, (strpos($configHeader, '/*') + 2));
            $configHeaderAry = explode("\n", $configHeader);
            foreach ($configHeaderAry as $line) {
                if (strpos($line, ':')) {
                    $ary = explode(':', $line);
                    if (isset($ary[0]) && isset($ary[1])) {
                        $key = trim(str_replace('*', '', $ary[0]));
                        $value = trim(str_replace('*', '', $ary[1]));
                        $info[$key] = $value;
                    }
                }
            }
        }

        return $info;
    }

    protected function getTables($sql)
    {
        $tables  = [];
        $matches = [];
        preg_match_all('/^CREATE TABLE(.*)$/mi', $sql, $matches);

        if (isset($matches[0]) && isset($matches[0][0])) {
            foreach ($matches[0] as $table) {
                if (strpos($table, '`') !== false) {
                    $table = substr($table, (strpos($table, '`') + 1));
                    $table = substr($table, 0, strpos($table, '`'));
                } else if (strpos($table, '"') !== false) {
                    $table = substr($table, (strpos($table, '"') + 1));
                    $table = substr($table, 0, strpos($table, '"'));
                } else if (strpos($table, "'") !== false) {
                    $table = substr($table, (strpos($table, "'") + 1));
                    $table = substr($table, 0, strpos($table, "'"));
                } else {
                    if (stripos($table, 'EXISTS') !== false) {
                        $table = substr($table, (stripos($table, 'EXISTS') + 6));
                    } else {
                        $table = substr($table, (stripos($table, 'TABLE') + 5));
                    }
                    if (strpos($table, '(') !== false) {
                        $table = substr($table, 0, strpos($table, '('));
                    }
                    $table = trim($table);
                }
                $tables[] = str_replace('[{prefix}]', DB_PREFIX, $table);
            }
        }

        return $tables;
    }

}