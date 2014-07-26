<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\File\Dir;
use Phire\Table;

class UserRole extends AbstractModel
{

    /**
     * Static method to get model types
     *
     * @param  \Pop\Config $config
     * @return array
     */
    public static function getResources($config = null)
    {
        $resources = array();
        $exclude = array();
        $override = null;

        // Get any exclude or override config values
        if (null !== $config) {
            $configAry = $config->asArray();
            if (isset($configAry['exclude_controllers'])) {
                $exclude = $configAry['exclude_controllers'];
            }
            if (isset($configAry['override'])) {
                $override = $configAry['override'];
            }
        }

        // If override, set overridden resources
        if (null !== $override) {
            foreach ($override as $resource) {
                $resources[] = $resource;
            }
        // Else, get all controllers from the system and module directories
        } else {
            $systemDirectory = new Dir(realpath(__DIR__ . '/../../../../'), true);
            $systemModuleDirectory = new Dir(realpath(__DIR__ . '/../../../../../module/'), true);
            $moduleDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules'), true);
            $dirs = array_merge($systemDirectory->getFiles(), $systemModuleDirectory->getFiles(), $moduleDirectory->getFiles());
            sort($dirs);

            // Dir clean up
            foreach ($dirs as $key => $dir) {
                unset($dirs[$key]);
                if (!((strpos($dir, 'config') !== false) || (strpos($dir, 'index.html') !== false))) {
                    $k = $dir;
                    if (substr($dir, -1) == DIRECTORY_SEPARATOR) {
                        $k = substr($k, 0, -1);
                    }
                    $k = substr($k, (strrpos($k, DIRECTORY_SEPARATOR) + 1));
                    $dirs[$k] = $dir;
                }
            }

            // Loop through each directory, looking for controller class files
            foreach ($dirs as $mod => $dir) {
                if (file_exists($dir . 'src/' . $mod . '/Controller')) {
                    $d = new Dir($dir . 'src/' . $mod . '/Controller', true, true, false);
                    $dFiles = $d->getFiles();
                    sort($dFiles);

                    // If found, loop through the files, getting the methods as the "permissions"
                    foreach ($dFiles as $c) {
                        if ((strpos($c, 'index.html') === false) && (strpos($c, 'Abstract') === false)) {
                            // Get all public methods from class
                            $class = str_replace(array('.php', DIRECTORY_SEPARATOR), array('', '\\'), substr($c, (strpos($c, 'src') + 4)));
                            $code = new \ReflectionClass($class);
                            $methods = ($code->getMethods(\ReflectionMethod::IS_PUBLIC));

                            $actions = array();
                            foreach ($methods as $value) {
                                if (($value->getName() !== '__construct') && ($value->class == $class)) {
                                    $action = $value->getName();
                                    if (!isset($exclude[$class]) ||
                                        (isset($exclude[$class]) && (is_array($exclude[$class])) && (!in_array($action, $exclude[$class])))) {
                                        $actions[] = $action;
                                    }
                                }
                            }

                            $types = array(0 => '(All)');

                            if (strpos($class, "\\Controller\\IndexController") === false) {
                                $classAry = explode('\\', $class);
                                $end1 = count($classAry) - 2;
                                $end2 = count($classAry) - 1;
                                $model = $classAry[0] . '_Model_';
                                if (stripos($classAry[$end2], 'index') !== false) {
                                    $model .= $classAry[$end1];
                                } else if (substr($classAry[$end2], 0, 4) == 'Type') {
                                    $model .= $classAry[$end1] . 'Type';
                                } else {
                                    $model .= str_replace('Controller', '', $classAry[$end2]);

                                }

                                if (substr($model, -3) == 'ies') {
                                    $model = substr($model, 0, -3) . 'y';
                                } else if (substr($model, -1) == 's') {
                                    $model = substr($model, 0, -1);
                                }
                                $types = \Phire\Project::getModelTypes($model);

                                // Format the resource and permissions
                                $c = str_replace(array('Controller.php', '\\'), array('', '/'), $c);
                                $c = substr($c, (strpos($c, 'Controller') + 11));
                                $c = str_replace('Phire/', '', $c);

                                if (!in_array($class, $exclude) || (isset($exclude[$class]) && is_array($exclude[$class]))) {
                                    $resources[$class] = array(
                                        'name'    => $c,
                                        'types'   => $types,
                                        'actions' => $actions
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $resources;
    }

    /**
     * Get all roles method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);

        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'user_roles.id' : $order['field'];

        // Create SQL object to get role data
        $sql = Table\UserRoles::getSql();
        $sql->select(array(
            DB_PREFIX . 'user_roles.id',
            DB_PREFIX . 'user_roles.type_id',
            DB_PREFIX . 'user_types.type',
            DB_PREFIX . 'user_roles.name'
        ))->join(DB_PREFIX . 'user_types', array('type_id', 'id'), 'LEFT JOIN')
          ->orderBy($order['field'], $order['order']);

        if (null !== $order['limit']) {
            $sql->select()->limit($order['limit'])
                          ->offset($order['offset']);
        }

        // Execute SQL query
        $roles = Table\UserRoles::execute($sql->render(true));

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\RolesController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_roles[]" id="remove_roles[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_roles" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove')
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove'),
                'style' => 'display: none;'
            );
        }

        $options = array(
            'form' => array(
                'id'      => 'role-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/roles/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/users/roles?sort=id">#</a>',
                    'edit'    => '<span style="display: block; margin: 0 auto; width: 100%; text-align: center;">' . $this->i18n->__('Edit') . '</span>',
                    'type'    => '<a href="' . BASE_PATH . APP_URI . '/users/roles?sort=type">' . $this->i18n->__('Type') . '</a>',
                    'name'    => '<a href="' . BASE_PATH . APP_URI . '/users/roles?sort=name">' . $this->i18n->__('Role') . '</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'separator' => '',
            'exclude'   => array('type_id', 'process' => array('id' => $this->data['user']->role_id)),
            'indent'    => '        '
        );

        if (isset($roles->rows[0])) {
            $rolesAry = array();
            foreach ($roles->rows as $role) {
                if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\RolesController', 'edit')) {
                    $edit = '<a class="edit-link" title="' . $this->i18n->__('Edit') . '" href="' . BASE_PATH . APP_URI . '/users/roles/edit/' . $role->id . '">Edit</a>';
                } else {
                    $edit = null;
                }

                $rAry = array(
                    'id'   => $role->id,
                    'name' => $role->name,
                    'type' => $role->type
                );

                if (null !== $edit) {
                    $rAry['edit'] = $edit;
                }

                $rolesAry[] = $rAry;
            }
            $this->data['table'] = Html::encode($rolesAry, $options, $this->config->pagination_limit, $this->config->pagination_range, Table\UserRoles::getCount());
        }
    }

    /**
     * Get role by ID method
     *
     * @param  int     $id
     * @return void
     */
    public function getById($id)
    {
        $role = Table\UserRoles::findById($id);
        if (isset($role->id)) {
            $roleValues = $role->getValues();
            $roleValues = array_merge($roleValues, FieldValue::getAll($id));
            $this->data = array_merge($this->data, $roleValues);
        }
    }

    /**
     * Save role
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $fields = $form->getFields();

        $role = new Table\UserRoles(array(
            'type_id' => $fields['type_id'],
            'name'    => $fields['name'],
        ));

        $role->save();
        $this->data['id'] = $role->id;

        // Add new permissions if any
        $perms = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'resource_new_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                if ($value != '0') {
                    $perm = (($_POST['permission_new_' . $id] != '0') ? $_POST['permission_new_' . $id] : '');
                    if ($perm != '') {
                        $perm .= (($_POST['type_new_' . $id] != '0') ? '_' . $_POST['type_new_' . $id] : '');
                    }
                    $perms[] = array(
                        'resource'   => $value,
                        'permission' => $perm,
                        'allow'      => (int)$_POST['allow_new_' . $id]
                    );
                }
            }
        }

        $role->permissions = serialize($perms);
        $role->update();

        FieldValue::save($fields, $role->id);
    }

    /**
     * Update role
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $fields = $form->getFields();

        $role = Table\UserRoles::findById($fields['id']);
        if (isset($role->id)) {
            $role->type_id = $fields['type_id'];
            $role->name    = $fields['name'];
            $role->update();

            $this->data['id'] = $role->id;
        }

        // Add new permissions if any
        $perms = array();
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'resource_new_') !== false) || (strpos($key, 'resource_cur_') !== false)) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $cur = (strpos($key, 'resource_new_') !== false) ? 'new' : 'cur';
                if ($value != '0') {
                    $perm = (($_POST['permission_' . $cur . '_' . $id] != '0') ? $_POST['permission_' . $cur . '_' . $id] : '');
                    if ($perm != '') {
                        $perm .= (($_POST['type_' . $cur . '_' . $id] != '0') ? '_' . $_POST['type_' . $cur . '_' . $id] : '');
                    }
                    $perms[] = array(
                        'resource'   => $value,
                        'permission' => $perm,
                        'allow'      => (int)$_POST['allow_' . $cur . '_' . $id]
                    );
                }
            }
        }

        // Remove and resource/permissions
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'rm_resource_') !== false) && isset($value[0])) {
                foreach ($perms as $k => $perm) {
                    if (($role->id . '_' . $perm['resource'] . '_' . $perm['permission']) == $value[0]) {
                        unset($perms[$k]);
                    }
                }
            }
        }

        $role->permissions = serialize($perms);
        $role->update();

        FieldValue::update($fields, $role->id);
    }

    /**
     * Remove user role
     *
     * @param  array   $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_roles'])) {
            foreach ($post['remove_roles'] as $id) {
                $role = Table\UserRoles::findById($id);
                if (isset($role->id)) {
                    $role->delete();
                }

                $sql = Table\UserTypes::getSql();

                if ($sql->getDbType() == \Pop\Db\Sql::SQLITE) {
                    $sql->update(array(
                        'default_role_id' => null
                    ))->where()->equalTo('default_role_id', $role->id);
                    Table\UserTypes::execute($sql->render(true));
                }

                FieldValue::remove($id);
            }
        }
    }

}

