<?php

namespace Phire\Table;

use Pop\Db\Record;

class Modules extends Record
{

    /**
     * Table name
     * @var string
     */
    protected static $table = 'modules';

    /**
     * Table prefix
     * @var string
     */
    protected static $prefix = DB_PREFIX;

    /**
     * Primary keys
     * @var array
     */
    protected $primaryKeys = ['id'];

}