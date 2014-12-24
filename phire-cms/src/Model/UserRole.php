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

    protected function getPermissions(array $post)
    {
        $permissions = [
            'allow' => [],
            'deny'  => []
        ];

        // Get new ones
        foreach ($post as $key => $value) {
            if (strpos($key, 'resource_new_') !== false) {
                $id    = substr($key, 13);
                $allow = $post['allow_new_' . $id];
                if (($value != '----') && ($allow != '----')) {
                    if ((bool)$allow) {
                        $permissions['allow'][] = [
                            'resource'   => $value,
                            'permission' => ((!empty($post['permission_new_' . $id]) && ($post['permission_new_' . $id] != '----')) ?
                                $post['permission_new_' . $id] : null),
                        ];
                    } else {
                        $permissions['deny'][] = [
                            'resource'   => $value,
                            'permission' => ((!empty($post['permission_new_' . $id]) && ($post['permission_new_' . $id] != '----')) ?
                                $post['permission_new_' . $id] : null),
                        ];
                    }
                }
            }
        }

        // Remove old ones
        foreach ($post as $key => $value) {
            if ((strpos($key, 'rm_resources') !== false) && isset($value[0])) {
                $id = $value[0];
                unset($post['resource_cur_' . $id]);
                unset($post['permission_cur_' . $id]);
                unset($post['allow_cur_' . $id]);
            }
        }

        // Save any remaining old ones
        foreach ($post as $key => $value) {
            if (strpos($key, 'resource_cur_') !== false) {
                $id    = substr($key, 13);
                $allow = $post['allow_cur_' . $id];
                if (($value != '----') && ($allow != '----')) {
                    if ((bool)$allow) {
                        $permissions['allow'][] = [
                            'resource'   => $value,
                            'permission' => ((!empty($post['permission_cur_' . $id]) && ($post['permission_cur_' . $id] != '----')) ?
                                $post['permission_cur_' . $id] : null),
                        ];
                    } else {
                        $permissions['deny'][] = [
                            'resource'   => $value,
                            'permission' => ((!empty($post['permission_cur_' . $id]) && ($post['permission_cur_' . $id] != '----')) ?
                                $post['permission_cur_' . $id] : null),
                        ];
                    }
                }
            }
        }

        return $permissions;
    }

}