<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Archive\Archive;
use Pop\File\Dir;
use Pop\Nav\Nav;
use Pop\Project\Install\Dbs;
use Pop\Web\Cookie;
use Phire\Table;

class Extension extends AbstractModel
{

    /**
     * Get modules method
     *
     * @return array
     */
    public function getAllModules()
    {
        $modules = Table\Extensions::findAll('id ASC', array('type' => 1));
        return $modules->rows;
    }

    /**
     * Get all modules method
     *
     * @param  \Phire\Project $project
     * @return void
     */
    public function getModules(\Phire\Project $project = null)
    {
        $modules = Table\Extensions::findAll('id ASC', array('type' => 1));
        $moduleRows = $modules->rows;

        $moduleDir1 = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules', false, false, false);
        $moduleDir2 = new Dir(__DIR__ . '/../../../../../module', false, false, false);

        $dirs = array_merge($moduleDir1->getFiles(), $moduleDir2->getFiles());
        $moduleFiles = array();

        $formats = Archive::formats();
        foreach ($dirs as $file) {
            if (array_key_exists(substr($file, strrpos($file, '.') + 1), $formats)) {
                $moduleFiles[substr($file, 0, strpos($file, '.'))] = $file;
            }
        }

        foreach ($moduleRows as $key => $module) {
            $moduleName = $module->name;
            if (null !== $project) {
                $cfg = $project->module($module->name);
                if ((null !== $cfg) && (null !== $cfg->module_nav)) {
                    $n = (!is_array($cfg->module_nav)) ? $cfg->module_nav->asArray() : $cfg->module_nav;
                    $modNav = new Nav($n, array(
                        'top' => array(
                            'id'    => strtolower($module->name) . '-nav',
                            'class' => 'module-nav'
                        ))
                    );
                    $modNav->setAcl($this->data['acl']);
                    $modNav->setRole($this->data['role']);
                    $moduleRows[$key]->module_nav = $modNav;
                }
            }
            if (isset($moduleFiles[$module->name])) {
                unset($moduleFiles[$module->name]);
            }

            // Get module info
            $assets = unserialize($module->assets);
            $moduleRows[$key]->author  = '';
            $moduleRows[$key]->desc    = '';
            $moduleRows[$key]->version = '';

            foreach ($assets['info'] as $k => $v) {
                if (stripos($k, 'name') !== false) {
                    $moduleRows[$key]->name = $v;
                } else if (stripos($k, 'author') !== false) {
                    $moduleRows[$key]->author = $v;
                } else if (stripos($k, 'desc') !== false) {
                    $moduleRows[$key]->desc = $v;
                } else if (stripos($k, 'version') !== false) {
                    $moduleRows[$key]->version = $v;
                }
            }

            $latest = '';
            $handle =@ fopen('http://update.phirecms.org/modules/' . strtolower($moduleName) . '/version', 'r');
            if ($handle !== false) {
                $latest = trim(stream_get_contents($handle));
                fclose($handle);
            }
            if ((version_compare($moduleRows[$key]->version, $latest) < 0) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Config\IndexController', 'update'))) {
                $moduleRows[$key]->version .= ' (<a href="' . BASE_PATH . APP_URI . '/config/update?module=' . $moduleName . '">' . $this->i18n->__('Update to') . ' ' . $latest . '</a>?)';
            }
        }

        $this->data['modules'] = $moduleRows;
        $this->data['new'] = $moduleFiles;
    }

    /**
     * Install modules method
     *
     * @throws \Phire\Exception
     * @return void
     */
    public function installModules()
    {
        try {
            $path = BASE_PATH . APP_URI;
            if ($path == '') {
                $path = '/';
            }

            $modulePath1 = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules';
            $modulePath2 = __DIR__ . '/../../../../../module';

            $formats = Archive::formats();

            $phireCookie = null;
            foreach ($this->data['new'] as $name => $module) {
                $ext = substr($module, (strrpos($module, '.') + 1));
                if (array_key_exists($ext, $formats)) {
                    $modPath = (file_exists($modulePath1 . '/' . $module)) ? $modulePath1 : $modulePath2;

                    if (!is_writable($modPath)) {
                        throw new \Phire\Exception($this->i18n->__('The modules folder is not writable.'));
                    }

                    $archive = new Archive($modPath . '/' . $module);
                    $archive->extract($modPath . '/');
                    if ((stripos($module, 'gz') || stripos($module, 'bz')) && (file_exists($modPath . '/' . $name . '.tar'))) {
                        unlink($modPath . '/' . $name . '.tar');
                    }

                    $dbType =  Table\Extensions::getSql()->getDbType();
                    if ($dbType == \Pop\Db\Sql::SQLITE) {
                        $type = 'sqlite';
                    } else if ($dbType == \Pop\Db\Sql::PGSQL) {
                        $type = 'pgsql';
                    } else {
                        $type = 'mysql';
                    }

                    $sqlFile = $modPath . '/' .
                        $name . '/data/' . strtolower($name) . '.' . $type . '.sql';

                    $cfg    = null;
                    $tables = array();
                    $info   = array();

                    // Check for a config and try to get info out of it
                    if (file_exists($modPath . '/' . $name . '/config') && file_exists($modPath . '/' . $name . '/config/module.php')) {
                        $cfg = file_get_contents($modPath . '/' . $name . '/config/module.php');
                        if (strpos($cfg, '*/') !== false) {
                            $cfgHeader = substr($cfg, 0, strpos($cfg, '*/'));
                            $cfgHeader = substr($cfgHeader, (strpos($cfgHeader, '/*') + 2));
                            $cfgHeaderAry = explode("\n", $cfgHeader);
                            foreach ($cfgHeaderAry as $line) {
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
                    }

                    if (file_exists($sqlFile)) {
                        // Get any tables required and created by this module
                        $sql = file_get_contents($sqlFile);
                        $tables = array();
                        $matches = array();
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

                        $ext = new Table\Extensions(array(
                            'name'   => $name,
                            'file'   => $module,
                            'type'   => 1,
                            'active' => 1,
                            'assets' => serialize(array(
                                'tables' => $tables,
                                'info'   => $info
                            ))
                        ));
                        $ext->save();

                        // If DB is SQLite
                        if (stripos($type, 'Sqlite') !== false) {
                            $dbName = realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/.htphire.sqlite');
                            $dbUser = null;
                            $dbPassword = null;
                            $dbHost = null;
                            $installFile = $dbName;
                        } else {
                            $dbName = DB_NAME;
                            $dbUser = DB_USER;
                            $dbPassword = DB_PASS;
                            $dbHost = DB_HOST;
                            $installFile = null;
                        }

                        $db = array(
                            'database' => $dbName,
                            'username' => $dbUser,
                            'password' => $dbPassword,
                            'host'     => $dbHost,
                            'prefix'   => DB_PREFIX,
                            'type'     => (DB_INTERFACE == 'Pdo') ? 'Pdo_' . ucfirst(DB_TYPE) : DB_INTERFACE
                        );

                        Dbs::install($dbName, $db, $sqlFile, $installFile, true, false);
                    } else {
                        $ext = new Table\Extensions(array(
                            'name'   => $name,
                            'type'   => 1,
                            'active' => 1,
                            'assets' => serialize(array(
                                'tables' => $tables,
                                'info'   => $info
                            ))
                        ));
                        $ext->save();
                    }

                    if (null !== $cfg) {
                        $config = include $modPath . '/' . $name . '/config/module.php';
                        if (null !== $config[$name]->install) {
                            $installFunc = $config[$name]->install;
                            $installFunc();
                        }
                    }

                    if (php_sapi_name() != 'cli') {
                        $cookie = Cookie::getInstance(array('path' => $path));
                        if (isset($cookie->phire)) {
                            if (null === $phireCookie) {
                                $phireCookie = $cookie->phire;
                            }
                            $i18n = (file_exists($modPath . '/' . $name . '/data/assets/i18n'));
                            $modules = (array)$phireCookie->modules;
                            $modules[] = array('name' => $name, 'i18n' => $i18n);
                            $phireCookie->modules = $modules;
                        }
                    }
                }
            }
            if (null !== $phireCookie) {
                $cookie = Cookie::getInstance(array('path' => $path));
                $cookie->set('phire', $phireCookie);
            }
        } catch (\Exception $e) {
            $this->data['error'] = $e->getMessage();
        }
    }

    /**
     * Process themes method
     *
     * @param  array $post
     * @return void
     */
    public function processModules($post)
    {
        foreach ($post as $key => $value) {
            if (strpos($key, 'module_active_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $ext = Table\Extensions::findById($id);
                if (isset($ext->id)) {
                    $ext->active = (int)$value;
                    $ext->save();
                }
            }
        }

        $path = BASE_PATH . APP_URI;
        if ($path == '') {
            $path = '/';
        }

        $modulePath1 = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules';
        $modulePath2 = __DIR__ . '/../../../../../module';

        $phireCookie = null;

        if (php_sapi_name() != 'cli') {
            $cookie = Cookie::getInstance(array('path' => $path));
            if (isset($cookie->phire)) {
                if (null === $phireCookie) {
                    $phireCookie = $cookie->phire;
                }
            }
        }

        if (isset($post['remove_modules'])) {
            foreach ($post['remove_modules'] as $id) {
                $ext = Table\Extensions::findById($id);
                if (isset($ext->id)) {
                    $modPath = (file_exists($modulePath1 . '/' . $ext->file)) ? $modulePath1 : $modulePath2;
                    $assets  = unserialize($ext->assets);

                    if (count($assets['tables']) > 0) {
                        $db = Table\Extensions::getDb();
                        if ((DB_INTERFACE == 'Mysqli') || (DB_TYPE == 'mysql')) {
                            $db->adapter()->query('SET foreign_key_checks = 0;');
                            foreach ($assets['tables'] as $table) {
                                $db->adapter()->query('DROP TABLE ' . $table);
                            }
                            $db->adapter()->query('SET foreign_key_checks = 1;');
                        } else if ((DB_INTERFACE == 'Pgsql') || (DB_TYPE == 'pgsql')) {
                            foreach ($assets['tables'] as $table) {
                                $db->adapter()->query('DROP TABLE ' . $table . ' CASCADE');
                            }
                        } else {
                            foreach ($assets['tables'] as $table) {
                                $db->adapter()->query('DROP TABLE ' . $table);
                            }
                        }
                    }

                    $contentPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH;
                    $exts = array('.zip', '.tar.gz', '.tar.bz2', '.tgz', '.tbz', '.tbz2');

                    // Check for a config and remove function
                    if (file_exists($modPath . '/' . $ext->name . '/config') && file_exists($modPath . '/' . $ext->name . '/config/module.php')) {
                        $config = include $modPath . '/' . $ext->name . '/config/module.php';
                        if (null !== $config[$ext->name]->remove) {
                            $removeFunc = $config[$ext->name]->remove;
                            $removeFunc();
                        }
                    }

                    if (file_exists($contentPath . '/extensions/modules/' . $ext->name)) {
                        $dir = new Dir($contentPath . '/extensions/modules/' . $ext->name);
                        $dir->emptyDir(null, true);
                    }

                    foreach ($exts as $e) {
                        if (file_exists($contentPath . '/extensions/modules/' . $ext->name . $e) &&
                            is_writable($contentPath . '/extensions/modules/' . $ext->name . $e)) {
                            unlink($contentPath . '/extensions/modules/' . $ext->name . $e);
                        }
                    }

                    if (file_exists(__DIR__ . '/../../../../../module/' . $ext->name)) {
                        $dir = new Dir(__DIR__ . '/../../../../../module/' . $ext->name);
                        $dir->emptyDir(null, true);
                    }

                    foreach ($exts as $e) {
                        if (file_exists(__DIR__ . '/../../../../../module/' . $ext->name . $e) &&
                            is_writable(__DIR__ . '/../../../../../module/' . $ext->name . $e)) {
                            unlink(__DIR__ . '/../../../../../module/' . $ext->name . $e);
                        }
                    }

                    if (file_exists($contentPath . '/assets/' . strtolower($ext->name))) {
                        $dir = new Dir($contentPath . '/assets/' . strtolower($ext->name));
                        $dir->emptyDir(null, true);
                    }

                    if (null !== $phireCookie) {
                        foreach ($phireCookie->modules as $key => $value) {
                            if ($value->name == $ext->name) {
                                $modules = (array)$phireCookie->modules;
                                unset($modules[$key]);
                                $phireCookie->modules = $modules;
                            }
                        }
                    }

                    $ext->delete();
                }
            }
        }

        if (null !== $phireCookie) {
            $cookie = Cookie::getInstance(array('path' => $path));
            $cookie->set('phire', $phireCookie);
        }
    }

}

