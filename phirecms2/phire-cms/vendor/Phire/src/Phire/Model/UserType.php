<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\Filter\String;
use Phire\Table;

class UserType extends AbstractModel
{

    /**
     * Get all types method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);

        $sql = Table\UserTypes::getSql();
        $sql->select(array(DB_PREFIX . 'user_types.id', DB_PREFIX . 'user_types.type'))
            ->orderBy($order['field'], $order['order']);

        if (null !== $order['limit']) {
            $sql->select()->limit($order['limit'])
                          ->offset($order['offset']);
        }

        $types = Table\UserTypes::execute($sql->render(true));

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\TypesController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_types[]" id="remove_types[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_types" />';
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
                'id'      => 'type-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/types/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/users/types?sort=id">#</a>',
                    'edit'    => '<span style="display: block; margin: 0 auto; width: 100%; text-align: center;">' . $this->i18n->__('Edit') . '</span>',
                    'type'    => '<a href="' . BASE_PATH . APP_URI . '/users/types?sort=type">' . $this->i18n->__('Type') . '</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'separator' => '',
            'exclude'   => array(
                'process' => array('id' => $this->data['user']->type_id)
            ),
            'indent'    => '        '
        );

        if (isset($types->rows[0])) {
            $typeRows = array();
            foreach ($types->rows as $type) {
                if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\TypesController', 'edit')) {
                    $edit = '<a class="edit-link" title="' . $this->i18n->__('Edit') . '" href="' . BASE_PATH . APP_URI . '/users/types/edit/' . $type->id . '">Edit</a>';
                } else {
                    $edit = null;
                }

                $type->type = ucwords(str_replace('-', ' ', $type->type));
                $tAry = array(
                    'id'   => $type->id,
                    'type' => $type->type
                );

                if (null !== $edit) {
                    $tAry['edit'] = $edit;
                }

                $typeRows[] = $tAry;
            }
            $this->data['table'] = Html::encode($typeRows, $options, $this->config->pagination_limit, $this->config->pagination_range, Table\UserTypes::getCount());
        }
    }

    /**
     * Get type by ID method
     *
     * @param  int     $id
     * @return void
     */
    public function getById($id)
    {
        $type = Table\UserTypes::findById($id);
        if (isset($type->id)) {
            $typeValues = $type->getValues();
            $typeValues['log_in'] = $typeValues['login'];
            unset($typeValues['login']);

            if (!empty($typeValues['reset_password_interval']) && ($typeValues['reset_password_interval'] != '1st')) {
                $resetAry = explode(' ', $typeValues['reset_password_interval']);
                $typeValues['reset_password_interval']       = 'Every';
                $typeValues['reset_password_interval_value'] = $resetAry[0];
                $typeValues['reset_password_interval_unit']  = $resetAry[1];
            }
            $typeValues = array_merge($typeValues, FieldValue::getAll($id));

            $this->data = array_merge($this->data, $typeValues);
        }
    }

    /**
     * Save type
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $fields = $form->getFields();

        $fields['type'] = String::slug($fields['type']);
        $fields['login'] = $fields['log_in'];
        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        if ($fields['default_role_id'] == 0) {
            $fields['default_role_id'] = null;
        }

        unset($fields['log_in']);
        unset($fields['id']);
        unset($fields['submit']);
        unset($fields['update_value']);
        unset($fields['update']);

        $fieldsAry = array();

        $reset = '';
        if (($fields['reset_password']) && ($fields['reset_password_interval'] != '')) {
            if (isset($fields['reset_password_interval'])) {
                switch ($fields['reset_password_interval']) {
                    case '1st':
                        $reset = '1st';
                        break;
                    case 'Every':
                        $resetValue = (int)$fields['reset_password_interval_value'];
                        if ($resetValue == 0) {
                            $resetValue = 1;
                        }
                        $resetUnit = ($fields['reset_password_interval_unit'] != '--') ? $fields['reset_password_interval_unit'] : 'Days';
                        $reset = $resetValue . ' ' . $resetUnit;
                        break;
                }
            }
        }

        unset($fields['reset_password_interval_value']);
        unset($fields['reset_password_interval_unit']);
        $fields['reset_password_interval']    = $reset;
        $fieldsAry['reset_password_interval'] = $reset;

        foreach ($fields as $key => $value) {
            if (substr($key, 0, 6) == 'field_') {
                $fieldsAry[$key] = $value;
                unset($fields[$key]);
            }
        }

        $type = new Table\UserTypes($fields);
        $type->save();
        $this->data['id'] = $type->id;

        FieldValue::save($fieldsAry, $type->id);
    }

    /**
     * Update type
     *
     * @param \Pop\Form\Form $form
     * @param  \Pop\Config   $config
     * @return void
     */
    public function update(\Pop\Form\Form $form, $config)
    {
        $fields = $form->getFields();

        $fields['type'] = String::slug($fields['type']);
        $fields['login'] = $fields['log_in'];
        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        if ($fields['default_role_id'] == 0) {
            $fields['default_role_id'] = null;
        }

        unset($fields['log_in']);
        unset($fields['submit']);
        unset($fields['update_value']);
        unset($fields['update']);

        $type = Table\UserTypes::findById($form->id);
        $oldEnc = $type->password_encryption;
        $newEnc = $fields['password_encryption'];

        // Extract dynamic field values out of the form
        $fieldsAry = array();

        $reset = '';
        if (($fields['reset_password']) && ($fields['reset_password_interval'] != '')) {
            if (isset($fields['reset_password_interval'])) {
                switch ($fields['reset_password_interval']) {
                    case '1st':
                        $reset = '1st';
                        break;
                    case 'Every':
                        $resetValue = (int)$fields['reset_password_interval_value'];
                        if ($resetValue == 0) {
                            $resetValue = 1;
                        }
                        $resetUnit = ($fields['reset_password_interval_unit'] != '--') ? $fields['reset_password_interval_unit'] : 'Days';
                        $reset = $resetValue . ' ' . $resetUnit;
                        break;
                }
            }
        }

        unset($fields['reset_password_interval_value']);
        unset($fields['reset_password_interval_unit']);
        $fields['reset_password_interval']    = $reset;
        $fieldsAry['reset_password_interval'] = $reset;

        foreach ($fields as $key => $value) {
            if (substr($key, 0, 6) == 'field_') {
                $fieldsAry[$key] = $value;
                unset($fields[$key]);
            }
        }

        // Save updated type fields
        $type->setValues($fields);
        $type->update();
        $this->data['id'] = $type->id;

        // If the password encryption changed
        if ($oldEnc != $newEnc) {
            $users = Table\Users::findAll(null, array('type_id' => $type->id));
            foreach ($users->rows as $u) {
                $user = Table\Users::findById($u->id);
                if (isset($user->id)) {
                    $u = new User();
                    $u->sendReminder($user->email, $config);
                }
            }
        }

        FieldValue::update($fieldsAry, $type->id);
    }

    /**
     * Remove user type
     *
     * @param  array   $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_types'])) {
            foreach ($post['remove_types'] as $id) {
                $type = Table\UserTypes::findById($id);
                if (isset($type->id)) {
                    \Phire\Table\Fields::deleteByType($type->id);
                    $type->delete();
                }
            }
        }
    }

}

