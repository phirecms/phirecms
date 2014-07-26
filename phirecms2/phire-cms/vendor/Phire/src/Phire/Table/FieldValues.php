<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;
use Phire\Model;

class FieldValues extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'field_values';

    /**
     * @var   string
     */
    protected $primaryId = array('field_id', 'model_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Static method to get field groups
     *
     * @param  array $keys
     * @return array
     */
    public static function getGroups(array $keys)
    {
        $groups = array();

        foreach ($keys as $key) {
            $id = substr($key, 6);
            if (strpos($id, '_') !== false) {
                $id = substr($id, 0, strpos($id, '_'));
            }

            $groupAry = Fields::getFieldGroup($id);
            if ((count($groupAry) > 0) && (!in_array($groupAry, $groups))) {
                $groups[] = $groupAry;
            }
        }

        return $groups;
    }

}
