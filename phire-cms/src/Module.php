<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire;

use App\Table;
use Pop\Acl\Resource\Resource;
use Pop\Acl\Role\Role;
use Pop\Application;
use Pop\Db\Record;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\View\View;

/**
 * Main module class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0
 */
class Module extends \Pop\Module\Module
{

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

        if ($this->application->services()->isAvailable('database')) {
            Record::setDb($this->application->getService('database'));
        }

        if (isset($this->config['forms'])) {
            $this->application->mergeConfig(['forms' => $this->config['forms']]);
        }

        if (isset($this->config['resources'])) {
            $this->application->mergeConfig(['resources' => $this->config['resources']]);
        }

        $this->application->on('app.route.pre', 'App\Event\Ssl::check', 1000)
             ->on('app.dispatch.pre', 'App\Event\Session::check', 1001)
             ->on('app.dispatch.pre', 'App\Event\Acl::check', 1000);

        $this->initNav();

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
        if ($this->application->services()->isAvailable('nav.fluid')) {
            $this->application->getService('nav.fluid')->setAcl($this->application->getService('acl'));
        }
        if ($this->application->services()->isAvailable('nav.static')) {
            $this->application->getService('nav.static')->setAcl($this->application->getService('acl'));
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

        $this->application->services()->setParams('nav.top', $params);
    }

    /**
     * Custom error handler method
     *
     * @param  \Exception $exception
     * @return void
     */
    public function error(\Exception $exception)
    {
        $view = new View(__DIR__ . '/../view/exception.phtml');
        $view->title   = 'Application Error';
        $view->message = htmlentities(strip_tags($exception->getMessage()), ENT_QUOTES, 'UTF-8');

        if (file_exists(__DIR__ . '/../config/application.php')) {
            $config = include __DIR__ . '/../config/application.php';
            $view->application_title = $config['application_title'];
        } else {
            $view->application_title = '';
        }

        $response = new Response();
        $response->setBody((string)$view);
        $response->send(500);
    }

}