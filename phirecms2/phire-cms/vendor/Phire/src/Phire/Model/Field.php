<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\File\Dir;
use Phire\Table;

class Field extends \Phire\Model\AbstractModel
{

    /**
     * Static method to get field definitions by model and
     * return them for consumption by a Pop\Form\Form object
     *
     * @param string $model
     * @param int    $tid
     * @param int    $mid
     * @return array
     */
    public static function getByModel($model, $tid = 0, $mid = 0)
    {
        $fieldsAry = array();
        $curFields = array();
        $groups = array();
        $dynamic = false;
        $hasFile = false;
        $i18n = Table\Config::getI18n();

        // Get fields
        $fields = array();
        $flds = Table\Fields::findAll('order ASC');
        foreach ($flds->rows as $f) {
            $models = (null !== $f->models) ? unserialize($f->models) : array();
            foreach ($models as $m) {
                if (($m['model'] == $model) && (($m['type_id'] == $tid) || ($m['type_id'] == 0))) {
                    $fields[] = $f;
                }
            }
        }

        // If fields exist
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                // Get field group, if applicable
                $groupAryResults = Table\Fields::getFieldGroup($field->id);
                $groupAry = $groupAryResults['fields'];
                $isDynamic = $groupAryResults['dynamic'];
                if ($isDynamic) {
                    $dynamic = true;
                }
                if ((count($groupAry) > 0) && (!in_array($groupAry, $groups))) {
                    $groups[$groupAryResults['group_id']] = $groupAry;
                }
                $rmFile = null;
                $fld = array(
                    'type' => ((strpos($field->type, '-') !== false) ?
                        substr($field->type, 0, strpos($field->type, '-')) : $field->type)
                );

                // Get field label
                if ($field->label != '') {
                    if (isset($groupAry[0]) && ($groupAry[0] == $field->id) && ($isDynamic)) {
                        $fld['label'] = '<a href="#" onclick="phire.addFields([' . implode(', ', $groupAry) . ']); return false;">[+]</a> ' . $field->label;
                    } else {
                        $fld['label'] = $field->label;
                    }
                }

                $fld['name']     = $field->name;
                $fld['required'] = (bool)$field->required;

                // Get field values and default values
                if (($field->type == 'select') || ($field->type == 'checkbox') || ($field->type == 'radio')) {
                    if ($field->values != '') {
                        // Get fields values of a multiple value field
                        if (strpos($field->values, '|') !== false) {
                            $vals = explode('|', $field->values);
                            $valAry = array();
                            foreach ($vals as $v) {
                                // If the values are a name/value pair
                                if (strpos($v, ',') !== false) {
                                    $vAry = explode(',', $v);
                                    if (count($vAry) >= 2) {
                                        // If the values are to be pulled from a database table
                                        if (strpos($vAry[0], 'Table') !== false) {
                                            $class = $vAry[0];
                                            $order = $vAry[1] . (isset($vAry[2]) ? ', ' . $vAry[2] : null);
                                            $order .= ' ' . ((isset($vAry[3])) ? $vAry[3] : 'ASC');
                                            $id = $vAry[1];
                                            $name = (isset($vAry[2]) ? $vAry[2] : $vAry[1]);
                                            $valRows = $class::findAll($order);
                                            if (isset($valRows->rows[0])) {
                                                foreach ($valRows->rows as $vRow) {
                                                    $valAry[$vRow->{$id}] = $vRow->{$name};
                                                }
                                            }
                                        // Else, if the value is a simple name/value pair
                                        } else {
                                            $valAry[$vAry[0]] = $vAry[1];
                                        }
                                    }
                                } else {
                                    $valAry[$v] = $v;
                                }
                            }
                            $fld['value'] = $valAry;
                        // If the values are to be pulled from a database table
                        } else if (strpos($field->values, 'Table') !== false) {
                            $valAry = array();
                            if (strpos($field->values, ',') !== false) {
                                $vAry = explode(',', $field->values);
                                $class = $vAry[0];
                                $order = $vAry[1] . (isset($vAry[2]) ? ', ' . $vAry[2] : null);
                                $order .= ' ' . ((isset($vAry[3])) ? $vAry[3] : 'ASC');
                                $id = $vAry[1];
                                $name = (isset($vAry[2]) ? $vAry[2] : $vAry[1]);
                            } else {
                                $class = $field->values;
                                $order = null;
                                $id = 'id';
                                $name = 'id';
                            }
                            $valRows = $class::findAll($order);
                            if (isset($valRows->rows[0])) {
                                foreach ($valRows->rows as $vRow) {
                                    $valAry[$vRow->{$id}] = $vRow->{$name};
                                }
                            }
                            $fld['value'] = $valAry;
                        // Else, if the value is Select constant
                        } else if (strpos($field->values, 'Select::') !== false) {
                            $fld['value'] = str_replace('Select::', '', $field->values);
                        // Else, the value is a simple value
                        } else {
                            $aryValues = array();
                            if (strpos($field->values, ',') !== false) {
                                $vls = explode(',', $field->values);
                                $aryValues[$vls[0]] = $vls[1];
                            } else {
                                $aryValues[$field->values] = $field->values;
                            }
                            $fld['value'] = $aryValues;
                        }
                    }
                    // Set default values
                    if ($field->default_values != '') {
                        $fld['marked'] = (strpos($field->default_values, '|') !== false) ? explode('|', $field->default_values) : $field->default_values;
                    }
                // If field is a file field
                } else if (($field->type == 'file') && (count($groupAry) == 0)) {
                    $dynamic = true;
                    $hasFile = true;
                    if ($mid != 0) {
                        $fileValue = Table\FieldValues::findById(array($field->id, $mid));
                        if (isset($fileValue->field_id)) {
                            $fileName = json_decode($fileValue->value, true);
                            $fileInfo = \Phire\Model\Media::getFileIcon($fileName);
                            $fld['label'] .= '<br /><a href="' .
                                BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank"><img style="padding-top: 3px;" src="' .
                                BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank">' .
                                $fileName . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span><br /><em style="font-size: 0.9em; font-weight:normal;">' . $i18n->__('Replace?') . '</em>';

                            $fld['required'] = false;

                            $rmFile = array(
                                'rm_file_' . $field->id => array(
                                    'type'=> 'checkbox',
                                    'value' => array($fileName => $i18n->__('Remove') . '?')
                                )
                            );
                        }
                    }
                // Else, if the field is a normal field
                } else {
                    if ($field->default_values != '') {
                        $fld['value'] = $field->default_values;
                    }
                }

                // Get field attributes
                if ($field->attributes != '') {
                    $attAry = array();
                    $attributes = explode('" ', $field->attributes);
                    foreach ($attributes as $attrib) {
                        $att = explode('=', $attrib);
                        $attAry[$att[0]] = str_replace('"', '', $att[1]);
                    }
                    $fld['attributes'] = $attAry;
                }

                // Get field validators
                if ($field->validators != '') {
                    $valAry = array();
                    $validators = unserialize($field->validators);
                    foreach ($validators as $key => $value) {
                        $valClass = '\Pop\Validator\\' . $key;
                        if ($value['value'] != '') {
                            $v = new $valClass($value['value']);
                        } else {
                            $v = new $valClass();
                        }
                        if ($value['message'] != '') {
                            $v->setMessage($value['message']);
                        }
                        $valAry[] = $v;
                    }
                    $fld['validators'] = $valAry;
                }

                // Detect any dynamic field group values
                $values = Table\FieldValues::findAll(null, array('field_id' => $field->id));
                if (isset($values->rows[0])) {
                    foreach ($values->rows as $value) {
                        $val = json_decode($value->value);
                        if ((count($groupAry) > 0) && ($value->model_id == $mid)) {
                            if (is_array($val)) {
                                foreach ($val as $k => $v) {
                                    $curFld = $fld;
                                    if (($field->type == 'select') || ($field->type == 'checkbox') || ($field->type == 'radio')) {
                                        $curFld['marked'] = $v;
                                    } else {
                                        $curFld['value'] = $v;
                                    }
                                    if (isset($curFld['label']) && ($dynamic)) {
                                        $curFld['label'] = '&nbsp;';
                                    }
                                    if (!isset($curFields[$field->id])) {
                                        $curFields[$field->id] = array('field_' . $field->id . '_cur_' . ($k + 1) => $curFld);
                                    } else {
                                        $curFields[$field->id]['field_' . $field->id . '_cur_' . ($k + 1)] = $curFld;
                                    }
                                }
                            } else {
                                $curFld = $fld;
                                if (($field->type == 'select') || ($field->type == 'checkbox') || ($field->type == 'radio')) {
                                    $curFld['marked'] = $val;
                                } else {
                                    $curFld['value'] = $val;
                                }
                                if (isset($curFld['label']) && ($dynamic)) {
                                    $curFld['label'] = '&nbsp;';
                                }
                                if (!isset($curFields[$field->id])) {
                                    $curFields[$field->id] = array('field_' . $field->id => $curFld);
                                } else {
                                    $curFields[$field->id]['field_' . $field->id] = $curFld;
                                }
                            }
                        }
                    }
                }

                // If field is assigned to a dynamic field group, set field name accordingly
                if ((count($groupAry) > 0) && ($isDynamic)) {
                    $fieldName = 'field_' . $field->id . '_new_1';
                } else {
                    $fieldName = 'field_' . $field->id;
                }

                // Add field to the field array
                $fieldsAry[$fieldName] = $fld;

                // If in the system back end, and the field is a textarea, add history select field
                if (($mid != 0) &&
                    (strpos($field->type, '-history') !== false) &&
                    (count($groupAry) == 0) && (strpos($_SERVER['REQUEST_URI'], APP_URI) !== false)) {
                    $fv = Table\FieldValues::findById(array($field->id, $mid));
                    if (isset($fv->field_id) && (null !== $fv->history)) {
                        $history = array(0 => '(' . $i18n->__('Current') . ')');
                        $historyAry = json_decode($fv->history, true);
                        krsort($historyAry);
                        foreach ($historyAry as $time => $fieldValue) {
                            $history[$time] = date('M j, Y H:i:s', $time);
                        }

                        $fieldsAry['history_' . $mid . '_' . $field->id] = array(
                            'type'       => 'select',
                            'label'      => $i18n->__('Select Revision'),
                            'value'      => $history,
                            'marked'     => 0,
                            'attributes' => array(
                                'onchange' => "phire.changeHistory(this, '" . BASE_PATH . APP_URI . "');",
                                'style'    => 'width: 160px;'
                            )
                        );
                    }
                }

                if (strpos($field->type, 'textarea') !== false) {
                    if ((null !== $field->editor) && ($field->editor != 'source')) {
                        $fieldsAry[$fieldName]['label'] .= ' <span style="float: right; margin-right: 4%; font-weight: normal;">[ <a href="#" class="editor-link" id="editor-' . $field->id . '" data-editor="' . $field->editor . '" data-editor-status="on" onclick="phire.changeEditor(this); return false;">' . $i18n->__('Source') . '</a> ]</span>';
                    }
                }

                // Add a remove field
                if (null !== $rmFile) {
                    foreach ($rmFile as $rmKey => $rmValue) {
                        $fieldsAry[$rmKey] = $rmValue;
                    }
                }

                if (isset($group) && (count($group) > 0)) {
                    if (isset($group[count($group) - 1]) && ($field->id == $group[count($group) - 1])) {
                        $fieldsAry[implode('_', $group)] = null;
                        $group = array();
                    }
                }
            }
        }

        // Add fields from dynamic field group in the correct order
        $realCurFields = array();
        $groupRmAry = array();
        if (count($curFields) > 0) {
            $fieldCount = count($curFields);
            $keys = array_keys($curFields);
            $valueCounts = array();
            foreach ($groups as $key => $value) {
                foreach ($curFields as $k => $v) {
                    if (in_array($k, $value)) {
                        $valueCounts[$key] = count($v);
                    }
                }
            }

            foreach ($valueCounts as $gKey => $valueCount) {
                for ($i = 0; $i < $valueCount; $i++) {
                    $fileName = null;
                    $gDynamic = false;
                    for ($j = 0; $j < $fieldCount; $j++) {
                        if (in_array($keys[$j], $groups[$gKey])) {
                            if (isset($curFields[$keys[$j]]['field_' . $keys[$j] . '_cur_' . ($i + 1)])) {
                                $gDynamic = true;
                                $f = $curFields[$keys[$j]]['field_' . $keys[$j] . '_cur_' . ($i + 1)];
                                if ($f['type'] == 'file') {
                                    $hasFile = true;
                                    $dynamic = true;
                                    $fileName = $f['value'];
                                    // Calculate file icon, set label
                                    if (!empty($fileName)) {
                                        $fileInfo = \Phire\Model\Media::getFileIcon($fileName);
                                        $f['label'] = '<br /><a href="' .
                                            BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank"><img style="padding-top: 3px;" src="' .
                                            BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank">' .
                                            $fileName . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span><br /><em style="font-size: 0.9em; font-weight:normal;">' . $i18n->__('Replace?') .'</em>';
                                    } else {
                                        $f['label'] = $i18n->__('Replace?');
                                    }
                                    $fld['required'] = false;
                                }
                                $realCurFields['field_' . $keys[$j] . '_cur_' . ($i + 1)] = $f;
                            } else if (isset($curFields[$keys[$j]]['field_' . $keys[$j]])) {
                                $gDynamic = false;
                                $f = $curFields[$keys[$j]]['field_' . $keys[$j]];
                                if ($f['type'] == 'file') {
                                    $hasFile = true;
                                    $dynamic = true;
                                    $fileName = $f['value'];
                                    // Calculate file icon, set label
                                    if (!empty($fileName)) {
                                        $fileInfo = \Phire\Model\Media::getFileIcon($fileName);
                                        $f['label'] = '<br /><a href="' .
                                            BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank"><img style="padding-top: 3px;" src="' .
                                            BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank">' .
                                            $fileName . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span><br /><em style="font-size: 0.9em; font-weight:normal;">' . $i18n->__('Replace?') . '</em>';
                                    } else {
                                        $f['label'] = $i18n->__('Replace?');
                                    }
                                    $fld['required'] = false;
                                }
                                $fieldsAry['field_' . $keys[$j]] = $f;
                            }
                        }
                    }

                    // Add a remove field
                    if ($gDynamic) {
                        $fieldId = implode('_', $groups[$gKey]) . '_' . ($i + 1);
                        $realCurFields['rm_fields_' . $fieldId] = array(
                            'type'  => 'checkbox',
                            'value' => array($fieldId => $i18n->__('Remove') . '?')
                        );
                    } else {
                        $fieldId = implode('_', $groups[$gKey]);
                        $groupRmAry[$key] = array(
                            'type'  => 'checkbox',
                            'value' => array($fieldId => $i18n->__('Remove') . '?')
                        );
                    }
                }
            }
        }

        // Merge new fields and current fields together in the right order.
        $realFieldsAry = array(
            'dynamic' => $dynamic,
            'hasFile' => $hasFile,
            '0'       => array()
        );

        if (count($groups) > 0) {
            foreach ($groups as $id => $fields) {
                $realFieldsAry[$id] = array();
            }
        }

        $cnt = 0;
        foreach ($fieldsAry as $key => $value) {
            $id = substr($key, (strpos($key, '_') + 1));
            if (strpos($id, '_') !== false) {
                $id = substr($id, 0, strpos($id, '_'));
            }
            $curGroupId = 0;
            foreach ($groups as $gId => $gFields) {
                if (in_array($id, $gFields)) {
                    $curGroupId = $gId;
                }
            }

            if (strpos($key, 'new_') !== false) {
                $cnt = 0;
                $curGroup = null;
                foreach ($groups as $group) {
                    if (in_array($id, $group)) {
                        $curGroup = $group;
                    }
                }

                $realFieldsAry[$curGroupId][$key] = $value;

                if ((null !== $curGroup) && ($id == $curGroup[count($curGroup) - 1])) {
                    foreach ($realCurFields as $k => $v) {
                        if (strpos($k, 'rm_field') === false) {
                            $i = substr($k, (strpos($k, '_') + 1));
                            $i = substr($i, 0, strpos($i, '_'));
                            if (in_array($i, $curGroup)) {
                                $realFieldsAry[$curGroupId][$k] = $v;
                            }
                        } else {
                            $i = substr($k, (strpos($k, 'rm_fields_') + 10));
                            $i = substr($i, 0, strrpos($i, '_'));
                            $grp = explode('_', $i);
                            if ($grp == $curGroup) {
                                $realFieldsAry[$curGroupId][$k] = $v;
                            }
                        }
                    }
                }
            } else {
                $cnt++;
                $realFieldsAry[$curGroupId][$key] = $value;
                if (isset($groupRmAry[$curGroupId]) && ($cnt == count($groups[$curGroupId]))) {
                    $realFieldsAry[$curGroupId]['rm_fields_' . implode('_', $groups[$curGroupId])] = $groupRmAry[$curGroupId];
                }
            }
        }

        return $realFieldsAry;
    }

    /**
     * Get available model objects
     *
     * @param  \Pop\Config $config
     * @return array
     */
    public static function getModels($config = null)
    {
        $models = array('0' => '----');
        $exclude = array();
        $override = null;

        // Get any exclude or override config values
        if (null !== $config) {
            $configAry = $config->asArray();
            if (isset($configAry['exclude_models'])) {
                $exclude = $configAry['exclude_models'];
            }
            if (isset($configAry['override'])) {
                $override = $configAry['override'];
            }
        }

        // If override, set overridden models
        if (null !== $override) {
            foreach ($override as $model) {
                $models[$model] = $model;
            }
            // Else, get all modules from the system and module directories
        } else {
            $systemDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/vendor'), true);
            $sysModuleDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/module'), true);
            $moduleDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules'), true);
            $dirs = array_merge($systemDirectory->getFiles(), $sysModuleDirectory->getFiles(), $moduleDirectory->getFiles());
            sort($dirs);

            // Dir clean up
            foreach ($dirs as $key => $dir) {
                unset($dirs[$key]);
                if (!((strpos($dir, 'PopPHPFramework') !== false) || (strpos($dir, 'config') !== false) || (strpos($dir, 'index.html') !== false))) {
                    $k = $dir;
                    if (substr($dir, -1) == DIRECTORY_SEPARATOR) {
                        $k = substr($k, 0, -1);
                    }
                    $k = substr($k, (strrpos($k, DIRECTORY_SEPARATOR) + 1));
                    $dirs[$k] = $dir;
                }
            }

            // Loop through each directory, looking for model class files
            foreach ($dirs as $mod => $dir) {
                if (file_exists($dir . 'src/' . $mod . '/Model')) {
                    $d = new Dir($dir . 'src/' . $mod . '/Model');
                    $dFiles = $d->getFiles();
                    sort($dFiles);
                    foreach ($dFiles as $m) {
                        if ((substr($m, 0, 8) !== 'Abstract')) {
                            $model = str_replace('.php', '', $mod . '\Model\\' . $m);
                            $wildcardModel = '*' . substr($model, strpos($model, '\\'));
                            if (!in_array($model, $exclude) && !in_array($wildcardModel, $exclude) && (strpos($model, 'index.html') === false)) {
                                $models[$model] = ((strpos($model, '\\') !== false) ?
                                    substr($model, (strrpos($model, '\\') + 1)) : $model);
                            }
                        }
                    }
                }
            }
        }

        return $models;
    }

    /**
     * Get all fields method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $fields = Table\Fields::findAll($order['field'] . ' ' . $order['order'], null, $order['limit'], $order['offset']);

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\Structure\FieldsController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_fields[]" id="remove_fields[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_fields" />';
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

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\Structure\FieldsController', 'edit')) {
            $edit = '<a class="edit-link" title="' . $this->i18n->__('Edit') . '" href="' . BASE_PATH . APP_URI . '/structure/fields/edit/[{id}]">Edit</a>';
        } else {
            $edit = null;
        }

        $options = array(
            'form' => array(
                'id'      => 'field-remove-form',
                'action'  => BASE_PATH . APP_URI . '/structure/fields/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=id">#</a>',
                    'edit'    => '<span style="display: block; margin: 0 auto; width: 100%; text-align: center;">' . $this->i18n->__('Edit') . '</span>',
                    'type'    => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=type">' . $this->i18n->__('Type') . '</a>',
                    'name'    => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=name">' . $this->i18n->__('Name') . '</a>',
                    'order'   => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=order">' . $this->i18n->__('Order') . '</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'separator' => '',
            'exclude'   => array(
                'group_id', 'values', 'default_values', 'attributes', 'validators', 'encryption', 'editor', 'models'
            ),
            'indent' => '        '
        );

        $fieldsAry = array();
        foreach ($fields->rows as $field) {
            $fAry = array(
                'id' => $field->id,
            );

            $fAry['type'] = $field->type;
            $fAry['name'] = $field->name;
            $fAry['label'] = $field->label;
            $fAry['required'] = ($field->required) ? $this->i18n->__('Yes') : $this->i18n->__('No');
            $fAry['order'] = $field->order;

            if (null !== $edit) {
                $fAry['edit'] = str_replace('[{id}]', $field->id, $edit);
            }

            $fieldsAry[] = $fAry;
        }

        if (isset($fieldsAry[0])) {
            $this->data['table'] = Html::encode($fieldsAry, $options, $this->config->pagination_limit, $this->config->pagination_range, Table\Fields::getCount());
        }
    }

    /**
     * Get field by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $field = Table\Fields::findById($id);
        if (isset($field->id)) {
            $fieldValues = $field->getValues();
            $fieldValues['attributes'] = htmlentities($fieldValues['attributes'], ENT_QUOTES, 'UTF-8');
            $this->data = array_merge($this->data, $fieldValues);
        }
    }

    /**
     * Save field
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $fields = $form->getFields();

        $validators = array();
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'validator_new_') !== false) && ($value != '') && ($value != '----')) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $validators[$value] = array(
                    'value'   => $_POST['validator_value_new_' . $id],
                    'message' => $_POST['validator_message_new_' . $id]
                );
            }
        }

        $field = new Table\Fields(array(
            'group_id'       => (((int)$fields['group_id'] > 0) ? (int)$fields['group_id'] : null),
            'type'           => $fields['type'],
            'name'           => $fields['name'],
            'label'          => $fields['label'],
            'values'         => $fields['values'],
            'default_values' => $fields['default_values'],
            'attributes'     => html_entity_decode($fields['attributes'], ENT_QUOTES, 'UTF-8'),
            'validators'     => ((count($validators) > 0) ? serialize($validators) : null),
            'encryption'     => (int)$fields['encryption'],
            'order'          => (int)$fields['order'],
            'required'       => (int)$fields['required'],
            'editor'         => (($fields['editor'] != '0') ? $fields['editor'] : null)
        ));

        $field->save();
        $this->data['id'] = $field->id;

        $models = array();

        // Save field to model relationships
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'model_new_') !== false) && ($value != '0')) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $models[] = array(
                    'model'    => $value,
                    'type_id'  => (int)$_POST['type_id_new_' . $id]
                );
            }
        }

        $field->models = serialize($models);
        $field->update();
    }

    /**
     * Update field
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $fields = $form->getFields();

        $curValidators = array();
        $newValidators = array();
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'validator_new_') !== false) && ($value != '') && ($value != '----')) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $newValidators[$value] = array(
                    'value'   => $_POST['validator_value_new_' . $id],
                    'message' => $_POST['validator_message_new_' . $id]
                );
            } else if (strpos($key, 'validator_cur_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                if (!isset($_POST['validator_remove_cur_' . $id])) {
                    if (($value != '') && ($value != '----')) {
                        $curValidators[$value] = array(
                            'value'   => $_POST['validator_value_cur_' . $id],
                            'message' => $_POST['validator_message_cur_' . $id]
                        );
                    }
                }
            }
        }

        $validators = array_merge($curValidators, $newValidators);

        $field = Table\Fields::findById($fields['id']);
        $field->group_id       = (((int)$fields['group_id'] > 0) ? (int)$fields['group_id'] : null);
        $field->type           = $fields['type'];
        $field->name           = $fields['name'];
        $field->label          = $fields['label'];
        $field->values         = $fields['values'];
        $field->default_values = $fields['default_values'];
        $field->attributes     = html_entity_decode($fields['attributes'], ENT_QUOTES, 'UTF-8');
        $field->validators     = ((count($validators) > 0) ? serialize($validators) : null);
        $field->encryption     = (int)$fields['encryption'];
        $field->order          = (int)$fields['order'];
        $field->required       = (int)$fields['required'];
        $field->editor         = (($fields['editor'] != '0') ? $fields['editor'] : null);
        $field->update();
        $this->data['id'] = $field->id;

        $models = array();

        // Save field to model relationships
        foreach ($_POST as $key => $value) {
            if ((substr($key, 0, 6) == 'model_') && ($value != '0')) {
                $cur = (strpos($key, 'new_') !== false) ? 'new_' : 'cur_';
                $id = substr($key, (strrpos($key, '_') + 1));
                $models[] = array(
                    'model'    => $value,
                    'type_id'  => (int)$_POST['type_id_' . $cur . $id]
                );
            }
        }

        // Remove field to model relationships
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'rm_model_') !== false) && isset($value[0])) {
                foreach ($models as $k => $model) {
                    if (($field->id . '_' . $model['model'] . '_' . $model['type_id']) == $value[0]) {
                        unset($models[$k]);
                    }
                }
            }
        }

        $field->models = serialize($models);
        $field->update();
    }

    /**
     * Remove fields
     *
     * @param array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_fields'])) {
            foreach ($post['remove_fields'] as $id) {
                $field = Table\Fields::findById($id);
                if (isset($field->id)) {
                    $field->delete();
                }
            }
        }
    }

}

