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

use Pop\Console\Console;

/**
 * Console controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
class ConsoleController extends AbstractController
{

    /**
     * Help command
     *
     * @return void
     */
    public function help()
    {
        $command = $this->console->colorize("./phire", Console::BOLD_CYAN) . ' ' .
            $this->console->colorize("config", Console::BOLD_YELLOW) . ' ' .
            $this->console->colorize("cli", Console::BOLD_GREEN) . ' ' .
            $this->console->colorize("[<param>]", Console::BOLD_MAGENTA);
        $this->console->append($command . "\t Display CLI configuration");

        $command = $this->console->colorize("./phire", Console::BOLD_CYAN) . ' ' .
            $this->console->colorize("config", Console::BOLD_YELLOW) . ' ' .
            $this->console->colorize("http", Console::BOLD_GREEN) . ' ' .
            $this->console->colorize("[<param>]", Console::BOLD_MAGENTA);
        $this->console->append($command . "\t Display HTTP configuration");

        $this->console->append();

        $command = $this->console->colorize("./phire", Console::BOLD_CYAN) . ' ' .
            $this->console->colorize("migrate", Console::BOLD_YELLOW) . ' ' .
            $this->console->colorize("create", Console::BOLD_GREEN);
        $this->console->append($command . "\t\t Create a new database migration");

        $command = $this->console->colorize("./phire", Console::BOLD_CYAN) . ' ' .
            $this->console->colorize("migrate", Console::BOLD_YELLOW) . ' ' .
            $this->console->colorize("run", Console::BOLD_GREEN) . ' ' .
            $this->console->colorize("[<steps>]", Console::BOLD_MAGENTA);
        $this->console->append($command . "\t Run the database migration");

        $command = $this->console->colorize("./phire", Console::BOLD_CYAN) . ' ' .
            $this->console->colorize("migrate", Console::BOLD_YELLOW) . ' ' .
            $this->console->colorize("rollback", Console::BOLD_GREEN) . ' ' .
            $this->console->colorize("[<steps>]", Console::BOLD_MAGENTA);;
        $this->console->append($command . "\t Rollback the database migration");

        $command = $this->console->colorize("./phire", Console::BOLD_CYAN) . ' ' .
            $this->console->colorize("migrate", Console::BOLD_YELLOW) . ' ' .
            $this->console->colorize("clear", Console::BOLD_GREEN);
        $this->console->append($command . "\t\t Clear the database migration");

        $this->console->append();

        $command = $this->console->colorize("./phire", Console::BOLD_CYAN) . ' ' .
            $this->console->colorize("help", Console::BOLD_YELLOW);
        $this->console->append($command . "\t\t\t Show the help screen");

        $this->console->send();
    }

    /**
     * Config command
     *
     * @param  string $interface
     * @param  string $param
     * @throws \Phire\Exception
     * @return void
     */
    public function config($interface = null, $param = null)
    {
        if ((null !== $interface) && ($interface != 'http') && ($interface != 'cli')) {
            throw new \Phire\Exception('Error: That interface is not allowed. It must be \'http\' or \'cli\'.');
        }

        switch($interface) {
            case 'http':
                $config = ['http' => include __DIR__ . '/../../../config/app.http.php'];
                break;
            case 'cli':
                $config = ['cli' => $this->application->config()];
                break;
            default:
                $config = [
                    'http' => include __DIR__ . '/../../../config/app.http.php',
                    'cli'  => $this->application->config()
                ];
        }

        foreach ($config as $iface => $cfg) {
            $output  = $this->console->colorize("config", Console::BOLD_CYAN);
            $output .= $this->console->colorize(" > ", Console::BOLD_WHITE);
            $output .= $this->console->colorize($iface, Console::BOLD_YELLOW);

            if (null !== $param) {
                $output .= $this->console->colorize(" > ", Console::BOLD_WHITE);
                $output .= $this->console->colorize($param, Console::BOLD_GREEN);
            }

            $this->console->append($output);
            $this->console->append();

            if (null !== $param) {
                if (!isset($cfg[$param])) {
                    throw new \Phire\Exception('Error: That config parameter does not exist.');
                }
                $cfg = $cfg[$param];
            }

            $max = max(array_map('strlen', array_keys($cfg)));

            foreach ($cfg as $key => $value) {
                if (($param == 'api') && isset($value['url'])) {
                    $v = $value['url'];
                } else if ($param == 'resources') {
                    $v = implode(',', $value);
                } else if ($param == 'routes') {
                    $routes = array_keys($value);
                    $v = (!in_array('controller', $routes)) ?
                        PHP_EOL . "\t    " . implode(PHP_EOL . "\t    ", $routes) : '';
                } else if (is_bool($value)) {
                    $v = ($value) ? 'true' : 'false';
                } else if (is_array($value)) {
                    $v = '[Array]';
                } else if (($key == 'username') || ($key == 'password')) {
                    $v = '********';
                } else {
                    $v = $value;
                }

                $this->console->append("\t" . $key . str_repeat(" ", ($max - strlen($key))) . "\t" . $v);
            }

            $this->console->append();
        }

        $this->console->send();
    }

}