<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class UserRoles extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'user_roles';

    /**
     * @var   string
     */
    protected $primaryId = 'id';

    /**
     * @var   boolean
     */
    protected $auto = true;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Get a role with its permissions
     *
     * @param  int $roleId
     * @return \Pop\Auth\Role
     */
    public static function getRole($roleId)
    {
        if ($roleId != 0) {
            $role = self::findById($roleId);
            $r = \Pop\Auth\Role::factory($role->name);

            $permissions = (null !== $role->permissions) ? unserialize($role->permissions) : array();

            foreach ($permissions as $permission) {
                if ($permission['permission'] != '') {
                    $r->addPermission($permission['permission']);
                }
            }
        } else {
            $r = \Pop\Auth\Role::factory('Blocked');
        }

        return $r;
    }

    /**
     * Get all roles, resources and permissions
     *
     * @param  int $typeId
     * @return array
     */
    public static function getAllRoles($typeId)
    {
        $results = array(
            'roles'     => array(),
            'resources' => array()
        );

        if (null !== $typeId) {
            $roles = self::findAll('id ASC', array('type_id' => $typeId));
            if (isset($roles->rows[0])) {
                foreach ($roles->rows as $role) {
                    $r = \Pop\Auth\Role::factory($role->name);
                    $results['resources'][$role->name] = array(
                        'allow' => array(),
                        'deny'  => array()
                    );

                    $permissions = (null !== $role->permissions) ? unserialize($role->permissions) : array();

                    if (isset($permissions[0])) {
                        foreach ($permissions as $permission) {
                            if (!isset($results['resources'][$role->name]['allow'][$permission['resource']])) {
                                if ($permission['allow']) {
                                    $results['resources'][$role->name]['allow'][$permission['resource']] = array();
                                } else {
                                    if (!isset($results['resources'][$role->name]['deny'][$permission['resource']])) {
                                        $results['resources'][$role->name]['deny'][$permission['resource']] = array();
                                    }
                                }
                            }
                            if ($permission['permission'] != '') {
                                $r->addPermission($permission['permission']);
                                if ($permission['resource'] != '') {
                                    if ($permission['allow']) {
                                        $results['resources'][$role->name]['allow'][$permission['resource']][] = $permission['permission'];
                                    } else {
                                        $results['resources'][$role->name]['deny'][$permission['resource']][] = $permission['permission'];
                                    }
                                }
                            }
                        }
                    }

                    $results['roles'][] = $r;
                }
            }
        }

        return $results;
    }

}

