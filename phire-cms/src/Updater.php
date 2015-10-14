<?php

namespace Phire;

use Phire\Table;

class Updater
{

    protected $newUpdates = [];

    protected $previousUpdates = [];

    public function __construct()
    {
        $updates = Table\Config::findById('updates')->value;
        if (!empty($updates)) {
            $this->previousUpdates = explode('|', $updates);
        }
    }

    public function run()
    {
        foreach ($this->newUpdates as $new) {
            if (!in_array($new, $this->previousUpdates)) {
                $method = 'update' . $new;
                $this->$method();
                $this->previousUpdates[] = $new;
            }
        }

        $updates = Table\Config::findById('updates');
        $updates->value = implode('|', $this->previousUpdates);
        $updates->save();

        $updated = Table\Config::findById('updated_on');
        $updated->value = date('Y-m-d H:i:s');
        $updated->save();
    }

}
