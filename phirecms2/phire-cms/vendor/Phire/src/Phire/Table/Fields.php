<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Fields extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'fields';

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
     * Static method to get a field group from a field ID
     *
     * @param int    $fid
     * @return array
     */
    public static function getFieldGroup($fid)
    {
        $groupAry = array(
            'group_id' => null,
            'fields'   => array(),
            'order'    => 0,
            'dynamic'  => false
        );

        $field = static::findById($fid);
        if (isset($field->id)) {
            $group = FieldGroups::findById($field->group_id);
            if (isset($group->id)) {
                $fields = static::findAll(null, array('group_id' => $group->id));

                if (isset($fields->rows[0])) {
                    foreach ($fields->rows as $fld) {
                        $groupAry['group_id'] = $group->id;
                        $groupAry['fields'][] = $fld->id;
                        $groupAry['order']    = $group->order;
                        $groupAry['dynamic']  = $group->dynamic;
                    }
                }
            }
        }

        return $groupAry;
    }

    /**
     * Static method to delete by model
     *
     * @param  string $model
     * @return void
     */
    public static function deleteByModel($model)
    {
        $fields = static::findAll();

        foreach ($fields->rows as $field) {
            if (null !== $field->models) {
                $models = unserialize($fields->models);
                foreach ($models as $k => $m) {
                    if ($m['model'] == $model) {
                        unset($models[$k]);
                    }
                }
                $fld = static::findById($field->id);
                if (isset($fld->id)) {
                    $fld = serialize($models);
                    $fld->save();
                }
            }
        }
    }

    /**
     * Static method to delete by type
     *
     * @param  int $id
     * @return void
     */
    public static function deleteByType($id)
    {
        $fields = static::findAll();

        foreach ($fields->rows as $field) {
            if (null !== $field->models) {
                $models = unserialize($fields->models);
                foreach ($models as $k => $m) {
                    if ($m['type_id'] == $id) {
                        unset($models[$k]);
                        $fld = static::findById($field->id);
                        if (isset($fld->id)) {
                            $fld->models = serialize($models);
                            $fld->update();
                        }
                    }
                }
            }
        }
    }

}

