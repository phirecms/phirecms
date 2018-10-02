<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire;

use Phire\Table;
use Pop\Acl\Resource\Resource;
use Pop\Acl\Role\Role;
use Pop\Application;
use Pop\Db\Record;
use Pop\File\Dir;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\View\View;

/**
 * Main Phire Module class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.1.0
 */
class Module extends Module\Module
{

    /**
     * Phire version
     */
    const VERSION = '2.1.0';

    /**
     * JS and CSS assets
     * @var array
     */
    protected $assets = [
        'js'  => [],
        'css' => [
            'link'   => [],
            'import' => []
        ]
    ];

    /**
     * Initialize the application
     *
     * @param  Application $application
     * @throws Exception
     * @return Module
     */
    public function register(Application $application)
    {
        parent::register($application);

        // Set the database
        if ($this->application->services()->isAvailable('database')) {
            Record::setDb($this->application->getService('database'));
            $db = (count($this->application->getService('database')->getTables()) > 0);
        } else {
            $db = false;
        }

        $this->application->mergeConfig(['db' => $db]);

        // Load assets, if they haven't been loaded already
        $this->loadAssets(__DIR__ . '/../data/assets', 'phire');

        if ($db) {
            $systemTheme = Table\Config::findById('system_theme')->value;
            if (file_exists(CONTENT_ABS_PATH . '/phire/themes/' . $systemTheme)) {
                $this->loadAssets(CONTENT_ABS_PATH . '/phire/themes/' . $systemTheme, $systemTheme);
            } else if (file_exists(__DIR__ . '/../data/themes/' . $systemTheme)) {
                $this->loadAssets(__DIR__ . '/../data/themes/' . $systemTheme, $systemTheme);
            }
        } else {
            $this->loadAssets(__DIR__ . '/../data/themes/default', 'default');
        }

        sort($this->assets['js']);
        sort($this->assets['css']['link']);
        sort($this->assets['css']['import']);

        // Load any custom/override assets
        $this->loadAssets(CONTENT_ABS_PATH . '/phire/assets', 'phire-custom', true);

        // Check PHP version
        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            throw new Exception('Error: Phire CMS requires PHP 5.4.0 or greater.');
        }

        // Add route params for the controllers
        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'request'     => new Request(),
                    'response'    => new Response()
                ]
            );
        }

        // Set up triggers to check the application session
        $this->application->on('app.route.pre', 'Phire\Event\Ssl::check', 1000)
             ->on('app.route.post', 'Phire\Event\Db::check', 1000)
             ->on('app.dispatch.pre', 'Phire\Event\Session::check', 1001)
             ->on('app.dispatch.pre', 'Phire\Event\Acl::check', 1000);

        // Add roles to user nav
        $this->addRoles();

        // Register modules
        $this->registerModules();

        return $this;
    }

    /**
     * Error handler
     *
     * @param  \Exception $exception
     * @return void
     */
    public function error(\Exception $exception)
    {
        if (($exception instanceof \Phire\Exception) && ($exception->isInstallError())) {
            Response::redirect(BASE_PATH . APP_URI . '/install');
            exit();
        }

        // Load assets, if they haven't been loaded already
        $this->loadAssets($_SERVER['DOCUMENT_ROOT'] . APP_PATH . '/data/themes/default', 'default');
        $this->loadAssets(__DIR__ . '/../data/assets', 'phire');
        sort($this->assets['js']);
        sort($this->assets['css']['link']);
        sort($this->assets['css']['import']);

        // Load any custom/override assets
        $this->loadAssets(CONTENT_ABS_PATH . '/phire/assets', 'phire-custom', true);

        $view = new View(__DIR__ . '/../view/phire/exception.phtml');
        $view->title        = 'Application Error';
        $view->systemTitle  = 'Phire CMS';
        $view->assets       = $this->assets;
        $view->phireUri     = BASE_PATH . APP_URI;
        $view->basePath     = BASE_PATH;
        $view->base_path    = BASE_PATH;
        $view->contentPath  = BASE_PATH . CONTENT_PATH;
        $view->content_path = BASE_PATH . CONTENT_PATH;
        $view->message      = htmlentities(strip_tags($exception->getMessage()), ENT_QUOTES, 'UTF-8');

        $response = new Response();
        $response->setBody((string)$view);
        $response->send();
    }

    /**
     * Add user roles to navigation
     *
     * @return void
     */
    public function addRoles()
    {
        if ($this->application->config()['db']) {
            $params = $this->application->services()->getParams('nav.phire');
            $roles  = Table\Roles::findAll();

            foreach ($roles->rows() as $role) {
                if (!isset($params['tree']['users']['children'])) {
                    $params['tree']['users']['children'] = [];
                }
                $params['tree']['users']['children']['users-of-role-' . $role->id] = [
                    'name' => $role->name,
                    'href' => '/users/' . $role->id,
                    'acl' => [
                        'resource' => 'users-of-role-' . $role->id,
                        'permission' => 'index'
                    ]
                ];
            }

            $this->application->services()->setParams('nav.phire', $params);
        }
    }

    /**
     * Load application modules
     *
     * @return Module
     */
    public function registerModules()
    {
        if ($this->application->config()['db']) {
            $modulesPath   = MODULES_ABS_PATH;
            $moduleFolders = [];

            $modules = Table\Modules::findBy(['active' => 1], ['order' => 'order DESC']);
            foreach ($modules->rows() as $module) {
                if (file_exists($modulesPath . '/' . $module->folder . '/src/Module.php')) {
                    include $modulesPath . '/' . $module->folder . '/src/Module.php';
                    $moduleClass = $module->prefix . 'Module';
                } else {
                    $moduleClass = 'Phire\Module\Module';
                }

                if (file_exists($modulesPath . '/' . $module->folder . '/config/module.php')) {
                    $moduleConfig = include $modulesPath . '/' . $module->folder . '/config/module.php';

                    // Load and register each module
                    if (file_exists(CONTENT_ABS_PATH . '/phire/config/' . $module->name . '.php')) {
                        $moduleConfig = array_merge(
                            $moduleConfig[$module->name], include CONTENT_ABS_PATH . '/phire/config/' . $module->name . '.php'
                        );
                    } else {
                        $moduleConfig = $moduleConfig[$module->name];
                    }

                    if (strpos($modulesPath, 'vendor') !== false) {
                        unset($moduleConfig['prefix']);
                        unset($moduleConfig['src']);
                    }
                    $newModule = new $moduleClass($moduleConfig, $this->application);
                } else {
                    $newModule = new $moduleClass($this->application);
                }

                $this->application->register($module->name, $newModule);
                $moduleFolders[$module->name] = $module->folder;
            }

            // Check module configs for Phire-specific configs
            foreach ($this->application->modules() as $module => $config) {
                // Load module assets
                if (isset($moduleFolders[$module]) && file_exists($modulesPath . '/' . $moduleFolders[$module] . '/data/assets')) {
                    $this->loadAssets(
                        $modulesPath . '/' . $moduleFolders[$module] . '/data/assets',
                        strtolower($module)
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Get application assets
     *
     * @param  string $type
     * @return array
     */
    public function getAssets($type = null)
    {
        return ((null !== $type) && isset($this->assets[$type])) ? $this->assets[$type] : $this->assets;
    }

    /**
     * Load application assets to a public folder
     *
     * @param  string  $from
     * @param  string  $to
     * @param  boolean $import
     * @return Module
     */
    public function loadAssets($from, $to, $import = false)
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets') &&
            is_writable($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets')) {

            $toDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to;
            if (!file_exists($toDir)) {
                mkdir($toDir);
                $dir = new Dir($from, [
                    'absolute'  => true,
                    'recursive' => true
                ]);
                $dir->copyDir($toDir, false);
            }

            $cssDirs     = ['css', 'styles', 'style'];
            $jsDirs      = ['js', 'scripts', 'script', 'scr'];
            $cssType     = ($import) ? 'import' : 'link';

            foreach ($cssDirs as $cssDir) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to .'/' . $cssDir)) {
                    $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to .'/' . $cssDir);
                    foreach ($dir->getFiles() as $cssFile) {
                        if ($cssFile != 'index.html') {
                            $css = BASE_PATH . CONTENT_PATH . '/assets/' . $to . '/' . $cssDir . '/' . $cssFile;
                            if (!in_array($css, $this->assets['css'][$cssType]) && (substr($css, -4) == '.css') &&
                                (stripos($css, 'public') === false)) {
                                $this->assets['css'][$cssType][] = $css;
                            }
                        }
                    }
                }
            }

            foreach ($jsDirs as $jsDir) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to .'/' . $jsDir)) {
                    $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to .'/' . $jsDir);
                    foreach ($dir->getFiles() as $jsFile) {
                        if ($jsFile != 'index.html') {
                            $js = BASE_PATH . CONTENT_PATH . '/assets/' . $to . '/' . $jsDir . '/' . $jsFile;
                            if (!in_array($js, $this->assets['js']) && (substr($js, -3) == '.js') &&
                                (stripos($js, 'public') === false)) {
                                $this->assets['js'][] = $js;
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Initialize the ACL service
     *
     * @return Module
     */
    public function initAcl()
    {
        $roles = Table\Roles::findAll()->rows();
        $resources = $this->application->config()['resources'];
        foreach ($roles as $role) {
            $roleName = str_replace(' ', '-', strtolower($role->name));
            $resources['role-' . $role->id . '|role-' . $roleName] = [
                'edit', 'remove'
            ];
            $resources['users-of-role-' . $role->id . '|users-of-role-' . $roleName] = [
                'index', 'add', 'edit', 'remove'
            ];
        }

        $this->application->mergeConfig(['resources' => $resources]);

        foreach ($this->application->config()['resources'] as $resource => $permissions) {
            if (strpos($resource, '|') !== false) {
                $resource = substr($resource, 0, strpos($resource, '|'));
            }
            $this->application->getService('acl')->addResource(new Resource($resource));
        }

        $allRoles  = [];

        foreach ($roles as $role) {
            $r = new Role($role->name);
            $allRoles[$role->id] = $r;
            $this->application->getService('acl')->addRole($r);

            if (null !== $role->permissions) {
                $role->permissions = unserialize($role->permissions);
            }
            if ((null === $role->permissions) || (is_array($role->permissions) && (count($role->permissions) == 0))) {
                $this->application->getService('acl')->allow($role->name);
            } else {
                if (count($role->permissions['allow']) > 0) {
                    foreach ($role->permissions['allow'] as $allow) {
                        $this->application->getService('acl')->allow($role->name, $allow['resource'], $allow['permission']);
                    }
                } else {
                    $this->application->getService('acl')->allow($role->name);
                }
                if (count($role->permissions['deny']) > 0) {
                    foreach ($role->permissions['deny'] as $deny) {
                        $this->application->getService('acl')->deny($role->name, $deny['resource'], $deny['permission']);
                    }
                }
            }
        }

        // Set up parent/child roles
        foreach ($allRoles as $id => $child) {
            $r = \Phire\Table\Roles::findById($id);
            if (isset($r->id) && (null !== $r->parent_id) && isset($allRoles[$r->parent_id])) {
                $child->setParent($allRoles[$r->parent_id]);
            }
        }

        // Set the acl in the main nav object
        $this->application->getService('nav.phire')->setAcl($this->application->getService('acl'));

        return $this;
    }

    /**
     * Compares the local version to the latest version available
     *
     * @param  string $version
     * @return mixed
     */
    public static function compareVersion($version)
    {
        return version_compare(self::VERSION, $version);
    }

    /**
     * Returns the latest version available.
     *
     * @return mixed
     */
    public static function getLatest()
    {
        $latest = null;

        $handle = fopen('http://www.phirecms.org/version', 'r');
        if ($handle !== false) {
            $latest = stream_get_contents($handle);
            fclose($handle);
        }

        return trim($latest);
    }

    /**
     * Returns whether or not this is the latest version.
     *
     * @return mixed
     */
    public static function isLatest()
    {
        return (self::compareVersion(self::getLatest()) >= 0);
    }

}
