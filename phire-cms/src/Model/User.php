<?php

namespace Phire\Model;

use Phire\Table;
use Pop\Crypt\Bcrypt;
use Pop\Db\Sql;
use Pop\Filter\String;
use Pop\Mail\Mail;

class User extends AbstractModel
{

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

    public function getAll()
    {
        $sql = Table\Users::sql();
        $sql->select([
            'user_id'   => DB_PREFIX . 'users.id',
            'role_id'   => DB_PREFIX . 'users.role_id',
            'username'  => DB_PREFIX . 'users.username',
            'email'     => DB_PREFIX . 'users.email',
            'id'        => DB_PREFIX . 'roles.id',
            'role_name' => DB_PREFIX . 'roles.name'
        ])->join(DB_PREFIX . 'roles', [DB_PREFIX . 'users.role_id' => DB_PREFIX . 'roles.id']);
        return Table\Users::query((string)$sql)->rows();
    }

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

        if (!$user->verified) {
            $this->sendVerification($user);
        }
    }

    public function update(array $fields)
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


            if ((null === $oldRoleId) && (null !== $user->role_id)) {
                $this->sendApproval($user);
            }
        }
    }

    public function forgot(array $fields)
    {
        $user = Table\Users::findBy(['email' => $fields['email']]);
        if (isset($user->id)) {
            $this->sendReminder($user);
        }
    }

    public function unsubscribe(array $fields)
    {
        $user = Table\Users::findBy(['email' => $fields['email']]);
        if (isset($user->id)) {
            $user->delete();
        }
    }

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

        // Send email verification
        $mail = new Mail($domain . ' - Email Verification', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents(__DIR__ . '/../../view/mail/verify.txt'));
        $mail->send();
    }

    protected function sendApproval(Table\Users $user)
    {
        $domain  = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'domain' => $domain
        ];

        // Send email verification
        $mail = new Mail($domain . ' - Approval', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents(__DIR__ . '/../../view/mail/approval.txt'));
        $mail->send();
    }

    protected function sendReminder(Table\Users $user)
    {
        $domain         = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $newPassword    = String::random(8, ['type' => 'alphanum', 'case' => 'lower']);
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

        // Send email verification
        $mail = new Mail($domain . ' - Forgot Password', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents(__DIR__ . '/../../view/mail/forgot.txt'));
        $mail->send();
    }

}