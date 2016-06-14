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
namespace Phire\Model;

use Phire\Table;

/**
 * Role Model class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.1rc1
 */
class Role extends AbstractModel
{

    /**
     * Get all user roles
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order = $this->getSortOrder($sort, $page);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            return Table\Roles::findAll([
                'offset' => $page,
                'limit'  => $limit,
                'order'  => $order
            ])->rows();
        } else {
            return Table\Roles::findAll([
                'order'  => $order
            ])->rows();
        }
    }

    /**
     * Get user role by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $role = Table\Roles::findById((int)$id);
        if (isset($role->id)) {
            $data = $role->getColumns();
            $data['role_parent_id'] = $data['parent_id'];
            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Save new user role
     *
     * @param  array $post
     * @return void
     */
    public function save(array $post)
    {
        $role = new Table\Roles([
            'parent_id'         => ($post['role_parent_id'] != '----') ? (int)$post['role_parent_id'] : null,
            'name'              => html_entity_decode($post['name'], ENT_QUOTES, 'UTF-8'),
            'verification'      => (int)$post['verification'],
            'approval'          => (int)$post['approval'],
            'email_as_username' => (int)$post['email_as_username'],
            'email_required'    => (int)$post['email_required'],
            'permissions'       => serialize($this->getPermissions($post))
        ]);
        $role->save();

        $this->data = array_merge($this->data, $role->getColumns());
    }

    /**
     * Update an existing user role
     *
     * @param  array            $post
     * @param  \Pop\Web\Session $sess
     * @return void
     */
    public function update(array $post, $sess = null)
    {
        $role = Table\Roles::findById((int)$post['id']);
        if (isset($role->id)) {
            $role->parent_id         = ($post['role_parent_id'] != '----') ? (int)$post['role_parent_id'] : null;
            $role->name              = html_entity_decode($post['name'], ENT_QUOTES, 'UTF-8');
            $role->verification      = (int)$post['verification'];
            $role->approval          = (int)$post['approval'];
            $role->email_as_username = (int)$post['email_as_username'];
            $role->email_required    = (int)$post['email_required'];
            $role->permissions       = serialize($this->getPermissions($post));
            $role->save();

            $this->data = array_merge($this->data, $role->getColumns());

            if ((null !== $sess) && isset($sess->user) && ($sess->user->role_id == $role->id)) {
                $sess->user->role = $role->name;
            }
        }
    }

    /**
     * Remove a user role
     *
     * @param  array $post
     * @return void
     */
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

    /**
     * Determine if list of user roles have pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Roles::findAll()->count() > $limit);
    }

    /**
     * Get count of user roles
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Roles::findAll()->count();
    }

    /**
     * Determine if user role has permission to register
     *
     * @param  int    $id
     * @param  string $register
     * @return boolean
     */
    public function canRegister($id, $register = 'register')
    {
        $result = true;
        $role   = Table\Roles::findById((int)$id);

        if (isset($role->id) && (null !== $role->permissions)) {
            $permissions = unserialize($role->permissions);
            if (isset($permissions['deny'])) {
                foreach ($permissions['deny'] as $deny) {
                    if ($deny['resource'] == $register) {
                        $result = false;
                    }
                }
            }
        } else if (!isset($role->id)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Determine if user role has permission to send a password reminder
     * and reset the password
     *
     * @param  int $id
     * @return boolean
     */
    public function canSendReminder($id)
    {
        $result = true;
        $role   = Table\Roles::findById((int)$id);

        if (isset($role->id) && (null !== $role->permissions)) {
            $permissions = unserialize($role->permissions);
            if (isset($permissions['deny'])) {
                foreach ($permissions['deny'] as $deny) {
                    if ($deny['resource'] == 'forgot') {
                        $result = false;
                    }
                }
            }
        } else if (!isset($role->id)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get permissions from $_POST data
     *
     * @param  array $post
     * @return mixed
     */
    protected function getPermissions(array $post)
    {
        $permissions = [
            'allow' => [],
            'deny'  => []
        ];

        // Get new ones
        foreach ($post as $key => $value) {
            if (strpos($key, 'resource_') !== false) {
                $id         = substr($key, 9);
                $permission = $post['permission_' . $id];
                if (($value != '----') && ($permission != '----')) {
                    if ((bool)$permission) {
                        $permissions['allow'][] = [
                            'resource'   => $value,
                            'permission' => ((!empty($post['action_' . $id]) &&
                                ($post['action_' . $id] != '----')) ?
                                $post['action_' . $id] : null),
                        ];
                    } else {
                        $permissions['deny'][] = [
                            'resource'   => $value,
                            'permission' => ((!empty($post['action_' . $id]) &&
                                ($post['action_' . $id] != '----')) ?
                                $post['action_' . $id] : null),
                        ];
                    }
                }
            }
        }

        return $permissions;
    }

}