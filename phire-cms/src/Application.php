<?php

namespace Phire;

use Pop\Acl\Resource\Resource;
use Pop\Acl\Role\Role;
use Pop\Db\Record;
use Pop\File\Dir;
use Pop\Http\Request;
use Pop\Http\Response;

class Application extends \Pop\Application
{

    /**
     * Phire version
     */
    const VERSION = '2.0.0b';

    /**
     * Application JS and CSS assets
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
     * @throws Exception
     * @return Application
     */
    public function init()
    {
        // Load assets, if they haven't been loaded already
        $this->loadAssets(__DIR__ . '/../data/assets', 'phire');
        sort($this->assets['js']);
        sort($this->assets['css']['link']);
        sort($this->assets['css']['import']);

        // Load any custom/override assets
        $this->loadAssets(__DIR__ . '/../..' . MODULE_PATH . '/phire/assets', 'phire-custom', true);

        // Set the database
        if ($this->services->isAvailable('database')) {
            Record::setDb($this->getService('database'));
            $this->config['db'] = (count($this->getService('database')->getTables()) > 0);
        } else {
            $this->config['db'] = false;
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            throw new Exception('Error: Phire CMS requires PHP 5.4.0 or greater.');
        }

        // Add route params for the controllers
        if (null !== $this->router) {
            $this->router->addRouteParams(
                '*', [
                    'application' => $this,
                    'request'     => new Request(),
                    'response'    => new Response()
                ]
            );
        }

        // Set up triggers to check the application session
        $this->on('app.route.pre', 'Phire\Application::sslCheck', 1000)
             ->on('app.route.post', 'Phire\Application::dbCheck', 1000)
             ->on('app.dispatch.pre', 'Phire\Application::sessionCheck', 1001)
             ->on('app.dispatch.pre', 'Phire\Application::aclCheck', 1000);

        // Load modules
        $this->loadModules();

        // Init the app
        return parent::init();
    }

    /**
     * Application error handler
     *
     * @param  \Exception $exception
     * @return void
     */
    public function error(\Exception $exception)
    {
        $view = new \Pop\View\View(__DIR__ . '/../view/exception.phtml');
        $view->title        = 'Application Error';
        $view->assets       = $this->assets;
        $view->phireUri     = BASE_PATH . APP_URI;
        $view->basePath     = BASE_PATH;
        $view->base_path    = BASE_PATH;
        $view->contentPath  = BASE_PATH . CONTENT_PATH;
        $view->content_path = BASE_PATH . CONTENT_PATH;
        $view->message      = $exception->getMessage();

        $response = new Response();
        $response->setBody((string)$view);
        $response->send();
    }

    /**
     * Load application modules
     *
     * @return Application
     */
    public function loadModules()
    {
        if ($this->config['db']) {
            $modulePath = $_SERVER['DOCUMENT_ROOT'] . MODULE_PATH;

            $modules = \Phire\Table\Modules::findBy(['active' => 1]);
            foreach ($modules->rows() as $module) {
                if (file_exists($modulePath . '/' . $module->folder . '/config/module.php')) {
                    $moduleConfig = include $modulePath . '/' . $module->folder . '/config/module.php';

                    // Load and register each module
                    foreach ($moduleConfig as $name => $config) {
                        // Check for module config override
                        if (file_exists($modulePath . '/phire/config/' . strtolower($name) . '.php')) {
                            $config = array_merge(
                                $config, include $modulePath . '/phire/config/' . strtolower($name) . '.php'
                            );
                        }
                        $this->register($name, $config);

                        // If the module has navigation
                        $params = $this->services->getParams('nav.phire');

                        // If the module has module-level navigation
                        if (isset($config['nav.module'])) {
                            if (!isset($params['tree']['modules']['children'])) {
                                $params['tree']['modules']['children'] = [];
                            }
                            $params['tree']['modules']['children'][] = $config['nav.module'];
                        }

                        // If the module has system-level navigation
                        if (isset($config['nav.phire'])) {
                            $newNav = [];
                            foreach ($config['nav.phire'] as $key => $value) {
                                if (($key !== 'modules') && ($key !== 'users') && ($key !== 'config')) {
                                    $newNav[$key] = $value;
                                    unset($config['nav.phire'][$key]);
                                }
                            }
                            $params['tree'] = array_merge_recursive($newNav, $params['tree'], $config['nav.phire']);
                        }

                        // If the module has ACL resources
                        if (isset($config['resources'])) {
                            $this->config['resources'] = array_merge($this->config['resources'], $config['resources']);
                        }

                        // If the module has form configs
                        if (isset($config['forms'])) {
                            $this->config['forms'] = array_merge($this->config['forms'], $config['forms']);
                        }

                        // Add the nav params back to the service
                        $this->services->setParams('nav.phire', $params);

                        // Load module assets
                        if (file_exists($modulePath . '/' . $module->folder . '/data/assets')) {
                            $this->loadAssets(
                                $modulePath . '/' . $module->folder . '/data/assets',
                                strtolower($name)
                            );
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
     * Load application assets to a public folder
     *
     * @param  string  $from
     * @param  string  $to
     * @param  boolean $import
     * @return Application
     */
    public function loadAssets($from, $to, $import = false)
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets') &&
            is_writable($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets')) {
            $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to;
            if (!file_exists($dir)) {
                mkdir($dir);
                chmod($dir, 0777);

                copy($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/index.html', $dir . '/index.html');
                chmod($dir . '/index.html', 0777);
            }

            $assetDirs = array(
                'css', 'css/fonts', 'styles', 'styles/fonts', 'style', 'style/fonts', // CSS folders
                'js', 'scripts', 'script', 'scr',                                     // JS folders
                'image', 'images', 'img', 'imgs'                                      // Image folders
            );

            $cssType     = ($import) ? 'import' : 'link';
            $navVertical = (isset($this->config['navigation_vertical']) && ($this->config['navigation_vertical']));

            foreach ($assetDirs as $aDir) {
                if (file_exists($from . '/' . $aDir)) {
                    if (!file_exists($dir . '/' . $aDir)) {
                        mkdir($dir . '/' . $aDir);
                        chmod($dir . '/' . $aDir, 0777);
                        copy($dir . '/index.html', $dir . '/' . $aDir . '/index.html');
                        chmod($dir . '/' . $aDir . '/index.html', 0777);
                    }
                    $d = new Dir($from . '/' . $aDir, false, false, false);
                    foreach ($d->getFiles() as $file) {
                        if ($file !== 'index.html') {
                            if (!file_exists($dir . '/' . $aDir . '/' . $file) ||
                                (file_exists($dir . '/' . $aDir . '/' . $file) &&
                                    (filemtime($from . '/' . $aDir . '/' . $file) >
                                        filemtime($dir . '/' . $aDir . '/' . $file)))) {
                                copy($from . '/' . $aDir . '/' . $file, $dir . '/' . $aDir . '/' . $file);
                                chmod($dir . '/' . $aDir . '/' . $file, 0777);
                            }
                            if (($aDir == 'css') || ($aDir == 'styles') || ($aDir == 'style')) {
                                if (file_exists($dir . '/' . $aDir . '/' . $file)) {
                                    $css = BASE_PATH . CONTENT_PATH . '/assets/' . $to . '/' . $aDir . '/' . $file;
                                    if (!in_array($css, $this->assets['css'][$cssType])) {
                                        if ((($file != 'phire.nav.horz.css') && ($file != 'phire.nav.vert.css')) ||
                                            (($file == 'phire.nav.horz.css') && (!$navVertical)) ||
                                            (($file == 'phire.nav.vert.css') && ($navVertical))) {
                                            $this->assets['css'][$cssType][] = $css;
                                        }
                                    }
                                }
                            }
                            if (($aDir == 'js') || ($aDir == 'scripts') || ($aDir == 'script') || ($aDir == 'scr')) {
                                $js = BASE_PATH . CONTENT_PATH . '/assets/' . $to . '/' . $aDir . '/' . $file;
                                if (!in_array($js, $this->assets['js'])) {
                                    $this->assets['js'][] = $js;
                                }
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
     * @return Application
     */
    public function initAcl()
    {
        $roles = Table\UserRoles::findAll()->rows();
        foreach ($roles as $role) {
            $this->config['resources']['role-' . $role->id] = [
                'edit', 'remove'
            ];
        }
        foreach ($roles as $role) {
            $this->config['resources']['user-role-' . $role->id] = [
                'index', 'add', 'edit', 'remove'
            ];
        }

        foreach ($this->config['resources'] as $resource => $permissions) {
            $this->services['acl']->addResource(new Resource($resource));
        }

        $allRoles  = [];

        foreach ($roles as $role) {
            $r = new Role($role->name);
            $allRoles[$role->id] = $r;
            $this->services['acl']->addRole($r);

            if (null !== $role->permissions) {
                $role->permissions = unserialize($role->permissions);
            }
            if ((null === $role->permissions) || (is_array($role->permissions) && (count($role->permissions) == 0))) {
                $this->services['acl']->allow($role->name);
            } else {
                if (count($role->permissions['allow']) > 0) {
                    foreach ($role->permissions['allow'] as $allow) {
                        $this->services['acl']->allow($role->name, $allow['resource'], $allow['permission']);
                    }
                } else {
                    $this->services['acl']->allow($role->name);
                }
                if (count($role->permissions['deny']) > 0) {
                    foreach ($role->permissions['deny'] as $deny) {
                        $this->services['acl']->deny($role->name, $deny['resource'], $deny['permission']);
                    }
                }
            }
        }

        // Set up parent/child roles
        foreach ($allRoles as $id => $child) {
            $r = \Phire\Table\UserRoles::findById($id);
            if (isset($r->id) && (null !== $r->parent_id) && isset($allRoles[$r->parent_id])) {
                $child->setParent($allRoles[$r->parent_id]);
            }
        }

        // Set the acl in the main nav object
        $this->services['nav.phire']->setAcl($this->services['acl']);

        return $this;
    }

    /**
     * Check if the application requires an SSL connection
     *
     * @param  Application $application
     * @return void
     */
    public static function sslCheck(Application $application)
    {
        if ($application->config()['db']) {
            $forceSsl = \Phire\Table\Config::findById('force_ssl')->value;
            // If force_ssl is checked, and request is not secure, redirect to secure request
            if (($forceSsl) && ($_SERVER['SERVER_PORT'] != '443')) {
                $secureUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .
                    ((!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '');
                Response::redirect($secureUrl);
                exit();
            }
        }
    }

    /**
     * Check if the database has been installed and a database connection is available
     *
     * @param  Application $application
     * @throws Exception
     * @return void
     */
    public static function dbCheck(Application $application)
    {
        $route = $application->router()->getRouteMatch()->getRoute();
        if (!$application->config()['db'] &&
            (substr($route, 0, strlen(BASE_PATH . APP_URI . '/install')) != BASE_PATH . APP_URI . '/install')) {
            throw new Exception(
                'Error: The database has not been installed. Please check the config file or install the system.'
            );
        }
    }

    /**
     * Check if the user session
     *
     * @param  Application $application
     * @return void
     */
    public static function sessionCheck(Application $application)
    {
        $sess      = $application->getService('session');
        $action    = $application->router()->getRouteMatch()->getAction();
        $route     = $application->router()->getRouteMatch()->getRoute();
        $isInstall = (substr($route, 0, strlen(BASE_PATH . APP_URI . '/install')) == BASE_PATH . APP_URI . '/install');

        // Special install check
        if (isset($sess->app_uri) && (strpos($_SERVER['REQUEST_URI'], 'install/config') !== false)) {
            if ((BASE_PATH . APP_URI) == (BASE_PATH . $sess->app_uri) && ($application->config()['db'])) {
                Response::redirect(BASE_PATH . APP_URI . '/install/user');
                exit();
            }
        }

        // If logged in, and a system URL, redirect to dashboard
        if (isset($sess->user) && (($action == 'login') || ($action == 'register') ||
                ($action == 'verify') || ($action == 'forgot') || ($isInstall))) {
            Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
            exit();
        // Else, if NOT logged in and NOT a system URL, redirect to login
        } else if (!isset($sess->user) && (($action != 'login') && ($action != 'register') && (!$isInstall) &&
                ($action != 'unsubscribe') && ($action != 'verify') && ($action != 'forgot') && (null !== $action)) &&
                (substr($route, 0, strlen(BASE_PATH . APP_URI)) == BASE_PATH . APP_URI)) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
            exit();
        }
    }

    /**
     * Check if the user session with the ACL service
     *
     * @param  Application $application
     * @return void
     */
    public static function aclCheck(Application $application)
    {
        if ($application->config()['db']) {
            $application->initAcl();
            $sess = $application->getService('session');
            $acl  = $application->getService('acl');

            if (isset($sess->user) && isset($sess->user->role) && ($acl->hasRole($sess->user->role))) {
                // Get routes with slash options
                $route  = $application->router()->getRouteMatch()->getRoute();
                $routes = $application->router()->getRouteMatch()->getRoutes();
                if (isset($routes[$route]) && isset($routes[$route]['acl']) &&
                    isset($routes[$route]['acl']['resource'])) {
                    $resource   = $routes[$route]['acl']['resource'];
                    $permission = (isset($routes[$route]['acl']['permission'])) ?
                        $routes[$route]['acl']['permission'] : null;
                    if (!$acl->isAllowed($sess->user->role, $resource, $permission)) {
                        Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
                        exit();
                    }
                }
            }
        }
    }

}