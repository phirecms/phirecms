<?php
/**
 * @namespace
 */
namespace Phire;

use Phire\Table\Sites;
use Pop\File\Dir;
use Pop\Project\Project as P;
use Pop\Web\Cookie;
use Pop\Web\Session;

class Project extends P
{

    /**
     * Phire version
     */
    const VERSION = '2.0.0b';

    /**
     * Project assets
     */
    protected $assets = null;

    /**
     * Register and load any other modules
     *
     * @param  \Pop\Loader\Autoloader $autoloader
     * @param  boolean                $site
     * @throws Exception
     * @return self
     */
    public function load($autoloader, $site = false)
    {
        if ($site) {
            $s = Table\Sites::getSite();
            $docRoot  = $s->document_root;
            $basePath = $s->base_path;
        } else {
            $docRoot  = $_SERVER['DOCUMENT_ROOT'];
            $basePath = BASE_PATH;
        }

        $events = array();

        // Load Phire any overriding Phire configuration
        if (!$site) {
            $this->loadAssets(__DIR__ . '/../../../Phire/data', 'Phire', $docRoot);
        }

        // Check if Phire is installed
        self::isInstalled();

        $sess = Session::getInstance();

        $errors = self::checkDirsQuick($docRoot . $basePath . CONTENT_PATH, true, $docRoot);
        if (count($errors) > 0) {
            $sess->errors = '            ' . implode('<br />' . PHP_EOL . '            ', $errors) . PHP_EOL;
        } else {
            unset($sess->errors);
        }

        $modulesAry  = array();
        $modulesDirs = array(
            __DIR__ . '/../../../',
            __DIR__ . '/../../../../module/',
            __DIR__ . '/../../../../..' . CONTENT_PATH . '/extensions/modules/'
        );

        // Check for overriding Phire config
        if (file_exists($docRoot . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php')) {
            $phireCfg = include $docRoot . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php';
            if (isset($phireCfg['Phire'])) {
                // If the overriding config is set to allow changes, merge new nav with the original nav
                // else, the entire original nav will be overwritten with the new nav.
                if (isset($phireCfg['Phire']->nav) && $phireCfg['Phire']->changesAllowed()) {
                    $nav = array_merge($phireCfg['Phire']->nav->asArray(), $this->module('Phire')->nav->asArray());
                    $phireCfg['Phire']->nav = new \Pop\Config($nav);
                }
                $this->module('Phire')->merge($phireCfg['Phire']);

                // Get any Phire event
                if (null !== $this->module('Phire')->events) {
                    $events['Phire'] = $this->module('Phire')->events->asArray();
                }
            }
        };

        // Register and load any other modules
        foreach ($modulesDirs as $directory) {
            if (file_exists($directory) && is_dir($directory)) {
                $dir = new Dir($directory);
                $dirs = $dir->getFiles();
                sort($dirs);
                foreach ($dirs as $d) {
                    $moduleCfg    = null;
                    if (($d != 'PopPHPFramework') && ($d != 'Phire') && ($d != 'config') && ($d != 'vendor') && (is_dir($directory . $d))) {
                        $ext = Table\Extensions::findBy(array('name' => $d));
                        if (!isset($ext->id) || (isset($ext->id) && ($ext->active))) {
                            $modulesAry[] = $d;

                            // Load assets
                            if (!$site) {
                                $this->loadAssets($directory . $d . '/data', $d, $docRoot);
                            }

                            // Get module config
                            if (file_exists($directory . $d . '/config/module.php')) {
                                $moduleCfg = include $directory . $d . '/config/module.php';
                            }

                            // Check for any module config overrides
                            if (file_exists($directory . '/config/' . strtolower($d) . '.php')) {
                                $override = include $directory . '/config/' . strtolower($d) . '.php';
                                if (isset($override[$d]) && (null !== $moduleCfg)) {
                                    $moduleCfg[$d]->merge($override[$d]);
                                }
                            }

                            // Load module configs
                            if (null !== $moduleCfg) {
                                // Register the module source
                                if (file_exists($moduleCfg[$d]->src)) {
                                    $autoloader->register($d, $moduleCfg[$d]->src);
                                }

                                // Get any module events
                                if (null !== $moduleCfg[$d]->events) {
                                    $events[$d] = $moduleCfg[$d]->events->asArray();
                                }
                                $this->loadModule($moduleCfg);
                            }
                        }
                    }
                }
            }
        }

        // Attach any event hooks
        if (count($events) > 0) {
            foreach ($events as $module => $evts) {
                foreach ($evts as $event => $action) {
                    $act = null;
                    $priority = 0;
                    if (is_array($action)) {
                        if (!isset($action['action'])) {
                            throw new Exception(
                                "The 'action' parameter is not set for the '" . $event .
                                "' event within the " . $module . " module configuration file."
                            );
                        }
                        $act = $action['action'];
                        $priority = (isset($action['priority']) ? $action['priority'] : 0);
                    } else {
                        $act = $action;
                    }
                    if (null !== $act) {
                        $this->attachEvent($event, $act, $priority);
                    }
                }
            }
        }

        // Add Phire CSS override file if it exists
        if (file_exists($docRoot . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/css/phire.css')) {
            $this->assets['css'] .= '    <style type="text/css">@import "' . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/css/phire.css";</style>' . PHP_EOL;
        }

        // If logged in, set Phire path cookie
        if ((!$site) && isset($sess->user)) {
            $path = BASE_PATH . APP_URI;
            if ($path == '') {
                $path = '/';
            }

            $cookie = Cookie::getInstance(array('path' => $path));
            if (!isset($cookie->phire)) {
                $modsAry = array();

                foreach ($modulesAry as $modName) {
                    $i18n = (file_exists($docRoot . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($modName) . '/i18n'));
                    $modsAry[] = array('name' => $modName, 'i18n' => $i18n);
                }

                $cookie->set('phire', array(
                    'base_path'        => BASE_PATH,
                    'app_path'         => APP_PATH,
                    'content_path'     => CONTENT_PATH,
                    'app_uri'          => APP_URI,
                    'server_tz_offset' => abs(date('Z')) / 60,
                    'modules'          => $modsAry
                ));
            }
        }

        // Initiate the router object
        $this->loadRouter(new \Pop\Mvc\Router(array(), new \Pop\Http\Request(null, BASE_PATH)));

        return $this;
    }

    /**
     * Get project assets
     *
     * @return string
     */
    public function getAssets()
    {
        return $this->assets['js'] . $this->assets['css'] . PHP_EOL;
    }

    /**
     * Add any project specific code to this method for run-time use here.
     *
     * @return void
     */
    public function run()
    {
        // Set the services
        $this->setService('acl', 'Phire\Auth\Acl');
        $this->setService('auth', 'Phire\Auth\Auth');
        $this->setService('phireNav', 'Pop\Nav\Nav');

        // Get loaded modules and add their routes and nav
        $modules = $this->modules();
        $nav = $this->getService('phireNav');

        // Load module routes and nav
        foreach ($modules as $name => $config) {
            $cfg = $config->asArray();
            // Add nav
            if (isset($cfg['nav'])) {
                $nav->addBranch($cfg['nav'], true);
            }

            // Add routes
            if (isset($cfg['routes'])) {
                $routes = ((APP_URI == '') && isset($cfg['routes'][APP_URI])) ? $cfg['routes'][APP_URI] : $cfg['routes'];
                $this->router->addControllers($routes);
            }
        }

        // If the path is the install path
        if (substr($_SERVER['REQUEST_URI'], 0, strlen(BASE_PATH . APP_URI . '/install')) == BASE_PATH . APP_URI . '/install') {
            parent::run();
        // Else, load any user routes and initialize the ACL object
        } else {
            $this->loadUserRoutes();
            $this->initAcl();

            // Set the auth method to trigger on 'dispatch.pre'
            $this->attachEvent('dispatch.pre', 'Phire\Project::auth');

            // Set up routing error check on 'route.error'
            $this->attachEvent('route.error', 'Phire\Project::error');

            // If SSL is required for this user type, and not SSL,
            // redirect to SSL, else, just run
            if (($this->getService('acl')->getType()->force_ssl) && !($_SERVER['SERVER_PORT'] == '443')) {
                \Pop\Http\Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            } else {
                parent::run();
            }
        }
    }

    /**
     * Event-based route error check
     *
     * @param  \Pop\Mvc\Router $router
     * @return void
     */
    public static function error($router)
    {
        $view = \Pop\Mvc\View::factory(__DIR__ . '/../../view/route.phtml', array(
            'i18n'  => \Phire\Table\Config::getI18n(),
            'title' => 'Routing Error',
            'msg'   => '    <p>There was no controller assigned for this route.</p>'
        ));

        $response = new \Pop\Http\Response(404);
        $response->setBody($view->render(true));
        $response->send();
    }

    /**
     * Event-based auth check
     *
     * @param  \Pop\Mvc\Router $router
     * @return mixed
     */
    public static function auth($router)
    {
        $sess     = Session::getInstance();
        $site     = Sites::getSite();
        $basePath = $site->base_path;

        $resource          = $router->getControllerClass();
        $permission        = $router->getAction();
        $isFrontController = (substr_count($resource, '\\') == 2);

        // Check for the resource and permission
        if (!($isFrontController) && ($resource != 'Phire\Controller\Phire\Install\IndexController')) {
            if (null === $router->project()->getService('acl')->getResource($resource)) {
                if ($resource != 'Phire\Controller\Phire\IndexController') {
                    $router->project()->getService('acl')->addResource($resource);
                } else {
                    $resource = null;
                    $permission = null;
                }
            }

            if ((null !== $permission) && (null !== $resource) && !method_exists($resource, $permission)) {
                $permission = 'error';
            }

            if (($router->controller()->getRequest()->getPath(0) == 'index') || ($router->controller()->getRequest()->getPath(0) == 'add')) {
                $permId = $router->controller()->getRequest()->getPath(1);
                if ((null !== $permId) && is_numeric($permId)) {
                    $permission .= '_' . $permId;
                }
            }

            // Get the user URI
            $uri = ((APP_URI == '') || (strtolower($router->project()->getService('acl')->getType()->type) == 'user')) ?
                APP_URI :
                '/' . strtolower($router->project()->getService('acl')->getType()->type);

            // If reset password flag is set
            if (isset($sess->reset_pwd) && ($_SERVER['REQUEST_URI'] != $basePath . $uri . '/profile') &&
                ($_SERVER['REQUEST_URI'] != $basePath . $uri . '/login') &&
                ($_SERVER['REQUEST_URI'] != $basePath . $uri . '/logout')) {
                \Pop\Http\Response::redirect($basePath . $uri . '/profile');
                return \Pop\Event\Manager::KILL;
            // If not logged in for unsubscribe and required, redirect to the system login
            } else if (($_SERVER['REQUEST_URI'] == $basePath . $uri . '/unsubscribe') &&
                ($router->project()->getService('acl')->getType()->unsubscribe_login) &&
                (!$router->project()->getService('acl')->isAuth($resource, $permission))) {
                \Pop\Http\Response::redirect($basePath . $uri . '/login');
                return \Pop\Event\Manager::KILL;
            // Else, if not logged in or allowed, redirect to the system login
            } else if (($_SERVER['REQUEST_URI'] != $basePath . $uri . '/login') &&
                ($_SERVER['REQUEST_URI'] != $basePath . $uri . '/register') &&
                ($_SERVER['REQUEST_URI'] != $basePath . $uri . '/forgot') &&
                ($_SERVER['REQUEST_URI'] != $basePath . $uri . '/unsubscribe') &&
                (substr($_SERVER['REQUEST_URI'], 0, strlen($basePath . $uri . '/json')) != ($basePath . $uri . '/json')) &&
                (strpos($_SERVER['REQUEST_URI'], $basePath . $uri . '/verify') === false) &&
                (!$router->project()->getService('acl')->isAuth($resource, $permission))) {
                \Pop\Http\Response::redirect($basePath . $uri . '/login');
                return \Pop\Event\Manager::KILL;
           // Else, if logged in and allowed, and a system access URI, redirect back to the system
            } else if ((($_SERVER['REQUEST_URI'] == $basePath . $uri . '/login') ||
                    ($_SERVER['REQUEST_URI'] == $basePath . $uri . '/register') ||
                    ($_SERVER['REQUEST_URI'] == $basePath . $uri . '/forgot')) &&
                ($router->project()->getService('acl')->isAuth($resource, $permission))) {
                \Pop\Http\Response::redirect($basePath . (($uri == '') ? '/' : $uri));
                return \Pop\Event\Manager::KILL;
            }
        }
    }

    /**
     * Static method to get model types
     *
     * @param mixed  $model
     * @param string $docRoot
     * @return array
     */
    public static function getModelTypes($model, $docRoot = null)
    {
        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        if (is_array($model)) {
            $aryVals = array_values($model);
            $model = array_shift($aryVals);
        }

        $typesClass = null;

        $modelTypes = array('0' => '(All)');
        $model = str_replace('Model', 'Table', $model);
        $classAry = explode('_', $model);
        $dirs = array();

        // Get system and extension modules
        if (file_exists($docRoot . BASE_PATH . APP_PATH . '/vendor/' . $classAry[0])) {
            if (file_exists($docRoot . BASE_PATH . APP_PATH . '/vendor/' . $classAry[0] . '/src/' . $classAry[0] . '/' . $classAry[1])) {
                $dirs[] = realpath($docRoot . BASE_PATH . APP_PATH . '/vendor/' . $classAry[0] . '/src/' . $classAry[0] . '/' . $classAry[1]);
            }
        } else if (file_exists($docRoot . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $classAry[0])) {
            if (file_exists($docRoot . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $classAry[0] . '/src/' . $classAry[0] . '/' . $classAry[1])) {
                $dirs[] = realpath($docRoot . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $classAry[0] . '/src/' . $classAry[0] . '/' . $classAry[1]);
            }
        }

        // Loop through directories looking for models and their respective types
        // i.e., Users and UserTypes
        foreach ($dirs as $dir) {
            if (file_exists($dir . '/' . $classAry[2] . 'Types.php')) {
                $typesClass = implode('\\', $classAry) . 'Types';
            } else {
                if (substr($classAry[2], -1) == 's') {
                    $class = substr($classAry[2], 0, -1) . 'Types';
                    if (file_exists($dir . '/' . $class . '.php')) {
                        $typesClass = $classAry[0] . '\\' . $classAry[0] . '\\' . $class;
                    }
                } else if (substr($classAry[2], -1) == 'y') {
                    $class = substr($classAry[2], 0, -1) . 'iesTypes';
                    if (file_exists($dir . '/' . $class . '.php')) {
                        $typesClass = $classAry[0] . '\\' . $classAry[0] . '\\' . $class;
                    }
                }
            }

            // Attempt to find all types for the related model
            if (null !== $typesClass) {
                $types = $typesClass::findAll('id ASC');
                if (isset($types->rows[0])) {
                    foreach ($types->rows as $type) {
                        if (isset($type->name)) {
                            $name = $type->name;
                        } else if (isset($type->type)) {
                            $name = $type->type;
                        } else {
                            $name = $type->id;
                        }
                        $modelTypes[$type->id] = $name;
                    }
                }
            }
        }

        return $modelTypes;
    }

    /**
     * Load other user types' routes
     *
     * @return void
     */
    protected function loadUserRoutes()
    {
        // Get any other user types and declare their URI / Controller mapping
        $types = \Phire\Table\UserTypes::findAll();

        foreach ($types->rows as $type) {
            if (strtolower($type->type) != 'user') {
                // If the user type has a defined controller
                if ($type->controller != '') {
                    // If the user type has defined sub-controllers
                    if ($type->sub_controllers != '') {
                        $controller = array('/' => $type->controller);
                        $namespace = substr($type->controller, 0, (strrpos($type->controller, '\\') + 1));
                        $subs = explode(',', $type->sub_controllers);
                        foreach ($subs as $sub) {
                            $sub = trim($sub);
                            $controller['/' . $sub] = $namespace . ucfirst($sub) . 'Controller';
                        }
                    } else {
                        $controller = $type->controller;
                    }
                // Else, just map to the base Phire controller
                } else {
                    $controller = 'Phire\Controller\Phire\IndexController';
                }

                $this->router->addControllers(array(
                    '/' . $type->type => $controller
                ));
            }
        }
    }

    /**
     * Initialize the ACL object, checking for user types and user roles
     *
     * @return void
     */
    protected function initAcl()
    {
        // Get the user type from either session or the URI
        $sess = \Pop\Web\Session::getInstance();
        $type = str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']);

        // If the URI matches the system user URI
        if (substr($type, 0, strlen(APP_URI)) == APP_URI) {
            $type = 'user';
            // Else, set user type
        } else {
            $type = substr($type, 1);
            if (strpos($type, '/') !== false) {
                $type = substr($type, 0, strpos($type, '/'));
            }
        }

        // Create the type object and pass it to the Acl object
        if (isset($sess->user->type_id)) {
            $typeObj = \Phire\Table\UserTypes::findById($sess->user->type_id);
        } else {
            $typeObj = \Phire\Table\UserTypes::findBy(array('type' => $type));
        }

        $this->getService('acl')->setType($typeObj);

        // Set the roles for this user type in the Acl object
        $perms = \Phire\Table\UserRoles::getAllRoles($typeObj->id);
        if (count($perms['roles']) > 0) {
            foreach ($perms['roles'] as $role) {
                $this->getService('acl')->addRole($role);
            }
        }

        // Set up the ACL object's resources and permissions
        if (count($perms['resources']) > 0) {
            foreach ($perms['resources'] as $role => $perm) {
                if (count($perm['allow']) > 0) {
                    foreach ($perm['allow'] as $resource => $p) {
                        $this->getService('acl')->addResource($resource);
                        if (count($p) > 0) {
                            $this->getService('acl')->allow($role, $resource, $p);
                        } else {
                            $this->getService('acl')->allow($role, $resource);
                        }
                    }
                } else {
                    $this->getService('acl')->allow($role);
                }

                if (count($perm['deny']) > 0) {
                    foreach ($perm['deny'] as $resource => $p) {
                        $this->getService('acl')->addResource($resource);
                        if (count($p) > 0) {
                            $this->getService('acl')->deny($role, $resource, $p);
                        } else {
                            $this->getService('acl')->deny($role, $resource);
                        }
                    }
                }
            }
        }
    }

    /**
     * Load install any assets for the module
     *
     * @param  string $d
     * @param  string $moduleName
     * @param  string $docRoot
     * @return void
     */
    protected function loadAssets($d, $moduleName, $docRoot = null)
    {
        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        clearstatcache();

        if (null === $this->assets) {
            $this->assets = array(
                'js'  => PHP_EOL . '    <script type="text/javascript" src="' . BASE_PATH . CONTENT_PATH . '/assets/js/jax.3.2.0.min.js"></script>' . PHP_EOL . '    <script type="text/javascript" src="' . BASE_PATH . CONTENT_PATH . '/assets/js/jax.form.min.js"></script>' . PHP_EOL,
                'css' => PHP_EOL
            );
        }

        $newModuleDir = $docRoot . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName);
        if (!file_exists($newModuleDir)) {
            mkdir($newModuleDir);
            chmod($newModuleDir, 0777);
            copy($docRoot . BASE_PATH . CONTENT_PATH . '/assets/index.html', $newModuleDir . '/index.html');
            chmod($newModuleDir . '/index.html', 0777);
        }

        $assetDirs = array(
            'css', 'css/fonts', 'styles', 'styles/fonts', 'style', 'style/fonts', // CSS folders
            'js', 'scripts', 'script', 'scr',                                     // JS folders
            'image', 'images', 'img', 'imgs',                                     // Image folders
            'i18n'                                                                // I18n folder
        );

        // Check and install asset files
        foreach ($assetDirs as $assetDir) {
            if (file_exists($d . '/assets/' . $assetDir)) {
                $newDir = $docRoot . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName) . '/' . $assetDir;
                if (!file_exists($newDir)) {
                    mkdir($newDir);
                    chmod($newDir, 0777);
                    copy($docRoot . BASE_PATH . CONTENT_PATH . '/assets/index.html', $newDir . '/index.html');
                    chmod($newDir . '/index.html', 0777);
                }
                $asDir = new Dir($d . '/assets/' . $assetDir, true, false, false);
                $asFiles = $asDir->getObjects();
                foreach ($asFiles as $as) {
                    if ($as->getExt() != 'html') {
                        // If asset file doesn't exist, or has been modified, copy it over
                        if (!file_exists($newDir . '/' . $as->getBasename()) ||
                            (filemtime($newDir . '/' . $as->getBasename()) < filemtime($as->getFullPath()))) {
                            $as->copy($newDir . '/' . $as->getBasename(), true);
                            $as->setPermissions(0777);
                        }
                        if ($as->getExt() == 'js') {
                            $folder = null;
                            if (strpos($as->getFullPath(), DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR) !== false) {
                                $folder = '/js/';
                            } else if (strpos($as->getFullPath(), DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR) !== false) {
                                $folder = '/scripts/';
                            } else if (strpos($as->getFullPath(), DIRECTORY_SEPARATOR . 'script/' . DIRECTORY_SEPARATOR) !== false) {
                                $folder = '/script/';
                            } else if (strpos($as->getFullPath(), DIRECTORY_SEPARATOR . 'scr/' . DIRECTORY_SEPARATOR) !== false) {
                                $folder = '/scr/';
                            }
                            if (null !== $folder) {
                                $this->assets['js'] .= '    <script type="text/javascript" src="' . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName) . $folder . $as->getBasename() . '"></script>' . PHP_EOL;
                            }
                        } else if ($as->getExt() == 'css') {
                            $folder = null;
                            if (strpos($as->getFullPath(), DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR) !== false) {
                                $folder = '/css/';
                            } else if (strpos($as->getFullPath(), DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR) !== false) {
                                $folder = '/styles/';
                            } else if (strpos($as->getFullPath(), DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR) !== false) {
                                $folder = '/style/';
                            }
                            if (null !== $folder) {
                                $this->assets['css'] .= '    <link type="text/css" rel="stylesheet" href="' . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName) . $folder . $as->getBasename() . '" />' . PHP_EOL;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Method to check if the system is installed
     *
     * @param  boolean $suppress
     * @throws \Exception
     * @return boolean
     */
    public static function isInstalled($suppress = false)
    {
        if (((isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], BASE_PATH . APP_URI . '/install') === false)) &&
            ((DB_INTERFACE == '') || (DB_NAME == '')))) {
            if (!$suppress) {
                $error = '<strong>Error:</strong> Phire CMS 2.0 is not properly configured. Please check the config file or <a href="' . BASE_PATH . APP_URI . '/install">install</a> the application.';
                ob_start();
                include __DIR__ . '/../../view/phire/install/not.phtml';
                $output = ob_get_clean();
                throw new \Exception($output);
            } else {
                return false;
            }
        } else if (!isset($_SERVER['REQUEST_URI']) && ((DB_INTERFACE == '') || (DB_NAME == ''))) {
            return false;
        }

        return true;
    }

    /**
     * Quickly determine whether or not the necessary system directories are writable or not.
     *
     * @param  string  $contentDir
     * @param  boolean $msgs
     * @param  string  $docRoot
     * @return boolean|array
     */
    public static function checkDirsQuick($contentDir, $msgs = false, $docRoot = null)
    {
        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        $errorMsgs = array();

        $dirs = array(
            $contentDir . DIRECTORY_SEPARATOR . 'assets',
            $contentDir . DIRECTORY_SEPARATOR . 'extensions',
            $contentDir . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes',
            $contentDir . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules',
            $contentDir . DIRECTORY_SEPARATOR . 'log',
            $contentDir . DIRECTORY_SEPARATOR . 'media',
        );

        foreach ($dirs as $value) {
            if (!is_writable($value)) {
                $errorMsgs[] = "The directory " . str_replace($docRoot, '', $value) . " is not writable.";
            }
        }

        if ($msgs) {
            return $errorMsgs;
        } else {
            return (count($errorMsgs) == 0) ? true : false;
        }
    }

    /**
     * Determine whether or not the necessary system directories are writable or not.
     *
     * @param  string  $contentDir
     * @param  boolean $msgs
     * @param  string  $docRoot
     * @return boolean|array
     */
    public static function checkDirs($contentDir, $msgs = false, $docRoot = null)
    {
        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        $dir = new Dir($contentDir, true, true);
        $files = $dir->getFiles();
        $errorMsgs = array();

        // Check if the necessary directories are writable for Windows.
        if (stripos(PHP_OS, 'win') !== false) {
            if ((@touch($contentDir . '/writetest.txt')) == false) {
                $errorMsgs[] = "The directory " . str_replace($docRoot, '', $contentDir) . " is not writable.";
            } else {
                unlink($contentDir . '/writetest.txt');
                clearstatcache();
            }
            foreach ($files as $value) {
                if ((strpos($value, 'data') === false) && (strpos($value, 'ckeditor') === false) && (strpos($value, 'tinymce') === false) && (is_dir($value))) {
                    if ((@touch($value . '/writetest.txt')) == false) {
                        $errorMsgs[] = "The directory " . str_replace($docRoot, '', $value) . " is not writable.";
                    } else {
                        unlink($value . '/writetest.txt');
                        clearstatcache();
                    }
                }
            }
        // Check if the necessary directories are writable for Unix/Linux.
        } else {
            clearstatcache();
            if (!is_writable($contentDir)) {
                $errorMsgs[] = "The directory " . str_replace($docRoot, '', $contentDir) . " is not writable.";
            }
            foreach ($files as $value) {
                if ((strpos($value, 'data') === false) && (strpos($value, 'ckeditor') === false) && (strpos($value, 'tinymce') === false) && (is_dir($value))) {
                    clearstatcache();
                    if (!is_writable($value)) {
                        $errorMsgs[] = "The directory " . str_replace($docRoot, '', $value) . " is not writable.";
                    }
                }
            }
        }

        // If the messaging flag was passed, return any
        // error messages, else return true/false.
        if ($msgs) {
            return $errorMsgs;
        } else {
            return (count($errorMsgs) == 0) ? true : false;
        }
    }

}

