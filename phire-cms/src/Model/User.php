<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;
use Pop\Crypt\Bcrypt;
use Pop\Db\Sql;
use Pop\Filter\Random;
use Pop\Mail\Mail;

/**
 * User Model class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.0
 */
class User extends AbstractModel
{

    /**
     * Get all users
     *
     * @param  int    $roleId
     * @param  array  $search
     * @param  array  $deniedRoles
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($roleId = null, array $search = null, array $deniedRoles = null, $limit = null, $page = null, $sort = null)
    {
        $sql = Table\Users::sql();
        $sql->select([
            'id'           => DB_PREFIX . 'users.id',
            'user_role_id' => DB_PREFIX . 'users.role_id',
            'username'     => DB_PREFIX . 'users.username',
            'first_name'   => DB_PREFIX . 'users.first_name',
            'last_name'    => DB_PREFIX . 'users.last_name',
            'company'      => DB_PREFIX . 'users.company',
            'email'        => DB_PREFIX . 'users.email',
            'active'       => DB_PREFIX . 'users.active',
            'verified'     => DB_PREFIX . 'users.verified',
            'role_id'      => DB_PREFIX . 'roles.id',
            'role_name'    => DB_PREFIX . 'roles.name'
        ])->join(DB_PREFIX . 'roles', [DB_PREFIX . 'users.role_id' => DB_PREFIX . 'roles.id']);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $sql->select()->offset($page)->limit($limit);
        }

        $params = [];
        $order  = $this->getSortOrder($sort, $page);
        $by     = explode(' ', $order);
        $sql->select()->orderBy($by[0], $by[1]);

        if (null !== $search) {
            $sql->select()->where($search['by'] . ' LIKE :' . $search['by']);
            $params[$search['by']] = $search['for'] . '%';
        }

        if (is_array($deniedRoles) && (count($deniedRoles) > 0)) {
            foreach ($deniedRoles as $key => $denied) {
                $sql->select()->where('role_id != :role_id' . ($key + 1));
                $params['role_id' . ($key + 1)] = $denied;
            }
        }

        if (null !== $roleId) {
            if ($roleId == 0) {
                $sql->select()->where(DB_PREFIX . 'users.role_id IS NULL');
                $rows = (count($params) > 0) ?
                    Table\Users::execute((string)$sql, $params)->rows() :
                    Table\Users::query((string)$sql)->rows();
            } else {
                $sql->select()->where(DB_PREFIX . 'users.role_id = :role_id');
                $params['role_id'] = $roleId;
                $rows = Table\Users::execute((string)$sql, $params)->rows();
            }
        } else {
            $rows = (count($params) > 0) ?
                Table\Users::execute((string)$sql, $params)->rows() :
                Table\Users::query((string)$sql)->rows();
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
        $roles    = Table\Roles::findAll()->rows();
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
     * @return array
     */
    public function getByRoleId($rid)
    {
        return Table\Users::findBy(['role_id' => (int)$rid])->rows();
    }

    /**
     * Get users by role name
     *
     * @param  string $name
     * @return array
     */
    public function getByRole($name)
    {
        $role  = Table\Roles::findBy(['name' => $name]);
        $users = [];
        if (isset($role->id)) {
            $users = Table\Users::findBy(['role_id' => $role->id])->rows();
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
            $this->data['role_id']    = $user->role_id;
            $this->data['username']   = $user->username;
            $this->data['first_name'] = $user->first_name;
            $this->data['last_name']  = $user->last_name;
            $this->data['company']    = $user->company;
            $this->data['email']      = $user->email;
            $this->data['phone']      = $user->phone;
            $this->data['active']     = $user->active;
            $this->data['verified']   = $user->verified;
            $this->data['id']         = $user->id;
        }
    }

    /**
     * Save new user
     *
     * @param  array $fields
     * @return void
     */
    public function save(array $fields)
    {
        $user = new Table\Users([
            'role_id'    => $fields['role_id'],
            'username'   => (isset($fields['username'])) ? $fields['username'] : $fields['email'],
            'password'   => (new Bcrypt())->create($fields['password1']),
            'first_name' => (isset($fields['first_name']) ? $fields['first_name'] : null),
            'last_name'  => (isset($fields['last_name']) ? $fields['last_name'] : null),
            'company'    => (isset($fields['company']) ? $fields['company'] : null),
            'email'      => (isset($fields['email']) ? $fields['email'] : null),
            'phone'      => (isset($fields['phone']) ? $fields['phone'] : null),
            'active'     => (int)$fields['active'],
            'verified'   => (int)$fields['verified']
        ]);
        $user->save();

        $this->data = array_merge($this->data, $user->getColumns());

        if ((!$user->verified) && !empty($user->email)) {
            $this->sendVerification($user);
        }
    }

    /**
     * Update an existing user
     *
     * @param  array            $fields
     * @param  \Pop\Web\Session $sess
     * @return void
     */
    public function update(array $fields, \Pop\Web\Session $sess = null)
    {
        $user = Table\Users::findById((int)$fields['id']);
        if (isset($user->id)) {
            $oldRoleId = $user->role_id;
            $username  = $user->username;
            $role      = Table\Roles::findById($fields['role_id']);
            if (($role->email_as_username) && isset($fields['email']) && !empty($fields['email'])) {
                $username = $fields['email'];
            } else if (isset($fields['username']) && !empty($fields['username'])) {
                $username = $fields['username'];
            }

            $user->role_id    = $fields['role_id'];
            $user->username   = $username;
            $user->password   = (!empty($fields['password1'])) ?
                (new Bcrypt())->create($fields['password1']) : $user->password;
            $user->first_name = (isset($fields['first_name']) ? $fields['first_name'] : $user->first_name);
            $user->last_name  = (isset($fields['last_name']) ? $fields['last_name'] : $user->last_name);
            $user->company    = (isset($fields['company']) ? $fields['company'] : $user->company);
            $user->email      = (isset($fields['email']) ? $fields['email'] : $user->email);
            $user->phone      = (isset($fields['phone']) ? $fields['phone'] : $user->phone);
            $user->active     = (isset($fields['active']) ? (int)$fields['active'] : $user->active);
            $user->verified   = (isset($fields['verified']) ? (int)$fields['verified'] : $user->verified);

            $user->save();

            if ((null !== $sess) && ($sess->user->id == $user->id)) {
                $sess->user->username = $user->username;
                $sess->user->email    = $user->email;
            }

            $this->data = array_merge($this->data, $user->getColumns());

            if ((null === $oldRoleId) && (null !== $user->role_id) && !empty($user->email)) {
                $this->sendApproval($user);
            }
        }
    }

    /**
     * Remove a user
     *
     * @param  array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['rm_users'])) {
            foreach ($post['rm_users'] as $id) {
                $user = Table\Users::findById((int)$id);
                if (isset($user->id)) {
                    $user->delete();
                }
            }
        }
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
     * @param  array $fields
     * @return void
     */
    public function forgot(array $fields)
    {
        $user = Table\Users::findBy(['email' => $fields['email']]);
        if (isset($user->id)) {
            $this->data['id'] = $user->id;
            $this->sendReminder($user);
        }
    }

    /**
     * Unsubscribe a user from the application
     *
     * @param  array $fields
     * @return void
     */
    public function unsubscribe(array $fields)
    {
        $user = Table\Users::findBy(['email' => $fields['email']]);
        if (isset($user->id)) {
            $this->data['id'] = $user->id;
            $user->delete();
            $this->sendUnsubscribe($user);
        }
    }

    /**
     * Determine if list of users have pages
     *
     * @param  int    $limit
     * @param  int    $roleId
     * @param  array  $search
     * @param  array  $deniedRoles
     * @return boolean
     */
    public function hasPages($limit, $roleId = null, array $search = null, array $deniedRoles = [])
    {
        $params = [];
        $sql    = Table\Users::sql();
        $sql->select();

        if (null !== $search) {
            $sql->select()->where($search['by'] . ' LIKE :' . $search['by']);
            $params[$search['by']] = $search['for'] . '%';
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
            return (Table\Users::execute((string)$sql, $params)->count() > $limit);
        } else {
            return (Table\Users::findAll()->count() > $limit);
        }
    }

    /**
     * Get count of users
     *
     * @param  int    $roleId
     * @param  array  $search
     * @param  array  $deniedRoles
     * @return int
     */
    public function getCount($roleId = null, array $search = null, array $deniedRoles = [])
    {
        $params = [];
        $sql    = Table\Users::sql();
        $sql->select();

        if (null !== $search) {
            $sql->select()->where($search['by'] . ' LIKE :' . $search['by']);
            $params[$search['by']] = $search['for'] . '%';
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
            return Table\Users::execute((string)$sql, $params)->count();
        } else {
            return Table\Users::findAll()->count();
        }
    }

    /**
     * Send user verification notification
     *
     * @param  Table\Users $user
     * @return void
     */
    protected function sendVerification(Table\Users $user)
    {
        $docRoot = Table\Config::findById('document_root')->value;
        $host    = Table\Config::findById('domain')->value;
        $domain  = str_replace('www.', '', $host);

        $basePath = str_replace([realpath($docRoot), '\\'], ['', '/'], realpath(__DIR__ . '/../../../'));
        $basePath = (!empty($basePath) ? $basePath : '');

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'url'    => 'http://' . $host . $basePath . APP_URI . '/verify/' .
                $user->id . '/' . sha1($user->email),
            'domain' => $domain
        ];

        // Check for an override template
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/verify.txt')) ?
            __DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/verify.txt' :
            __DIR__ . '/../../view/phire/mail/verify.txt';

        // Send email verification
        $mail = new Mail($domain . ' - Email Verification', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

    /**
     * Send user approval notification
     *
     * @param  Table\Users $user
     * @return void
     */
    protected function sendApproval(Table\Users $user)
    {
        $host   = Table\Config::findById('domain')->value;
        $domain = str_replace('www.', '', $host);

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'domain' => $domain
        ];

        // Check for an override template
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/approval.txt')) ?
            __DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/approval.txt' :
            __DIR__ . '/../../view/phire/mail/approval.txt';

        // Send email verification
        $mail = new Mail($domain . ' - Approval', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

    /**
     * Send user password reminder notification
     *
     * @param  Table\Users $user
     * @return void
     */
    protected function sendReminder(Table\Users $user)
    {
        $host           = Table\Config::findById('domain')->value;
        $domain         = str_replace('www.', '', $host);
        $newPassword    = Random::create(8, Random::ALPHANUM|Random::LOWERCASE);
        $user->password = (new Bcrypt())->create($newPassword);
        $user->save();

        // Set the recipient
        $rcpt = [
            'name'     => $user->username,
            'email'    => $user->email,
            'domain'   => $domain,
            'username' => $user->username,
            'password' => $newPassword
        ];

        // Check for an override template
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/forgot.txt')) ?
            __DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/forgot.txt' :
            __DIR__ . '/../../view/phire/mail/forgot.txt';

        // Send email verification
        $mail = new Mail($domain . ' - Forgot Password', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

    /**
     * Send user unsubscribe notification
     *
     * @param  Table\Users $user
     * @return void
     */
    protected function sendUnsubscribe(Table\Users $user)
    {
        $host   = Table\Config::findById('domain')->value;
        $domain = str_replace('www.', '', $host);

        // Set the recipient
        $rcpt = [
            'name'     => $user->username,
            'email'    => $user->email,
            'domain'   => $domain
        ];

        // Check for an override template
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/unsubscribe.txt')) ?
            __DIR__ . '/../../..' . MODULES_PATH . '/phire/view/phire/mail/unsubscribe.txt' :
            __DIR__ . '/../../view/phire/mail/unsubscribe.txt';

        // Send email verification
        $mail = new Mail($domain . ' - Unsubscribed', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

}