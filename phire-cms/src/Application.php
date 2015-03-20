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
        $this->on('app.route.pre', 'Phire\Event\Ssl::check', 1000)
             ->on('app.route.post', 'Phire\Event\Db::check', 1000)
             ->on('app.dispatch.pre', 'Phire\Event\Session::check', 1001)
             ->on('app.dispatch.pre', 'Phire\Event\Acl::check', 1000);

        // Add roles to user nav
        $this->addRoles();

        // Register modules
        $this->registerModules();

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
     * Add user roles to navigation
     *
     * @return void
     */
    public function addRoles()
    {
        if ($this->config()['db']) {
            $params = $this->services()->getParams('nav.phire');
            $roles = \Phire\Table\Roles::findAll();

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

            $this->services()->setParams('nav.phire', $params);
        }
    }

    /**
     * Load application modules
     *
     * @return Application
     */
    public function registerModules()
    {
        if ($this->config['db']) {
            $modulePath = $_SERVER['DOCUMENT_ROOT'] . MODULE_PATH;

            $modules = \Phire\Table\Modules::findBy(['active' => 1]);
            foreach ($modules->rows() as $module) {
                if (file_exists($modulePath . '/' . $module->folder . '/src/Module.php')) {
                    include $modulePath . '/' . $module->folder . '/src/Module.php';
                    $moduleClass = $module->folder . '\Module';
                    $this->register($module->folder, new $moduleClass($this));
                } else if (file_exists($modulePath . '/' . $module->folder . '/config/module.php')) {
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
                    }
                }

                // Check module configs for Phire-specific configs
                foreach ($this->modules as $module => $config) {
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
                            } else {
                                $params['tree'][$key] = array_merge_recursive($params['tree'][$key], $value);
                            }
                        }
                        if (count($newNav) > 0) {
                            $params['tree'] = array_merge($newNav, $params['tree'], $config['nav.phire']);
                        }
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
                    if (file_exists($modulePath . '/' . $module . '/data/assets')) {
                        $this->loadAssets(
                            $modulePath . '/' . $module . '/data/assets',
                            strtolower($module)
                        );
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

            $toDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to;
            if (!file_exists($toDir)) {
                mkdir($toDir);
                $dir = new Dir($from, true, true);
                $dir->copyDir($toDir, false);
            }

            $cssDirs     = ['css', 'styles', 'style'];
            $jsDirs      = ['js', 'scripts', 'script', 'scr'];
            $navVertical = (isset($this->config['navigation_vertical']) && ($this->config['navigation_vertical']));
            $cssType     = ($import) ? 'import' : 'link';

            foreach ($cssDirs as $cssDir) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to .'/' . $cssDir)) {
                    $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to .'/' . $cssDir);
                    foreach ($dir->getFiles() as $cssFile) {
                        if ($cssFile != 'index.html') {
                            $css = BASE_PATH . CONTENT_PATH . '/assets/' . $to . '/' . $cssDir . '/' . $cssFile;
                            if (!in_array($css, $this->assets['css'][$cssType]) && (substr($css, -4) == '.css') &&
                                (stripos($css, 'public') === false)) {
                                if ((($cssFile != 'phire.nav.horz.css') && ($cssFile != 'phire.nav.vert.css')) ||
                                    (($cssFile == 'phire.nav.horz.css') && (!$navVertical)) ||
                                    (($cssFile == 'phire.nav.vert.css') && ($navVertical))
                                ) {
                                    $this->assets['css'][$cssType][] = $css;
                                }
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
     * @return Application
     */
    public function initAcl()
    {
        $roles = Table\Roles::findAll()->rows();
        foreach ($roles as $role) {
            $roleName = str_replace(' ', '-', strtolower($role->name));
            $this->config['resources']['role-' . $role->id . '|role-' . $roleName] = [
                'edit', 'remove'
            ];
            $this->config['resources']['users-of-role-' . $role->id . '|users-of-role-' . $roleName] = [
                'index', 'add', 'edit', 'remove'
            ];
        }

        foreach ($this->config['resources'] as $resource => $permissions) {
            if (strpos($resource, '|') !== false) {
                $resource = substr($resource, 0, strpos($resource, '|'));
            }
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
            $r = \Phire\Table\Roles::findById($id);
            if (isset($r->id) && (null !== $r->parent_id) && isset($allRoles[$r->parent_id])) {
                $child->setParent($allRoles[$r->parent_id]);
            }
        }

        // Set the acl in the main nav object
        $this->services['nav.phire']->setAcl($this->services['acl']);

        return $this;
    }

}
