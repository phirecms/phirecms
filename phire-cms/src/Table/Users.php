<?php

namespace Phire\Table;

use Pop\Db\Record;

class Users extends Record
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
    protected $primaryKeys = ['id'];

}