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
namespace Phire;

use Phire\Table;
use Pop\Acl\AclResource as Resource;
use Pop\Acl\AclRole as Role;
use Pop\Application;
use Pop\Db\Record;
use Pop\Dir\Dir;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\View\View;

/**
 * Main module class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Module extends \Pop\Module\Module
{

    /**
     * Phire Version
     * @var string
     */
    const VERSION = '3.0.0b';

    /**
     * Module name
     * @var string
     */
    protected $name = 'phire';

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
     * Register module
     *
     * @param  Application $application
     * @throws Exception
     * @return Module
     */
    public function register(Application $application)
    {
        parent::register($application);

        if ($this->application->router->isCli()) {
            $this->registerCli();
        } else {
            $this->registerWeb();
        }

        if ($this->application->services()->isAvailable('database')) {
            $this->registerModules();
        }

        return $this;
    }

    /**
     * Register the web app components
     *
     * @throws Exception
     */
    public function registerWeb()
    {
        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'request'     => new Request(),
                    'response'    => new Response()
                ]
            );
        }

        if (!empty($this->application->config()['database']) && !empty($this->application->config()['database']['adapter'])) {
            $adapter = $this->application->config()['database']['adapter'];
            $options = [
                'database' => $this->application->config()['database']['database'],
                'username' => $this->application->config()['database']['username'],
                'password' => $this->application->config()['database']['password'],
                'host'     => $this->application->config()['database']['host'],
                'type'     => $this->application->config()['database']['type']
            ];

            $check = \Pop\Db\Db::check($adapter, $options);

            if (null !== $check) {
                throw new Exception('DB ' . $check);
            }

            $this->application->services()->set('database', [
                'call'   => 'Pop\Db\Db::connect',
                'params' => [
                    'adapter' => $adapter,
                    'options' => $options
                ]
            ]);
        }

        if (isset($this->application->config['forms'])) {
            $this->application->mergeConfig(['forms' => $this->application->config['forms']]);
        }

        if (isset($this->application->config['resources'])) {
            $this->application->mergeConfig(['resources' => $this->application->config['resources']]);
        }

        $this->application->on('app.dispatch.pre', 'Phire\Event\Db::check', 1002)
            ->on('app.dispatch.pre', 'Phire\Event\Session::check', 1001)
            ->on('app.dispatch.pre', 'Phire\Event\Acl::check', 1000);

        if ($this->application->services()->isAvailable('database')) {
            Record::setDb($this->application->getService('database'));
            $this->initNav();
        }

    }

    /**
     * Register the CLI app components
     *
     * @throws Exception
     */
    public function registerCli()
    {
        // Add controller params
        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'console'     => new \Pop\Console\Console(120, '    ')
                ]
            );
        }

        if (!empty($this->application->config()['database']) && !empty($this->application->config()['database']['adapter'])) {
            $adapter = $this->application->config()['database']['adapter'];
            $options = [
                'database' => $this->application->config()['database']['database'],
                'username' => $this->application->config()['database']['username'],
                'password' => $this->application->config()['database']['password'],
                'host'     => $this->application->config()['database']['host'],
                'type'     => $this->application->config()['database']['type']
            ];

            $check = \Pop\Db\Db::check($adapter, $options);

            if (null !== $check) {
                throw new Exception('DB ' . $check);
            }

            $this->application->services()->set('database', [
                'call'   => 'Pop\Db\Db::connect',
                'params' => [
                    'adapter' => $adapter,
                    'options' => $options
                ]
            ]);
        }

        if ($this->application->services()->isAvailable('database')) {
            Record::setDb($this->application->getService('database'));
        }

        // Set up triggers to check the application session
        $this->application->on('app.route.pre', function(){
            if (isset($_SERVER['argv'][1])) {
                echo PHP_EOL . '    Phire Console' . PHP_EOL;
                echo '    =============' . PHP_EOL . PHP_EOL;
            }
        }, 1000);
        $this->application->on('app.dispatch.post', function(){
            echo PHP_EOL;
        }, 1000);
    }

    /**
     * Register modules
     *
     * @return void
     */
    public function registerModules()
    {
        $modulesPath   = __DIR__ . '/../..' . CONTENT_PATH . '/modules';
        $moduleFolders = [];
        $modules       = Table\Modules::findBy(['active' => 1], ['order' => 'order DESC']);

        foreach ($modules as $module) {
            if (file_exists($modulesPath . '/' . $module->folder . '/src/Module.php')) {
                include $modulesPath . '/' . $module->folder . '/src/Module.php';
                $moduleClass = $module->prefix . 'Module';
            } else {
                $moduleClass = 'Phire\Module\Module';
            }

            if (file_exists($modulesPath . '/' . $module->folder . '/config/module.php')) {
                $moduleConfig = include $modulesPath . '/' . $module->folder . '/config/module.php';

                // Load and register each module
                if (file_exists($modulesPath . '/config/' . $module->name . '.php')) {
                    $moduleConfig = array_merge(
                        $moduleConfig[$module->name], include $modulesPath . '/config/' . $module->name . '.php'
                    );
                } else {
                    $moduleConfig = $moduleConfig[$module->name];
                }

                $newModule = new $moduleClass($moduleConfig, $this->application, $module->name);
            } else {
                $newModule = new $moduleClass($this->application, $module->name);
            }

            $this->application->register($newModule, $module->name);
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
        if (file_exists(__DIR__ . '/../..' . CONTENT_PATH . '/assets') &&
            is_writable(__DIR__ . '/../..' . CONTENT_PATH . '/assets')) {

            $toDir = __DIR__ . '/../..' . CONTENT_PATH . '/assets/' . $to;
            if (!file_exists($toDir)) {
                mkdir($toDir);
                $dir = new Dir($from, [
                    'absolute'  => true,
                    'recursive' => true
                ]);
                $dir->copyTo($toDir, false);
            }

            $cssDirs     = ['css', 'styles', 'style'];
            $jsDirs      = ['js', 'scripts', 'script', 'scr'];
            $cssType     = ($import) ? 'import' : 'link';

            foreach ($cssDirs as $cssDir) {
                if (file_exists(__DIR__ . '/../..' . CONTENT_PATH . '/assets/' . $to .'/' . $cssDir)) {
                    $dir = new Dir(__DIR__ . '/../..' . CONTENT_PATH . '/assets/' . $to .'/' . $cssDir);
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
                if (file_exists(__DIR__ . '/../..' . CONTENT_PATH . '/assets/' . $to .'/' . $jsDir)) {
                    $dir = new Dir(__DIR__ . '/../..' . CONTENT_PATH . '/assets/' . $to .'/' . $jsDir);
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
     * Initialize the ACL service
     *
     * @return Module
     */
    public function initAcl()
    {
        $roles     = Table\Roles::findAll();
        $resources = $this->application->config()['resources'];
        foreach ($roles as $role) {
            $roleName = str_replace(' ', '-', strtolower(str_replace(' ', '-', $role->name)));
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
            $r = Table\Roles::findById($id);
            if (isset($r->id) && (null !== $r->parent_id) && isset($allRoles[$r->parent_id])) {
                $child->setParent($allRoles[$r->parent_id]);
            }
        }

        // Set the acl in the nav objects
        $this->application->getService('nav.top')->setAcl($this->application->getService('acl'));
        if ($this->application->services()->isAvailable('nav.side')) {
            $this->application->getService('nav.side')->setAcl($this->application->getService('acl'));
        }

        return $this;
    }

    /**
     * Initialize navigation object
     *
     * @return void
     */
    public function initNav()
    {
        $params = $this->application->services()->getParams('nav.top');
        $roles  = Table\Roles::findAll();

        foreach ($roles as $role) {
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

        $this->application->services()->setParams('nav.top', $params);
    }

    /**
     * Custom error handler method
     *
     * @param  \Exception $exception
     * @return void
     */
    public function webError(\Exception $exception)
    {
        $view = new View(__DIR__ . '/../view/exception.phtml');
        $view->title        = 'Application Error';
        $view->phireVersion = self::VERSION;
        $view->message      = htmlentities(strip_tags($exception->getMessage()), ENT_QUOTES, 'UTF-8');

        if (file_exists(__DIR__ . '/../config/app.web.php')) {
            $config = include __DIR__ . '/../config/app.web.php';
            $view->application_title = $config['application_title'];
        } else {
            $view->application_title = '';
        }

        $response = new Response();
        $response->setBody((string)$view);
        $response->send(500);
    }

    /**
     * Error handler
     *
     * @param  \Exception $exception
     * @return void
     */
    public function cliError(\Exception $exception)
    {
        $message = strip_tags($exception->getMessage());
        if (stripos(PHP_OS, 'win') === false) {
            $string  = "    \x1b[1;37m\x1b[41m    " . str_repeat(' ', strlen($message)) . "    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    " . $message . "    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    " . str_repeat(' ', strlen($message)) . "    \x1b[0m" . PHP_EOL . PHP_EOL;
            $string .= "    Try \x1b[1;33m./app help\x1b[0m for help" . PHP_EOL . PHP_EOL;
        } else {
            $string  = $message . PHP_EOL . PHP_EOL;
            $string .= '    Try \'./app help\' for help' . PHP_EOL . PHP_EOL;
        }
        echo $string;
        exit(127);
    }

}