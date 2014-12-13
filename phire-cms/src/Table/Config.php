<?php

namespace Phire\Table;

use Pop\Db\Record;

class Config extends Record
{

    /**
     * Table prefix
     * @var string
     */
    protected static $prefix = DB_PREFIX;

    /**
     * Primary keys
     * @var array
     */
    protected $primaryKeys = ['setting'];

    /**
     * Get config values
     */
    public static function getConfig()
    {
        $config = static::findAll();
        $values = [];

        foreach ($config->rows() as $row) {
            $values[$row['setting']] = $row['value'];
        }

        return $values;
    }
}