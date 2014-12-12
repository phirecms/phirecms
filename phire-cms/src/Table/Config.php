<?php

namespace Phire\Table;

use Pop\Db\Record;

class Config extends Record
{

    /**
     * Table name
     * @var string
     */
    protected static $table = 'config';

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

}