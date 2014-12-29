<?php

namespace Phire\Model;

use Phire\Table;
use Pop\Crypt\Bcrypt;
use Pop\Db\Sql;
use Pop\Filter\Random;
use Pop\Mail\Mail;

class User extends AbstractModel
{

    /**
     * Get all users
     *
     * @param  int    $roleId
     * @param  string $username
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($roleId = null, $username = null, $limit = null, $page = null, $sort = null)
    {
        $sql = Table\Users::sql();
        $sql->select([
            'id'           => DB_PREFIX . 'users.id',
            'user_role_id' => DB_PREFIX . 'users.role_id',
            'username'     => DB_PREFIX . 'users.username',
            'email'        => DB_PREFIX . 'users.email',
            'role_id'      => DB_PREFIX . 'user_roles.id',
            'role_name'    => DB_PREFIX . 'user_roles.name'
        ])->join(DB_PREFIX . 'user_roles', [DB_PREFIX . 'users.role_id' => DB_PREFIX . 'user_roles.id']);

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
        $roles    = Table\UserRoles::findAll()->rows();
        $rolesAry = [];

        foreach ($roles as $role) {
            $rolesAry[$role->id] = $role->name;
        }

        $rolesAry[0] = '[Blocked]';
        return $rolesAry;
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
            $this->data['role_id']  = $user->role_id;
            $this->data['username'] = $user->username;
            $this->data['email1']   = $user->email;
            $this->data['verified'] = $user->verified;
            $this->data['id']       = $user->id;
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
            'role_id'  => ($fields['role_id'] != '----') ? $fields['role_id'] : null,
            'username' => $fields['username'],
            'password' => (new Bcrypt())->create($fields['password1']),
            'email'    => $fields['email1'],
            'verified' => $fields['verified'],
            'created'  => date('Y-m-d H:i:s')
        ]);
        $user->save();

        $this->data = array_merge($this->data, $user->getColumns());

        if (!$user->verified) {
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

            $user->role_id  = ($fields['role_id'] != '----') ? $fields['role_id'] : null;
            $user->username = $fields['username'];
            $user->password = (!empty($fields['password1'])) ? (new Bcrypt())->create($fields['password1']) : $user->password;
            $user->email    = $fields['email1'];
            $user->verified = $fields['verified'];
            $user->updated  = date('Y-m-d H:i:s');

            $user->save();

            if ((null !== $sess) && ($sess->user->id == $user->id)) {
                $sess->user->username = $user->username;
                $sess->user->email    = $user->email;
            }

            $this->data = array_merge($this->data, $user->getColumns());

            if ((null === $oldRoleId) && (null !== $user->role_id)) {
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
            $user->delete();
            $this->sendUnsubscribe($user);
        }
    }

    /**
     * Determine if list of users have pages
     *
     * @param  int    $limit
     * @param  int    $roleId
     * @param  string $username
     * @return boolean
     */
    public function hasPages($limit, $roleId = null, $username = null)
    {
        $params = [];
        $sql    = Table\Users::sql();
        $sql->select();

        if (null !== $username) {
            $sql->select()->where('username LIKE :username');
            $params['username'] = $username . '%';
        }

        if (null !== $roleId) {
            $sql->select()->where('role_id = :role_id');
            $params['role_id'] = $roleId;
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
     * @param  string $username
     * @return int
     */
    public function getCount($roleId = null, $username = null)
    {
        $params = [];
        $sql    = Table\Users::sql();
        $sql->select();

        if (null !== $username) {
            $sql->select()->where('username LIKE :username');
            $params['username'] = $username . '%';
        }

        if (null !== $roleId) {
            $sql->select()->where('role_id = :role_id');
            $params['role_id'] = $roleId;
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
        $domain  = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'url'    => 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . APP_URI . '/verify/' .
                $user->id . '/' . sha1($user->email),
            'domain' => $domain
        ];

        // Check for an override template
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/verify.txt')) ?
            __DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/verify.txt' : __DIR__ . '/../../view/mail/verify.txt';

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
        $domain  = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'domain' => $domain
        ];

        // Check for an override template
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/approval.txt')) ?
            __DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/approval.txt' : __DIR__ . '/../../view/mail/approval.txt';

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
        $domain         = str_replace('www.', '', $_SERVER['HTTP_HOST']);
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
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/forgot.txt')) ?
            __DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/forgot.txt' : __DIR__ . '/../../view/mail/forgot.txt';

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
        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        // Set the recipient
        $rcpt = [
            'name'     => $user->username,
            'email'    => $user->email,
            'domain'   => $domain
        ];

        // Check for an override template
        $mailTemplate = (file_exists(__DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/unsubscribe.txt')) ?
            __DIR__ . '/../../..' . MODULE_PATH . '/phire/view/mail/unsubscribe.txt' : __DIR__ . '/../../view/mail/unsubscribe.txt';

        // Send email verification
        $mail = new Mail($domain . ' - Unsubscribed', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

}