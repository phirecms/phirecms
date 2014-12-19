<?php

namespace Phire;

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

    public function init()
    {
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
                    'services' => $this->services,
                    'request'  => new Request(),
                    'response' => new Response()
                ]
            );
        }

        // Set up triggers to check the application session
        $this->on('app.route.pre', 'Phire\Application::sslCheck', 1000)
             ->on('app.route.post', 'Phire\Application::dbCheck', 1000)
             ->on('app.dispatch.pre', 'Phire\Application::sessionCheck', 1001)
             ->on('app.dispatch.pre', 'Phire\Application::aclCheck', 1000);

        // Load assets, if they haven't been loaded already
        $this->loadAssets(__DIR__ . '/../data/assets', 'phire');

        // Init the app
        return parent::init();
    }

    public function loadAssets($from, $to)
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
                        if (!file_exists($dir . '/' . $aDir . '/' . $file) ||
                            (file_exists($dir . '/' . $aDir . '/' . $file) &&
                                (filemtime($from . '/' . $aDir . '/' . $file) > filemtime($dir . '/' . $aDir . '/' . $file)))) {
                            copy($from . '/' . $aDir . '/' . $file, $dir . '/' . $aDir . '/' . $file);
                            chmod($dir . '/' . $aDir . '/' . $file, 0777);
                        }
                    }
                }
            }
        }
    }

    public function initAcl()
    {
        $config = \Phire\Model\UserRole::getPermissionsConfig();

        if (count($config['resources']) > 0) {
            foreach ($config['resources'] as $resource) {
                $this->services['acl']->addResource($resource);
            }
        }
        if (count($config['roles']) > 0) {
            foreach ($config['roles'] as $role) {
                $this->services['acl']->addRole($role['role']);

                if (count($role['allow']) > 0) {
                    foreach ($role['allow'] as $resource) {
                        $this->services['acl']->allow($role['role']->getName(), $resource);
                    }
                }
                if (count($role['deny']) > 0) {
                    foreach ($role['deny'] as $resource) {
                        $this->services['acl']->deny($role['role']->getName(), $resource);
                    }
                }
                if (count($role['allow']) == 0) {
                    $this->services['acl']->allow($role['role']->getName());
                }
            }
        }

        // Set the acl in the main nav object
        $this->services['nav.phire']->setAcl($this->services['acl']);
    }

    public static function sslCheck(Application $application)
    {
        $config = $application->config();

        // If force_ssl is checked, and request is not secure, redirect to secure request
        if (isset($config['force_ssl']) && ($config['force_ssl']) && ($_SERVER['SERVER_PORT'] != '443')) {
            $secureUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .
                ((!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '');
            Response::redirect($secureUrl);
            exit();
        }
    }

    public static function dbCheck(Application $application)
    {
        $config = $application->config();
        $route  = $application->router()->getRouteMatch()->getRoute();
        if (!$config['db'] &&
            (substr($route, 0, strlen(BASE_PATH . APP_URI . '/install')) != BASE_PATH . APP_URI . '/install')) {
            throw new Exception('Error: The database has not been installed. Please check the config file or install the system.');
        }
    }

    public static function sessionCheck(Application $application)
    {
        $sess      = $application->getService('session');
        $action    = $application->router()->getRouteMatch()->getAction();
        $route     = $application->router()->getRouteMatch()->getRoute();
        $isInstall = (substr($route, 0, strlen(BASE_PATH . APP_URI . '/install')) == BASE_PATH . APP_URI . '/install');

        // If logged in, and a system URL, redirect to dashboard
        if (isset($sess->user) && (($action == 'login') || ($action == 'register') ||
                ($action == 'verify') || ($action == 'forgot') || ($isInstall))) {
            Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
            exit();
        // Else, if NOT logged in and NOT a system URL, redirect to login
        } else if (!isset($sess->user) && (($action != 'login') && ($action != 'register') && (!$isInstall) &&
                ($action != 'unsubscribe') && ($action != 'verify') && ($action != 'forgot') && (null !== $action))) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
            exit();
        }
    }

    public static function aclCheck(Application $application)
    {
        $config = $application->config();
        if ($config['db']) {
            $application->initAcl();
            $sess = $application->getService('session');
            $acl  = $application->getService('acl');

            if (isset($sess->user) && isset($sess->user->role_name) && ($acl->hasRole($sess->user->role_name))) {
                // Get routes with slash options
                $route  = $application->router()->getRouteMatch()->getRoute();
                $routes = [$route];
                if (substr($route, -1) == '/') {
                    $bareRoute = substr($route, 0, -1);
                    $routes[]  = $bareRoute;
                    $routes[]  = $bareRoute . '[/]';
                } else {
                    $bareRoute = $route;
                    $routes[]  = $route . '[/]';
                    $routes[]  = $route . '/';
                }

                // Get the resource
                $resource = null;
                foreach ($routes as $route) {
                    if ($acl->hasResource($route)) {
                        $resource = $route;
                    }
                }

                // Check for resources with params
                if (null === $resource) {
                    $resources = $acl->getResources();

                    foreach ($resources as $key => $value) {
                        if ((strpos($key, '/[:') !== false) && (substr($key, 0, strpos($key, '/[:')) == $bareRoute)) {
                            $resource = $key;
                        } else if ((strpos($key, '/[:') === false) && (strpos($key, '/:') !== false) &&
                            (substr($key, 0, strpos($key, '/:')) == $bareRoute)) {
                            $resource = $key;
                        }
                    }
                }

                // If role and resource exists, check if denied
                // If denied, then redirect
                if (null !== $resource) {
                    if ($acl->isDenied($sess->user->role_name, $resource)) {
                        Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
                        exit();
                    }
                }
            }
        }
    }

}