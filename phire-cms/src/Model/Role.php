<?php

namespace Phire\Model;

use Phire\Table;

class Role extends AbstractModel
{

    public function getAll()
    {
        return Table\Roles::findAll()->rows();
    }

    public function getById($id)
    {
        $role = Table\Roles::findById((int)$id);
        if (isset($role->id)) {
            $role = $role->getColumns();
            $role['permissions'] = (null !== $role['permissions']) ? unserialize($role['permissions']) : [];
            $this->data = array_merge($this->data, $role);
        }
    }

    public function save(array $post)
    {
        $role = new Table\Roles([
            'name'              => html_entity_decode($post['name'], ENT_QUOTES, 'UTF-8'),
            'verification'      => (int)$post['verification'],
            'approval'          => (int)$post['approval'],
            'email_as_username' => (int)$post['email_as_username'],
            'permissions'       => serialize($this->getPermissions($post))
        ]);
        $role->save();
    }

    public function update(array $post)
    {
        $role = Table\Roles::findById((int)$post['id']);
        if (isset($role->id)) {
            $role->name              = html_entity_decode($post['name'], ENT_QUOTES, 'UTF-8');
            $role->verification      = (int)$post['verification'];
            $role->approval          = (int)$post['approval'];
            $role->email_as_username = (int)$post['email_as_username'];
            $role->permissions       = serialize($this->getPermissions($post));
            $role->save();
        }
    }

    public function remove(array $post)
    {
        if (isset($post['rm_roles'])) {
            foreach ($post['rm_roles'] as $id) {
                $role = Table\Roles::findById((int)$id);
                if (isset($role->id)) {
                    $role->delete();
                }
            }
        }
    }

    protected function getPermissions(array $post)
    {
        $permissions = [];

        foreach ($post as $key => $value) {
            if (strpos($key, 'permission_') !== false) {
                $allow = $post['allow_' . substr($key, 11)];
                if (($value != '----') && ($allow != '----')) {
                    $permissions[$value] = $allow;
                }
            }
        }

        foreach ($post as $key => $value) {
            if ((strpos($key, 'rm_permissions') !== false) && isset($value[0]) && isset($permissions[$value[0]])) {
                unset($permissions[$value[0]]);
            }
        }

        return $permissions;
    }

}