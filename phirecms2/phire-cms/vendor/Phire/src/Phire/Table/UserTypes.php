<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class UserTypes extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'user_types';

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

}

