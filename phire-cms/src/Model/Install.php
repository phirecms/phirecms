<?php

namespace Phire\Model;

use Phire\Table;
use Pop\Db\Db;

class Install extends AbstractModel
{

    /**
     * Create the config file
     *
     * @param  array $fields
     * @return void
     */
    public function config(array $fields)
    {
        print_r($fields);
    }

}