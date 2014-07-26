<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Model;
use Phire\Table;

class Field extends AbstractForm
{

    /**
     * @var array
     */
    protected $validators = array(
        '----'                 => '----',
        'AlphaNumeric'         => 'AlphaNumeric',
        'Alpha'                => 'Alpha',
        'BetweenInclude'       => 'BetweenInclude',
        'Between'              => 'Between',
        'CreditCard'           => 'CreditCard',
        'Email'                => 'Email',
        'Equal'                => 'Equal',
        'Excluded'             => 'Excluded',
        'GreaterThanEqual'     => 'GreaterThanEqual',
        'GreaterThan'          => 'GreaterThan',
        'Included'             => 'Included',
        'Ipv4'                 => 'Ipv4',
        'Ipv6'                 => 'Ipv6',
        'IsSubnetOf'           => 'IsSubnetOf',
        'LengthBetweenInclude' => 'LengthBetweenInclude',
        'LengthBetween'        => 'LengthBetween',
        'LengthGte'            => 'LengthGte',
        'LengthGt'             => 'LengthGt',
        'LengthLte'            => 'LengthLte',
        'LengthLt'             => 'LengthLt',
        'Length'               => 'Length',
        'LessThanEqual'        => 'LessThanEqual',
        'LessThan'             => 'LessThan',
        'NotEmpty'             => 'NotEmpty',
        'NotEqual'             => 'NotEqual',
        'Numeric'              => 'Numeric',
        'RegEx'                => 'RegEx',
        'Subnet'               => 'Subnet'
    );

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string      $action
     * @param  string      $method
     * @param  int         $id
     * @param  \Pop\Config $config
     * @return self
     */
    public function __construct($action = null, $method = 'post', $id = 0, $config = null)
    {
        parent::__construct($action, $method, null, '        ');
        $this->initFieldsValues = $this->getInitFields($id, $config);
        $this->setAttributes('id', 'field-form');
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

        if ($_POST) {
            if ((strpos($this->type, 'history') !== false) && ($this->group_id != '0')) {
                $this->getElement('group_id')
                     ->addValidator(new Validator\NotEqual($this->group_id, $this->i18n->__('A field with history tracking cannot be assigned to a field group.')));
            }
            if (($this->editor != 'source') && ($this->group_id != '0')) {
                $this->getElement('group_id')
                     ->addValidator(new Validator\NotEqual($this->group_id, $this->i18n->__('An editor cannot be used on a field assigned to a field group.')));
            }
        }
    }

    /**
     * Get the init field values
     *
     * @param  int         $id
     * @param  \Pop\Config $config
     * @return array
     */
    protected function getInitFields($id = 0, $config = null)
    {
        // Get field groups
        $groups = array('0' => '----');

        $grps = Table\FieldGroups::findAll('id ASC');
        if (isset($grps->rows[0])) {
            foreach ($grps->rows as $grp) {
                $groups[$grp->id] = $grp->name;
            }
        }

        $editors = array('source' => 'Source');
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/js/ckeditor')) {
            $editors['ckeditor'] = 'CKEditor';
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/js/tinymce')) {
            $editors['tinymce'] = 'TinyMCE';
        }

        // Get any current validators
        $fields2 = array();
        $editorDisplay = 'none;';

        if ($id != 0) {
            $fld = Table\Fields::findById($id);
            if (isset($fld->id)) {
                if (strpos($fld->type, 'textarea') !== false) {
                    $editorDisplay = 'block;';
                }
                $validators = unserialize($fld->validators);
                if ($validators != '') {
                    $i = 1;
                    foreach ($validators as $key => $value) {
                        $fields2['validator_cur_' . $i] = array(
                            'type'       => 'select',
                            'label'      => '&nbsp;',
                            'value'      => $this->validators,
                            'marked'     => $key
                        );

                        $fields2['validator_value_cur_' . $i] = array(
                            'type'       => 'text',
                            'attributes' => array(
                                'size'  => 10,
                                'style' => 'display: block; padding: 4px 4px 5px 4px; margin: 0 0 4px 0; height: 17px;'
                            ),
                            'value'      => $value['value']
                        );
                        $fields2['validator_message_cur_' . $i] = array(
                            'type'       => 'text',
                            'attributes' => array(
                                'size'  => 30,
                                'style' => 'display: block; padding: 4px 4px 5px 4px; margin: 0 0 4px 0; height: 17px;'
                            ),
                            'value'      => $value['message']
                        );
                        $fields2['validator_remove_cur_' . $i] = array(
                            'type'       => 'checkbox',
                            'value'      => array('Yes' => $this->i18n->__('Remove') . '?')
                        );
                        $i++;
                    }
                }
            }
        }

        // Start creating initial fields
        $fields1 = array(
            'type' => array(
                'type'       => 'select',
                'label'      => $this->i18n->__('Field Type'),
                'required'   => true,
                'value'      => array(
                    'text'             => 'text',
                    'text-history'     => 'text (history)',
                    'textarea'         => 'textarea',
                    'textarea-history' => 'textarea (history)',
                    'select'           => 'select',
                    'checkbox'         => 'checkbox',
                    'radio'            => 'radio',
                    'file'             => 'file',
                    'hidden'           => 'hidden'
                ),
                'attributes' => array(
                    'style'    => 'width: 200px;',
                    'onchange' => 'phire.toggleEditor(this);'
                )
            ),
            'editor' => array(
                'type'       => 'select',
                'value'      => $editors,
                'marked'     => 0,
                'attributes' => array(
                    'style' => 'display: ' . $editorDisplay
                )
            ),
            'name' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Field Name'),
                'required'   => true,
                'attributes' => array('size' => 64)
            ),
            'label' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Field Label'),
                'attributes' => array('size' => 64)
            ),
            'values' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Field Values') . ' <span style="font-size: 0.9em; font-weight: normal;">(' . $this->i18n->__('Pipe delimited') . ')</span>',
                'attributes' => array('size' => 64)
            ),
            'default_values' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Default Field Values') . ' <span style="font-size: 0.9em; font-weight: normal;">(' . $this->i18n->__('Pipe delimited') . ')</span>',
                'attributes' => array('size' => 64)
            ),
            'attributes' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Field Attributes'),
                'attributes' => array('size' => 64)
            ),
            'validator_new_1' => array(
                'type'       => 'select',
                'label'      => '<a href="#" onclick="phire.addValidator(); return false;">[+]</a> ' . $this->i18n->__('Field Validators') . '<br /><span style="font-size: 0.9em;">(' . $this->i18n->__('Type / Value / Message') . ')</span>',
                'value'      => $this->validators,
                'attributes' => array(
                    'style' => 'display: block; padding: 4px 4px 5px 4px; margin: 0 0 4px 0; height: 28px;'
                )
            ),
            'validator_value_new_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 10,
                    'style' => 'display: block; padding: 4px 4px 5px 4px; margin: 0 0 4px 0; height: 17px;'
                )
            ),
            'validator_message_new_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 30,
                    'style' => 'display: block; padding: 4px 4px 5px 4px; margin: 0 0 4px 0; height: 17px;'
                )
            )
        );

        if ($id != 0) {
            $fields1['name']['attributes']['onkeyup'] = "phire.updateTitle('#field-title', this);";
        }
        // Create next set of fields
        $fields3 = array();

        $models = Model\Field::getModels($config);

        asort($models);

        $fields3['model_new_1'] = array(
            'type'       => 'select',
            'label'      => '<a href="#" onclick="phire.addModel(); return false;">[+]</a> ' . $this->i18n->__('Model &amp; Type'),
            'value'      => $models,
            'attributes' => array(
                'style'    => 'display: block; margin: 0 0 4px 0;',
                'onchange' => 'phire.changeModelTypes(this);'
            )
        );
        $fields3['type_id_new_1'] = array(
            'type'       => 'select',
            'value'      => \Phire\Project::getModelTypes($models),
            'attributes' => array(
                'style' => 'display: block; width: 200px; margin: 0 0 4px 0;'
            )
        );

        if ($id != 0) {
            $field = Table\Fields::findById($id);
            $fieldToModels = (null !== $field->models) ? unserialize($field->models) : array();
            if (isset($fieldToModels[0])) {
                $i = 1;
                foreach ($fieldToModels as $f2m) {
                    $fields3['model_cur_' . $i] = array(
                        'type'       => 'select',
                        'label'      => '&nbsp;',
                        'value'      => $models,
                        'marked'     => $f2m['model'],
                        'attributes' => array(
                            'style'    => 'display: block; margin: 0 0 4px 0;',
                            'onchange' => 'phire.changeModelTypes(this);'
                        )
                    );
                    $fields3['type_id_cur_' . $i] = array(
                        'type'       => 'select',
                        'value'      => \Phire\Project::getModelTypes(str_replace('\\', '_', $f2m['model'])),
                        'marked'     => $f2m['type_id'],
                        'attributes' => array(
                            'style'  => 'display: block; width: 200px; margin: 0 0 4px 0;'
                        )
                    );
                    $fields3['rm_model_cur_' . $i] = array(
                        'type'       => 'checkbox',
                        'value'      => array(
                            $field->id . '_' . $f2m['model'] . '_' . $f2m['type_id'] => 'Remove?'
                        ),
                    );
                    $i++;
                }
            }
        }

        $fields4 = array();

        $fields4['submit'] = array(
            'type'  => 'submit',
            'value' => $this->i18n->__('SAVE'),
            'attributes' => array(
                'class' => 'save-btn'
            )
        );
        $fields4['update'] = array(
            'type'       => 'button',
            'value'      => $this->i18n->__('UPDATE'),
            'attributes' => array(
                'onclick' => "return phire.updateForm('#field-form', true);",
                'class' => 'update-btn'
            )
        );
        $fields4['order'] = array(
            'type'       => 'text',
            'label'      => $this->i18n->__('Order'),
            'value'      => 0,
            'attributes' => array('size' => 3)
        );
        $fields4['group_id'] = array(
            'type'   => 'select',
            'label'  => $this->i18n->__('Field Group'),
            'value'  => $groups,
            'attributes' => array(
                'style' => 'display: block; width: 200px;'
            )
        );
        $fields4['encryption'] = array(
            'type'       => 'select',
            'label'  => $this->i18n->__('Encryption'),
            'value' => array(
                '0' => $this->i18n->__('None'),
                '1' => 'MD5',
                '2' => 'SHA1',
                '3' => 'Crypt',
                '4' => 'Bcrypt',
                '5' => 'Mcrypt (2-Way)',
                '6' => 'Crypt_MD5',
                '7' => 'Crypt_SHA256',
                '8' => 'Crypt_SHA512',
            ),
            'marked'     => 0,
            'attributes' => array(
                'style' => 'display: block; width: 200px;'
            )
        );
        $fields4['required'] = array(
            'type'   => 'select',
            'label'  => $this->i18n->__('Required'),
            'value'  => array(
                '0' => $this->i18n->__('No'),
                '1' => $this->i18n->__('Yes')
            ),
            'marked' => 0
        );

        $fields4['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields4['update_value'] = array(
            'type'  => 'hidden',
            'value' => 0
        );

        return array($fields4, $fields1, $fields2, $fields3);
    }

}
