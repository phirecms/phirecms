<?php

namespace Phire\Table;

use Pop\Db\Record;

class UserRoles extends Record
{

    /**
     * Table name
     * @var string
     */
    protected static $table = 'user_roles';

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