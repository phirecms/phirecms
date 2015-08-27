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

    /**
     * Get all modules
     *
     * @param  \Pop\Module\Manager $moduleManager
     * @param  \Pop\Acl\Acl        $acl
     * @param  int                 $limit
     * @param  int                 $page
     * @param  string              $sort
     * @return array
     */
    public function getAll(\Pop\Module\Manager $moduleManager, \Pop\Acl\Acl $acl, $limit = null, $page = null, $sort = null)
    {
        $order = (null !== $sort) ? $this->getSortOrder($sort, $page) : 'order, id ASC';

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
            if (isset($moduleManager[$module->folder]) && isset($moduleManager[$module->folder]->config()['nav.module'])) {
                $module->nav = new Nav(
                    [$moduleManager[$module->folder]->config()['nav.module']], ['top' => ['class' => 'module-nav']]
                );
                $module->nav->setBaseUrl(BASE_PATH . APP_URI);
                $module->nav->setAcl($acl);
                $module->nav->setRole($acl->getRole($sess->user->role));
                $module->nav->setIndent('                    ');
            } else {
                $module->nav = null;
            }
        }

        return $modules;
    }

    /**
     * Detect new modules
     *
     * @param  boolean $count
     * @return mixed
     */
    public function detectNew($count = true)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . MODULES_PATH;
        $installed  = [];
        $newModules = [];

        if (file_exists($modulesPath)) {
            $modules = Table\Modules::findAll();

            foreach ($modules->rows() as $module) {
                $installed[] = $module->file;
            }

            $dir = new Dir($modulesPath, false, false, false);
            foreach ($dir->getFiles() as $file) {
                if (((substr($file, -4) == '.zip') || (substr($file, -4) == '.tgz') ||
                        (substr($file, -7) == '.tar.gz')) && (!in_array($file, $installed))
                ) {
                    $newModules[] = $file;
                }
            }
        }

        return ($count) ? count($newModules) : $newModules;
    }

    /**
     * Determine if list of modules have pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Modules::findAll()->count() > $limit);
    }

    /**
     * Get count of modules
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Modules::findAll()->count();
    }

    /**
     * Install modules
     *
     * @param  \Pop\Service\Locator $services
     * @throws \Exception
     * @return void
     */
    public function install(\Pop\Service\Locator $services)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . MODULES_PATH;
        $modules    = $this->detectNew(false);

        if (!is_writable($modulesPath)) {
            throw new \Phire\Exception('Error: The module folder is not writable.');
        }

        $formats = Archive::getFormats();

        foreach ($modules as $module) {
            if (file_exists($modulesPath . '/' . $module)) {
                $ext  = null;
                $name = null;
                if (substr($module, -4) == '.zip') {
                    $ext  = 'zip';
                    $name = substr($module, 0, -4);
                } else if (substr($module, -4) == '.tgz') {
                    $ext  = 'tgz';
                    $name = substr($module, 0, -4);
                } else if (substr($module, -7) == '.tar.gz') {
                    $ext  = 'tar.gz';
                    $name = substr($module, 0, -7);
                }

                if ((null !== $ext) && (null !== $name) && array_key_exists($ext, $formats)) {
                    $archive = new Archive($modulesPath . '/' . $module);
                    $archive->extract($modulesPath);
                    if ((stripos($module, 'gz') !== false) && (file_exists($modulesPath . '/' . $name . '.tar'))) {
                        unlink($modulesPath . '/' . $name . '.tar');
                    }

                    if (file_exists($modulesPath . '/' . $name) &&
                        file_exists($modulesPath . '/' . $name . '/config/module.php')) {
                        // Get SQL, if exists
                        $sqlType = strtolower(((DB_INTERFACE == 'pdo') ? DB_TYPE : DB_INTERFACE));
                        $sqlFile = $modulesPath . '/' . $name . '/data/' . $name . '.' . $sqlType . '.sql';
                        if (!file_exists($sqlFile)) {
                            $sqlFile = null;
                        }

                        // Get module info from config file
                        $info   = $this->getInfo(file_get_contents($modulesPath . '/' . $name . '/config/module.php'));
                        $tables = (null !== $sqlFile) ? $this->getTables(file_get_contents($sqlFile)) : [];
                        $config = include $modulesPath . '/' . $name . '/config/module.php';

                        // Save module in the database
                        $mod = new Table\Modules([
                            'file'   => $module,
                            'folder' => $name,
                            'prefix' => $config[$name]['prefix'],
                            'active' => 1,
                            'order'  => (int)Table\Modules::findAll()->count() + 1,
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
                        if (!empty($config[$name]['install'])) {
                            call_user_func_array($config[$name]['install'], [$services]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Process modules
     *
     * @param  array                $post
     * @param  \Pop\Service\Locator $services
     * @return void
     */
    public function process($post, \Pop\Service\Locator $services)
    {
        foreach ($post as $key => $value) {
            if (strpos($key, 'active_') !== false) {
                $id     = substr($key, (strrpos($key, '_') + 1));
                $module = Table\Modules::findById((int)$id);
                if (isset($module->id)) {
                    $module->active = (int)$value;
                    $module->order  = (int)$post['order_' . $id];
                    $module->save();
                }
            }
        }

        if (isset($post['rm_modules']) && (count($post['rm_modules']) > 0)) {
            $this->uninstall($post['rm_modules'], $services);
        }
    }

    /**
     * Uninstall modules
     *
     * @param  array                $ids
     * @param  \Pop\Service\Locator $services
     * @return void
     */
    public function uninstall($ids, $services)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . MODULES_PATH;

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
                $config = include $modulesPath . '/' . $module->folder . '/config/module.php';
                if (!empty($config[$module->folder]['uninstall'])) {
                    call_user_func_array($config[$module->folder]['uninstall'], [$services]);
                }

                // Remove the module folder and files
                if (file_exists($modulesPath . '/' . $module->folder)) {
                    $dir = new Dir($modulesPath . '/' . $module->folder);
                    $dir->emptyDir(true);
                }

                // Remove the module file
                if (file_exists($modulesPath . '/' . $module->file) &&
                    is_writable($modulesPath . '/' . $module->file)) {
                    unlink($modulesPath . '/' . $module->file);
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

    /**
     * Get module info
     *
     * @param  string $config
     * @return array
     */
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
                        $key        = trim(str_replace('*', '', $ary[0]));
                        $value      = trim(str_replace('*', '', $ary[1]));
                        $info[$key] = $value;
                    }
                }
            }
        }

        return $info;
    }

    /**
     * Get module tables
     *
     * @param  string $sql
     * @return array
     */
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