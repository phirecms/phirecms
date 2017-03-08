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

use Phire\Table;
use Pop\Cookie\Cookie;
use Pop\Mail\Mailer;

/**
 * User model class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class User extends AbstractModel
{

    /**
     * Get all users
     *
     * @param  int    $roleId
     * @param  string $username
     * @param  array  $deniedRoles
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return \Pop\Db\Record\Collection
     */
    public function getAll($roleId = null, $username = null, array $deniedRoles = null, $limit = null, $page = null, $sort = null)
    {
        $sql = Table\Users::sql();

        $sql->select([
            'id'           => DB_PREFIX . 'users.id',
            'user_role_id' => DB_PREFIX . 'users.role_id',
            'username'     => DB_PREFIX . 'users.username',
            'email'        => DB_PREFIX . 'users.email',
            'active'       => DB_PREFIX . 'users.active',
            'verified'     => DB_PREFIX . 'users.verified',
            'total_logins' => DB_PREFIX . 'users.total_logins',
            'role_id'      => DB_PREFIX . 'roles.id',
            'role_name'    => DB_PREFIX . 'roles.name'
        ])->from(Table\Users::table())
          ->leftJoin(DB_PREFIX . 'roles', [DB_PREFIX . 'users.role_id' => DB_PREFIX . 'roles.id']);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $sql->select()->offset($page)->limit($limit);
        }
        $params = [];
        $order  = $this->getSortOrder($sort, $page);
        $by     = explode(' ', $order);
        $sql->select()->orderBy($by[0], $by[1]);

        if (null !== $username) {
            $sql->select()->where('username LIKE :username');
            $params['username'] = $username . '%';
        }

        if (is_array($deniedRoles) && (count($deniedRoles) > 0)) {
            foreach ($deniedRoles as $key => $denied) {
                $sql->select()->where('role_id != :role_id' . ($key + 1));
                $params['role_id' . ($key + 1)] = $denied;
            }
        }

        if (null !== $roleId) {
            if ($roleId == 0) {
                $sql->select()->where('users.role_id IS NULL');
                $rows = (count($params) > 0) ?
                    Table\Users::execute((string)$sql, $params) :
                    Table\Users::query((string)$sql);
            } else {
                $sql->select()->where('users.role_id = :role_id');
                $params['role_id'] = $roleId;
                $rows = Table\Users::execute((string)$sql, $params);
            }
        } else {
            $rows = (count($params) > 0) ?
                Table\Users::execute((string)$sql, $params) :
                Table\Users::query((string)$sql);
        }

        return $rows;
    }

    /**
     * Get all user roles
     *
     * @return array
     */
    public function getRoles()
    {
        $roles    = Table\Roles::findAll();
        $rolesAry = [];

        foreach ($roles as $role) {
            $rolesAry[$role->id] = $role->name;
        }

        $rolesAry[0] = '[Blocked]';
        return $rolesAry;
    }

    /**
     * Get users by role ID
     *
     * @param  int $rid
     * @return \Pop\Db\Record\Collection
     */
    public function getByRoleId($rid)
    {
        return Table\Users::findBy(['role_id' => (int)$rid]);
    }

    /**
     * Get users by role name
     *
     * @param  string $name
     * @return \Pop\Db\Record\Collection
     */
    public function getByRole($name)
    {
        $role  = Table\Roles::findBy(['name' => $name]);
        $users = [];
        if (isset($role->id)) {
            $users = Table\Users::findBy(['role_id' => $role->id]);
        }

        return $users;
    }

    /**
     * Get user by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $user = Table\Users::findById((int)$id);
        if (isset($user->id)) {
            $this->data['id']              = $user->id;
            $this->data['role_id']         = $user->role_id;
            $this->data['username']        = $user->username;
            $this->data['email']           = $user->email;
            $this->data['active']          = $user->active;
            $this->data['verified']        = $user->verified;
            $this->data['last_ip']         = $user->last_ip;
            $this->data['last_ua']         = $user->last_ua;
            $this->data['total_logins']    = $user->total_logins;
            $this->data['failed_attempts'] = $user->failed_attempts;
        }
    }

    /**
     * Save new user
     *
     * @param  mixed  $form
     * @param  string $title
     * @param  Mailer $mailer
     * @return void
     */
    public function save($form, $title, Mailer $mailer)
    {
        $user = new Table\Users([
            'role_id'    => $form['role_id'],
            'username'   => $form['username'],
            'password'   => password_hash($form['password1'], PASSWORD_BCRYPT),
            'email'      => (isset($form['email']) ? $form['email'] : null),
            'active'     => (int)$form['active'],
            'verified'   => (int)$form['verified']
        ]);
        $user->save();

        $this->data = array_merge($this->data, $user->toArray());

        if ((!$user->verified) && !empty($user->email)) {
            $notify = new Notification();
            $notify->sendVerification($user, $title, $mailer);
        }
    }

    /**
     * Update an existing user
     *
     * @param  mixed                $form
     * @param  string               $title
     * @param  Mailer               $mailer
     * @param  \Pop\Session\Session $sess
     * @return void
     */
    public function update($form, $title, Mailer $mailer, \Pop\Session\Session $sess = null)
    {
        $user = Table\Users::findById((int)$form['id']);
        if (isset($user->id)) {
            $oldRoleId = $user->role_id;
            $oldActive = $user->active;

            $user->role_id         = (isset($form['role_id']) ? $form['role_id'] : $user->role_id);
            $user->username        = $form['username'];
            $user->password        = (!empty($form['password1'])) ?
                password_hash($form['password1'], PASSWORD_BCRYPT) : $user->password;
            $user->email           = (isset($form['email']) ? $form['email'] : $user->email);
            $user->active          = (isset($form['active']) ? (int)$form['active'] : $user->active);
            $user->verified        = (isset($form['verified']) ? (int)$form['verified'] : $user->verified);
            $user->total_logins    = (isset($form['clear_logins']) ? 0 : $user->total_logins);
            $user->failed_attempts = (isset($form['failed_attempts']) ? (int)$form['failed_attempts'] : $user->failed_attempts);

            $user->save();

            if ((null !== $sess) && ($sess->user->id == $user->id)) {
                $sess->user->username = $user->username;
                $sess->user->email    = $user->email;
            }

            $this->data = array_merge($this->data, $user->toArray());

            if ((((null === $oldRoleId) && (null !== $user->role_id)) || ((!($oldActive) && ($user->active)))) && !empty($user->email)) {
                $notify = new Notification();
                $notify->sendApproval($user, $title, $mailer);
            }
        }
    }

    /**
     * Process users
     *
     * @param  array  $post
     * @param  string $title
     * @param  Mailer $mailer
     * @return void
     */
    public function process(array $post, $title, Mailer $mailer)
    {
        if (isset($post['process_users'])) {
            foreach ($post['process_users'] as $id) {
                $user = Table\Users::findById((int)$id);
                if (isset($user->id)) {
                    switch ((int)$post['user_process_action']) {
                        case 1:
                            $user->active = 1;
                            $user->save();
                            $notify = new Notification();
                            $notify->sendApproval($user, $title, $mailer);
                            break;
                        case 0:
                            $user->active = 0;
                            $user->save();
                            break;
                        case -1:
                            $user->delete();
                            break;
                    }
                }
            }
        }
    }

    /**
     * Login a user
     *
     * @param  mixed                $user
     * @param  \Pop\Session\Session $sess
     * @return void
     */
    public function login($user, $sess)
    {
        $user->failed_attempts = 0;
        $user->total_logins++;
        $user->save();

        $role = Table\Roles::findById($user->role_id);

        $sess->user = new \ArrayObject([
            'id'           => $user->id,
            'role_id'      => $user->role_id,
            'role'         => $role->name,
            'username'     => $user->username,
            'email'        => $user->email,
            'last_login'   => $user->last_login,
            'last_ip'      => $user->last_ip
        ], \ArrayObject::ARRAY_AS_PROPS);

        if (php_sapi_name() != 'cli') {
            $path = BASE_PATH . APP_URI;
            if ($path == '') {
                $path = '/';
            }
            $cookie = Cookie::getInstance(['path' => $path]);
            $cookie->set('phire', [
                'base_path'    => BASE_PATH,
                'app_path'     => APP_PATH,
                'content_path' => CONTENT_PATH,
                'app_uri'      => APP_URI
            ]);
        }
    }

    /**
     * Record a failed login attempt
     *
     * @param  mixed $user
     * @return void
     */
    public function failed($user)
    {
        $user->failed_attempts++;
        $user->save();
    }

    /**
     * Logout a user
     *
     * @param  \Pop\Session\Session $sess
     * @return void
     */
    public function logout($sess)
    {
        $user = Table\Users::findById($sess->user->id);


        $path = BASE_PATH . APP_URI;
        if ($path == '') {
            $path = '/';
        }
        $cookie = Cookie::getInstance(['path' => $path]);
        $cookie->delete('phire');

        if (isset($user->id)) {
            $user->last_login = date('Y-m-d H:i:s');
            $user->last_ip    = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
            $user->last_ua    = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
            $user->save();
        }

        unset($sess->user);
    }

    /**
     * Verify a user
     *
     * @param  int    $id
     * @param  string $hash
     * @return boolean
     */
    public function verify($id, $hash)
    {
        $result = false;
        $user   = Table\Users::findById((int)$id);

        if (isset($user->id) && ($hash == sha1($user->email))) {
            $user->verified = 1;
            $user->save();
            $this->data['id'] = $user->id;
            $result = true;
        }

        return $result;
    }

    /**
     * Send a user a forgot password reminder
     *
     * @param  mixed  $form
     * @param  string $title
     * @param  Mailer $mailer
     * @return void
     */
    public function forgot($form, $title, Mailer $mailer)
    {
        $user = Table\Users::findOne(['email' => $form['email']]);
        if (isset($user->id)) {
            $this->data['id'] = $user->id;
            $notify = new Notification();
            $notify->sendReset($user, $title, $mailer);
        }
    }

    /**
     * Determine if list of users has pages
     *
     * @param  int    $limit
     * @param  int    $roleId
     * @param  string $username
     * @param  array  $deniedRoles
     * @return boolean
     */
    public function hasPages($limit, $roleId = null, $username = null, array $deniedRoles = [])
    {
        $params = [];
        $sql    = Table\Users::sql();
        $sql->select()->from(Table\Users::table());

        if (null !== $username) {
            $sql->select()->where('username LIKE :username');
            $params['username'] = $username . '%';
        }

        if (null !== $roleId) {
            $sql->select()->where('role_id = :role_id');
            $params['role_id'] = $roleId;
        }

        if (count($deniedRoles) > 0) {
            foreach ($deniedRoles as $key => $denied) {
                $sql->select()->where('role_id != :role_id' . ($key + 1));
                $params['role_id' . ($key + 1)] = $denied;
            }
        }

        if (count($params) > 0) {
            return (Table\Users::execute((string)$sql, $params, Table\Users::AS_ARRAY)->count() > $limit);
        } else {
            return (Table\Users::findAll(null, Table\Users::AS_ARRAY)->count() > $limit);
        }
    }

    /**
     * Get count of users
     *
     * @param  int    $roleId
     * @param  string $username
     * @param  array  $deniedRoles
     * @return int
     */
    public function getCount($roleId = null, $username = null, array $deniedRoles = [])
    {
        $params = [];
        $sql    = Table\Users::sql();
        $sql->select()->from(Table\Users::table());

        if (null !== $username) {
            $sql->select()->where('username LIKE :username');
            $params['username'] = $username . '%';
        }

        if (null !== $roleId) {
            $sql->select()->where('role_id = :role_id');
            $params['role_id'] = $roleId;
        }

        if (count($deniedRoles) > 0) {
            foreach ($deniedRoles as $key => $denied) {
                $sql->select()->where('role_id != :role_id' . ($key + 1));
                $params['role_id' . ($key + 1)] = $denied;
            }
        }

        if (count($params) > 0) {
            return Table\Users::execute((string)$sql, $params, Table\Users::AS_ARRAY)->count();
        } else {
            return Table\Users::findAll(null, Table\Users::AS_ARRAY)->count();
        }
    }

}