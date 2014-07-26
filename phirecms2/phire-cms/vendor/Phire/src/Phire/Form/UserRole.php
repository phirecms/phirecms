<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Element;
use Pop\Validator;
use Phire\Table\UserRoles;
use Phire\Table\UserTypes;

class UserRole extends AbstractForm
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string      $action
     * @param  string      $method
     * @param  int         $rid
     * @param  \Pop\Config $config
     * @return self
     */
    public function __construct($action = null, $method = 'post', $rid = 0, $config = null)
    {
        parent::__construct($action, $method, null, '        ');
        $this->initFieldsValues = $this->getInitFields($rid, $config);
        $this->setAttributes('id', 'user-role-form');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $filters
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null)
    {
        parent::setFieldValues($values, $filters);
        $this->checkFiles();
        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int         $rid
     * @param  \Pop\Config $config
     * @return array
     */
    protected function getInitFields($rid = 0, $config = null)
    {
        // Get types for the user role
        $typesAry = array();
        $types = UserTypes::findAll('id ASC');
        foreach ($types->rows as $type) {
            $typesAry[$type->id] = $type->type;
        }

        // Create initial fields
        $fields1 = array(
            'name' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Name'),
                'required'   => true,
                'attributes' => array(
                    'size'  => 75,
                    'style' => 'width: 600px;'
                )
            )
        );

        if ($rid != 0) {
            $fields1['name']['attributes']['onkeyup'] = "phire.updateTitle('#user-role-title', this);";
        }

        // Get any existing field values
        $fields2 = array();
        $fieldGroups = array();

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, 0, $rid);
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
            }
        }

        // Get available resources with their corresponding permissions
        $resources = \Phire\Model\UserRole::getResources($config);
        $classes = array('0' => '(' . $this->i18n->__('All') . ')');
        $classTypes = array();
        $classActions = array();
        foreach ($resources as $key => $resource) {
            $classes[$key] = $resource['name'];
            $classTypes[$key] = array('0' => '(' . $this->i18n->__('All') . ')');
            $classActions[$key] = array('0' => '(' . $this->i18n->__('All') . ')');
            foreach ($resource['types'] as $id => $type) {
                if ((int)$id != 0) {
                    $classTypes[$key][$id] = $type;
                }
            }
            foreach ($resource['actions'] as $permAction) {
                $classActions[$key][$permAction] = $permAction;
            }
        }

        asort($classes);

        // Get any current resource/permission fields
        if ($rid != 0) {
            $role = UserRoles::findById($rid);
            $permissions = (null !== $role->permissions) ? unserialize($role->permissions) : array();
            $i = 1;
            foreach ($permissions as $permission) {
                if (strpos($permission['permission'], '_') !== false) {
                    $permAry = explode('_', $permission['permission']);
                    $p = $permAry[0];
                    $t = $permAry[1];
                } else {
                    $p = $permission['permission'];
                    $t = '0';
                }
                $fields2['resource_cur_' . $i] = array(
                    'type'       => 'select',
                    'label'      => "&nbsp;",
                    'value'      => $classes,
                    'marked'     => $permission['resource'],
                    'attributes' => array(
                        'onchange' => 'phire.changePermissions(this);',
                        'style' => 'display: block;'
                    ),
                );
                $fields2['permission_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => $classActions[$permission['resource']],
                    'marked'     => $p,
                    'attributes' => array('style' => 'display: block; width: 150px;')
                );
                $fields2['type_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => $classTypes[$permission['resource']],
                    'marked'     => $t,
                    'attributes' => array('style' => 'display: block; width: 150px;')
                );
                $fields2['allow_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => array(
                        '1' => $this->i18n->__('allow'),
                        '0' => $this->i18n->__('deny')
                    ),
                    'marked'     => $permission['allow'],
                    'attributes' => array('style' => 'display: block; width: 150px;')
                );
                $fields2['rm_resource_' . $i] = array(
                    'type'       => 'checkbox',
                    'value'      => array($rid . '_' . $permission['resource'] . '_' . $permission['permission'] => $this->i18n->__('Remove') . '?')
                );
                $i++;
            }
        }

        // Create new resource/permission fields
        $fields3 = array(
            'resource_new_1' => array(
                'type'       => 'select',
                'label'      => '<span class="label-pad-2"><a href="#" onclick="phire.addResource(); return false;">[+]</a> ' . $this->i18n->__('Resource') . '</span><span class="label-pad-2">' . $this->i18n->__('Action') . '</span><span class="label-pad-2">' . $this->i18n->__('Type') . '</span><span class="label-pad-2">' . $this->i18n->__('Permission') . '</span>',
                'attributes' => array(
                    'onchange' => 'phire.changePermissions(this);',
                    'style' => 'display: block; margin: 3px 0 3px 0;'
                ),
                'value'      => $classes
            ),
            'permission_new_1' => array(
                'type'       => 'select',
                'attributes' => array('style' => 'display: block; width: 150px; margin: 3px 0 3px 0;'),
                'value'      => array('0' => '(' . $this->i18n->__('All') . ')')
            ),
            'type_new_1' => array(
                'type'       => 'select',
                'attributes' => array('style' => 'display: block; width: 150px; margin: 3px 0 3px 0;'),
                'value'      => array('0' => '(' . $this->i18n->__('All') . ')')
            ),
            'allow_new_1' => array(
                'type'       => 'select',
                'attributes' => array('style' => 'display: block; width: 150px; margin: 3px 0 3px 0;'),
                'value'      => array(
                    '1' => $this->i18n->__('allow'),
                    '0' => $this->i18n->__('deny')
                )
            ),
        );
        $fields4 = array(
            'submit' => array(
                'type'  => 'submit',
                'value' => $this->i18n->__('SAVE'),
                'attributes' => array(
                    'class'   => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => $this->i18n->__('UPDATE'),
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#user-role-form', true);",
                    'class'   => 'update-btn'
                )
            ),
            'type_id' => array(
                'type'       => 'select',
                'required'   => true,
                'label'      => $this->i18n->__('User Type'),
                'value'      => $typesAry,
                'attributes' => array(
                    'style'  => 'width: 200px;'
                )
            ),
            'id' => array(
                'type' => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            )
        );

        $allFields = array($fields4, $fields1);
        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }
        $allFields[] = $fields3;
        $allFields[] = $fields2;

        return $allFields;
    }
}

