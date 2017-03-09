<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Phar;
use PharData;
use ZipArchive;
use Phire\Table;
use Pop\Db\Db;
use Pop\Dir\Dir;
use Pop\Http\Client\Curl;
use Pop\Http\Upload;

/**
 * Module model class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Module extends AbstractModel
{

    /**
     * Get all modules
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return \Pop\Db\Record\Collection
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order   = $this->getSortOrder($sort, $page);
        $options = ['order'  => $order];

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $options['offset'] = $page;
            $options['limit']  = $limit;
        }

        return Table\Modules::findAll($options);
    }


    /**
     * Get module by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $module = Table\Modules::findById($id);
        if (isset($module->id)) {
            $data = $module->toArray();
            $data['assets'] = unserialize($data['assets']);
            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Detect new modules
     *
     * @param  boolean $count
     * @return mixed
     */
    public function detectNew($count = true)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';
        $installed   = [];
        $newModules  = [];

        if (file_exists($modulesPath)) {
            $modules = Table\Modules::findAll();
            foreach ($modules as $module) {
                $installed[] = $module->file;
            }

            $dir = new Dir($modulesPath, ['filesOnly' => true]);

            foreach ($dir as $file) {
                if (((substr($file, -4) == '.zip') || (substr($file, -4) == '.tgz') ||
                     (substr($file, -7) == '.tar.gz') || (substr($file, -4) == '.tbz') ||
                     (substr($file, -8) == '.tar.bz2')) && !in_array($file, $installed)
                ) {
                    $newModules[] = $file;
                }
            }
        }

        return ($count) ? count($newModules) : $newModules;
    }

    /**
     * Upload module
     *
     * @param  array $file
     * @return void
     */
    public function upload($file)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';
        $upload = new Upload($modulesPath);
        $upload->overwrite(true);
        $upload->upload($file);
    }


    /**
     * Install modules
     *
     * @param  \Pop\Service\Locator $services
     * @throws \Phire\Exception
     * @return void
     */
    public function install(\Pop\Service\Locator $services)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';
        $modules     = $this->detectNew(false);

        if (!is_writable($modulesPath)) {
            throw new \Phire\Exception('Error: The module folder is not writable.');
        }

        foreach ($modules as $module) {
            if (file_exists($modulesPath . '/' . $module)) {
                $compression = null;
                $folder      = null;
                if (substr($module, -4) == '.zip') {
                    $compression = 'zip';
                    $folder      = substr($module, 0, -4);
                } else if (substr($module, -7) == '.tar.gz') {
                    $compression = 'gz';
                    $folder      = substr($module, 0, -7);
                } else if (substr($module, -8) == '.tar.bz2') {
                    $compression = 'bz';
                    $folder      = substr($module, 0, -8);
                }

                if (($compression == 'zip') && !class_exists('ZipArchive')) {
                    throw new \Phire\Exception('Error: Zip archives are not supported on this system.');
                } else if (($compression == 'gz') && !Phar::canCompress(Phar::GZ)) {
                    throw new \Phire\Exception('Error: Gzip compression is not available on this system.');
                } else if (($compression == 'bz') && !Phar::canCompress(Phar::BZ2)) {
                    throw new \Phire\Exception('Error: Bzip2 compression is not available on this system.');
                } else if ((null === $compression) || (null === $folder)) {
                    throw new \Phire\Exception('Error: Could not detect module file or compression type.');
                }

                if ($compression == 'zip') {
                    $archive = new ZipArchive();
                    $archive->open($modulesPath . '/' . $module);
                    $archive->extractTo($modulesPath);
                    $archive->close();
                } else {
                    $archive = new PharData($modulesPath . '/' . $module);
                    $archive->decompress('.tar');
                    $archive->extractTo($modulesPath);
                }

                if (file_exists($modulesPath . '/' . $folder) &&
                    file_exists($modulesPath . '/' . $folder . '/config/module.php')) {
                    $this->finalizeInstall($module, $folder, $modulesPath, $services);
                }
            }
        }

        // Clean up TAR balls
        $tars = scandir($modulesPath);
        foreach ($tars as $tar) {
            if (substr($tar, -4) == '.tar') {
                unlink($modulesPath . '/' . $tar);
            }
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
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';

        foreach ($ids as $id) {
            $module = Table\Modules::findById((int)$id);
            if (isset($module->id)) {
                $assets = unserialize($module->assets);
                if (isset($assets['tables']) && (count($assets['tables']) > 0)) {
                    $db = $services['database'];
                    if ((DB_ADAPTER == 'mysql') || (DB_TYPE == 'mysql')) {
                        $db->query('SET foreign_key_checks = 0;');
                        foreach ($assets['tables'] as $table) {
                            $db->query('DROP TABLE ' . $table);
                        }
                        $db->query('SET foreign_key_checks = 1;');
                    } else if ((DB_ADAPTER == 'pgsql') || (DB_TYPE == 'pgsql')) {
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
                if (isset($config[$module->name]) && isset($config[$module->name]['uninstall']) &&
                    !empty($config[$module->name]['uninstall'])) {
                    call_user_func_array($config[$module->name]['uninstall'], [$services]);
                }

                // Remove any assets
                if (file_exists(__DIR__ . '/../../..' . CONTENT_PATH . '/assets/' . strtolower($module->name))) {
                    $dir = new Dir(__DIR__ . '/../../..' . CONTENT_PATH . '/assets/' . strtolower($module->name));
                    $dir->emptyDir(true);
                }

                if (strpos($modulesPath, 'vendor') === false) {
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
                }

                $module->delete();
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
     * Determine if list of modules has pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Modules::findAll(null, Table\Modules::AS_ARRAY)->count() > $limit);
    }

    /**
     * Get count of modules
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Modules::findAll(null, Table\Modules::AS_ARRAY)->count();
    }


    /**
     * Finalize module install
     *
     * @param  string               $module
     * @param  string               $folder
     * @param  string               $modulesPath
     * @param  \Pop\Service\Locator $services
     * @return void
     */
    protected function finalizeInstall($module, $folder, $modulesPath, $services)
    {
        // Get module config and module info from config file
        $config   = include $modulesPath . '/' . $folder . '/config/module.php';
        $info     = $this->getInfo(file_get_contents($modulesPath . '/' . $folder . '/config/module.php'));
        $name     = key($config);
        $descName = '';

        if (isset($info['name'])) {
            $descName = $info['name'];
        } else if (isset($info['Name'])) {
            $descName = $info['Name'];
        } else if (isset($info['NAME'])) {
            $descName = $info['NAME'];
        } else if (isset($info['module name'])) {
            $descName = $info['module name'];
        } else if (isset($info['Module Name'])) {
            $descName = $info['Module Name'];
        } else if (isset($info['MODULE NAME'])) {
            $descName = $info['MODULE NAME'];
        }

        $info['Desc Name'] = $descName;

        if (isset($info['version'])) {
            $version = $info['version'];
        } else if (isset($info['Version'])) {
            $version = $info['Version'];
        } else if (isset($info['VERSION'])) {
            $version = $info['VERSION'];
        } else {
            $version = 'N/A';
        }

        if (isset($info['Author'])) {
            $author = $info['Author'];
        } else if (isset($info['author'])) {
            $author = $info['author'];
        } else {
            $author = null;
        }

        if (isset($info['Description'])) {
            $description = $info['Description'];
        } else if (isset($info['description'])) {
            $description = $info['description'];
        } else if (isset($info['Desc'])) {
            $description = $info['Desc'];
        } else if (isset($info['desc'])) {
            $description = $info['desc'];
        } else {
            $description = null;
        }

        // Get SQL, if exists
        $sqlType = strtolower(((DB_ADAPTER == 'pdo') ? DB_TYPE : DB_ADAPTER));
        $sqlFile = $modulesPath . '/' . $folder . '/data/' . $name . '.' . $sqlType . '.sql';
        if (!file_exists($sqlFile)) {
            $sqlFile = null;
        }

        $tables = (null !== $sqlFile) ? $this->getTables(file_get_contents($sqlFile)) : [];

        // Save module in the database
        $mod = new Table\Modules([
            'file'        => $module,
            'folder'      => $folder,
            'name'        => $name,
            'prefix'      => (isset($config[$name]['prefix'])) ? $config[$name]['prefix'] : '',
            'version'     => $version,
            'description' => $description,
            'author'      => $author,
            'active'      => 1,
            'order'       => (int)Table\Modules::findAll()->count() + 1,
            'assets'      => serialize([
                'tables'  => $tables,
                'info'    => $info
            ]),
            'installed'   => date('Y-m-d H:i:s')
        ]);
        $mod->save();

        $this->sendStats($name, $version);

        // Execute any SQL that came with the module
        if (null !== $sqlFile) {
            Db::install($sqlFile, ucfirst(strtolower(DB_ADAPTER)), [
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASS,
                'host'     => DB_HOST,
                'prefix'   => DB_PREFIX,
                'type'     => DB_TYPE
            ]);
        }

        // Run any install functions
        if (isset($config[$name]) && isset($config[$name]['install']) && !empty($config[$name]['install'])) {
            call_user_func_array($config[$name]['install'], [$services]);
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
            $configHeader    = substr($config, 0, strpos($config, '*/'));
            $configHeader    = substr($configHeader, (strpos($configHeader, '/*') + 2));
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

    /**
     * Send installation stats
     *
     * @param  string $name
     * @param  string $version
     * @return void
     */
    protected function sendStats($name, $version)
    {
        $headers = [
            'Authorization: ' . base64_encode('phire-stats-' . time()),
            'User-Agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ?
                $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0')
        ];

        $curl = new Curl('http://stats.phirecms.org/module', [
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $curl->setPost();
        $curl->setFields([
            'name'      => $name,
            'version'   => $version,
            'domain'    => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
            'ip'        => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
            'os'        => PHP_OS,
            'server'    => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : ''),
            'php'       => PHP_VERSION,
            'db'        => DB_ADAPTER . ((DB_ADAPTER == 'pdo') ? ' (' . DB_TYPE . ')' : '')
        ]);

        $curl->send();
    }

}