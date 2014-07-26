<?php
/**
 * @namespace
 */
namespace Phire\Auth;

use Pop\Auth\Acl as A;
use Pop\Web\Cookie;
use Pop\Web\Session;
use Phire\Table;

class Acl extends A
{

    /**
     * Session property
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Type property
     * @var \Phire\Table\UserTypes
     */
    protected $type = null;

    /**
     * Base path property
     * @var string
     */
    protected $basePath = null;

    /**
     * Set type method
     *
     * @param  \Phire\Table\UserTypes $type
     * @return \Phire\Auth\Acl
     */
    public function setType(\Phire\Table\UserTypes $type)
    {
        $this->sess = Session::getInstance();
        $this->type = $type;
        $this->basePath = (strtolower($this->type->type) != 'user') ? BASE_PATH . '/' . strtolower($this->type->type) : BASE_PATH . APP_URI;
    }

    /**
     * Get type method
     *
     * @return \Phire\Table\UserTypes
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Is auth method
     *
     * @param  string $resource
     * @param  string $permission
     * @return boolean
     */
    public function isAuth($resource = null, $permission = null)
    {
        $auth = false;

        // If tracking sessions is on
        if (($this->type->track_sessions) && ((isset($this->sess->user->sess_id) && null !== $this->sess->user->sess_id))) {
            $session = Table\UserSessions::findById($this->sess->user->sess_id);
            if (!isset($session->id) || (($this->type->session_expiration != 0) && $session->hasExpired($this->type->session_expiration, $this->sess->user->last_action))) {
                $this->sess->lastUrl = (strpos($_SERVER['REQUEST_URI'], '/users/sessions/json') === false) ? $_SERVER['REQUEST_URI'] : BASE_PATH . APP_URI . '/';
                $this->sess->expired = true;
                $this->logout();
            } else if (isset($this->sess->user->id)) {
                // If the user is not the right type, check for global access
                if ($this->type->id != $this->sess->user->type_id) {
                    if ($this->sess->user->global_access) {
                        $auth = true;
                    } else {
                        $this->sess->authError = true;
                        $this->logout();
                        $auth = false;
                    }
                // Else, authorize the user role
                } else if ($this->sess->user->role_id != 0) {
                    $role = Table\UserRoles::getRole($this->sess->user->role_id);
                    if ((null !== $resource) && (!$this->hasResource($resource))) {
                        $this->addResource($resource);
                    }
                    $auth = ($this->isAllowed($role, $resource, $permission));
                // Else, validate the session and record the action
                } else {
                    $auth = true;
                }
            }
        // Else, just check for a regular session
        } else if (isset($this->sess->user->id)) {
            // If the user is not the right type, check for global access
            if ($this->type->id != $this->sess->user->type_id) {
                $auth = ($this->sess->user->global_access) ? true : false;
            // Else, authorize the user role
            } else if ($this->sess->user->role_id != 0) {
                $role = Table\UserRoles::getRole($this->sess->user->role_id);
                if ((null !== $resource) && (!$this->hasResource($resource))) {
                    $this->addResource($resource);
                }
                $auth = $this->isAllowed($role, $resource, $permission);
            } else {
                $auth = true;
            }
        }

        return $auth;
    }

    /**
     * Logout method
     *
     * @param  boolean $redirect
     * @return void
     */
    public function logout($redirect = true)
    {
        // Destroy the session database entry
        if (null !== $this->sess->user->sess_id) {
            $session = Table\UserSessions::findById($this->sess->user->sess_id);
            if (isset($session->id)) {
                $session->delete();
            }
        }

        // Destroy the session object.
        unset($this->sess->user);

        // Delete the phire cookie
        $path = BASE_PATH . APP_URI;
        if ($path == '') {
            $path = '/';
        }

        $cookie = Cookie::getInstance(array('path' => $path));
        $cookie->delete('phire');

        if ($redirect) {
            $uri = ($this->basePath == '') ? '/' : $this->basePath;
            \Pop\Http\Response::redirect($uri);
        }
    }

}

