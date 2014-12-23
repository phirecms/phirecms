<?php

namespace Phire\Model;

use Phire\Table;
use Pop\Acl;

class UserRole extends AbstractModel
{

    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order = $this->getSortOrder($sort, $page);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            return Table\UserRoles::findAll(null, [
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            return Table\UserRoles::findAll(null, [
                'order'  => $order
            ])->rows();
        }
    }

    public function getById($id)
    {
        $role = Table\UserRoles::findById((int)$id);
        if (isset($role->id)) {
            $role = $role->getColumns();
            $role['permissions'] = (null !== $role['permissions']) ? unserialize($role['permissions']) : [];
            $this->data = array_merge($this->data, $role);
        }
    }

    public function save(array $post)
    {
        $role = new Table\UserRoles([
            'parent_id'         => ($post['parent_id'] != '----') ? (int)$post['parent_id'] : null,
            'name'              => html_entity_decode($post['name'], ENT_QUOTES, 'UTF-8'),
            'verification'      => (int)$post['verification'],
            'approval'          => (int)$post['approval'],
            'email_as_username' => (int)$post['email_as_username'],
            'permissions'       => serialize($this->getPermissions($post))
        ]);
        $role->save();

        $this->data = array_merge($this->data, $role->getColumns());
    }

    public function update(array $post)
    {
        $role = Table\UserRoles::findById((int)$post['id']);
        if (isset($role->id)) {
            $role->parent_id         = ($post['parent_id'] != '----') ? (int)$post['parent_id'] : null;
            $role->name              = html_entity_decode($post['name'], ENT_QUOTES, 'UTF-8');
            $role->verification      = (int)$post['verification'];
            $role->approval          = (int)$post['approval'];
            $role->email_as_username = (int)$post['email_as_username'];
            $role->permissions       = serialize($this->getPermissions($post));
            $role->save();

            $this->data = array_merge($this->data, $role->getColumns());
        }
    }

    public function remove(array $post)
    {
        if (isset($post['rm_roles'])) {
            foreach ($post['rm_roles'] as $id) {
                $role = Table\UserRoles::findById((int)$id);
                if (isset($role->id)) {
                    $role->delete();
                }
            }
        }
    }

    public function hasPages($limit)
    {
        return (Table\UserRoles::findAll()->count() > $limit);
    }

    public function getCount()
    {
        return Table\UserRoles::findAll()->count();
    }

    public function canRegister($id)
    {
        $result = false;
        $role = Table\UserRoles::findById((int)$id);
        if (isset($role->id)) {
            $permissions = (null !== $role->permissions) ? unserialize($role->permissions) : [];
            if (!isset($permissions[APP_URI . '/register/:id']) ||
                ((isset($permissions[APP_URI . '/register/:id']) && ($permissions[APP_URI . '/register/:id'])))) {
                $result = true;
            }
        }
        return $result;
    }

    public static function getPermissionsConfig()
    {
        $config = [
            'roles'     => [],
            'resources' => []
        ];

        $roles   = Table\UserRoles::findAll();

        foreach ($roles->rows() as $role) {
            $config['roles'][$role->id] = [
                'role'   => new Acl\Role($role->name),
                'allow'  => [],
                'deny'   => [],
                'parent' => $role->parent_id
            ];

            $permissions = (null !== $role->permissions) ? unserialize($role->permissions) : [];

            if (count($permissions) > 0) {
                foreach ($permissions as $resource => $permission) {
                    $config['resources'][$resource] = new Acl\Resource($resource);
                    if ($permission) {
                        $config['roles'][$role->id]['role']->addPermission($resource);
                        $config['roles'][$role->id]['allow'][] = $resource;
                    } else {
                        $config['roles'][$role->id]['deny'][] = $resource;
                    }
                }
            }
        }

        // Discover any parents
        foreach ($config['roles'] as $id => $role) {
            if ((null !== $role['parent']) && isset($config['roles'][$role['parent']])) {
                $role['role']->inheritsFrom($config['roles'][$role['parent']]['role']);
            }
        }

        return $config;
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