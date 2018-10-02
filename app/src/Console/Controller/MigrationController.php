<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Console\Controller;

use Pop\Db\Sql\Migrator;

/**
 * Migration console controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
class MigrationController extends AbstractController
{

    /**
     * Create migration command
     *
     * @return void
     */
    public function create()
    {
        $class = 'PhireMigration' . uniqid();
        Migrator::create($class, __DIR__ . '/../../../../script/migrations');
        $this->console->append("New migration class '" . $class . "' has been created.");
        $this->console->send();
    }

    /**
     * Run migration command
     *
     * @param  mixed $steps
     * @throws \Phire\Exception
     * @return void
     */
    public function run($steps = null)
    {
        $this->console->append("Running migration...");

        if (null === $steps) {
            $steps = 1;
        } else if (is_numeric($steps)) {
            $steps = (int)$steps;
        } else if ($steps != 'all') {
            throw new \Phire\Exception("Error: Invalid run step parameter '" . $steps . "'.");
        }

        $migrator = new Migrator($this->application->services['database'], __DIR__ . '/../../../../script/migrations');
        $migrator->run($steps);

        $this->console->append("Done!");
        $this->console->send();
    }

    /**
     * Rollback migration command
     *
     * @param  mixed $steps
     * @throws \Phire\Exception
     * @return void
     */
    public function rollback($steps = null)
    {
        $this->console->append("Rolling back migration...");

        if (null === $steps) {
            $steps = 1;
        } else if (is_numeric($steps)) {
            $steps = (int)$steps;
        } else if ($steps != 'all') {
            throw new \Phire\Exception("Error: Invalid rollback step parameter '" . $steps . "'.");
        }

        $migrator = new Migrator($this->application->services['database'], __DIR__ . '/../../../../script/migrations');
        $migrator->rollback($steps);

        $this->console->append("Done!");
        $this->console->send();
    }

    /**
     * Clear migration command
     *
     * @return void
     */
    public function clear()
    {
        $this->console->append("Clearing all migrations...");

        $migrator = new Migrator($this->application->services['database'], __DIR__ . '/../../../../script/migrations');
        $migrator->rollback('all');

        $files = scandir(__DIR__ . '/../../../../script/migrations');

        foreach ($files as $file) {
            if (($file != '.') && ($file != '..') && ($file != '.empty') &&
                file_exists(__DIR__ . '/../../../../script/migrations/' . $file) &&
                !is_dir(__DIR__ . '/../../../../script/migrations/' . $file)) {
                unlink(__DIR__ . '/../../../../script/migrations/' . $file);
            }
        }

        $this->console->append("Done!");
        $this->console->send();
    }

}