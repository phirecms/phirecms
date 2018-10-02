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
namespace Phire;

use Pop\Application;
use Pop\Db\Db;
use Pop\Db\Record;
use Pop\Http\Request;
use Pop\Http\Response;

/**
 * Phire module class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
class Module extends \Pop\Module\Module
{

    /**
     * Module version
     * @var string
     */
    const VERSION = '3.0.0-alpha';

    /**
     * Module name
     * @var string
     */
    const NAME = 'phire-cms';

    /**
     * Module name
     * @var string
     */
    protected $name = self::NAME;

    /**
     * Module version
     * @var string
     */
    protected $version = self::VERSION;

    /**
     * Register module
     *
     * @param  Application $application
     * @return Module
     */
    public function register(Application $application)
    {
        parent::register($application);

        if (defined('DB_ADAPTER') && !empty(DB_ADAPTER)) {
            $this->initDb();
        }

        if ($this->application->router()->isCli()) {
            $this->registerCli();
        } else {
            $this->registerHttp();
        }

        return $this;
    }

    /**
     * Register HTTP
     *
     * @return void
     */
    public function registerHttp()
    {
        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'request'     => new Request(),
                    'response'    => new Response()
                ]
            );
        }

        $this->application->on('app.dispatch.pre', 'Phire\Http\Event\Maintenance::check');

        if ($this->isApi()) {
            $this->application->on('app.dispatch.pre', 'Phire\Http\Api\Event\Options::check');
        }
    }

    /**
     * Register CLI
     *
     * @return void
     */
    public function registerCli()
    {
        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'console'     => new \Pop\Console\Console(120, '    ')
                ]
            );
        }

        $this->application->on('app.route.pre', 'Phire\Console\Event\Console::header', 2)
             ->on('app.dispatch.post', 'Phire\Console\Event\Console::footer', 1);
    }

    /**
     * Determine if request is a CLI request
     *
     * @return boolean
     */
    public function isCli()
    {
        return $this->application->router()->isCli();
    }

    /**
     * Determine if request is an HTTP request
     *
     * @return boolean
     */
    public function isHttp()
    {
        return $this->application->router()->isHttp();
    }

    /**
     * Determine if request is an HTTP API request
     *
     * @return boolean
     */
    public function isApi()
    {
        return ($this->application->router()->isHttp() &&
            (substr($this->application->router()->getRouteMatch()->getRouteString(), 0, strlen(APP_URI) + 4) == APP_URI . '/api'));
    }

    /**
     * Determine if request is an HTTP web request
     *
     * @return boolean
     */
    public function isWeb()
    {
        return ($this->application->router()->isHttp() &&
            (substr($this->application->router()->getRouteMatch()->getRouteString(), 0, strlen(APP_URI) + 4) != APP_URI . '/api'));
    }

    /**
     * HTTP error handler method
     *
     * @param  \Exception $exception
     * @return void
     */
    public function httpError(\Exception $exception)
    {
        $response = new Response();
        $message  = $exception->getMessage();

        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode(['error' => $message], JSON_PRETTY_PRINT) . PHP_EOL);
        $response->send(500);
    }

    /**
     * CLI error handler method
     *
     * @param  \Exception $exception
     * @return void
     */
    public function cliError(\Exception $exception)
    {
        $message = strip_tags($exception->getMessage());

        if (stripos(PHP_OS, 'win') === false) {
            $string  = "    \x1b[1;37m\x1b[41m    " . str_repeat(' ', strlen($message)) . "    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    " . $message . "    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    " . str_repeat(' ', strlen($message)) . "    \x1b[0m" . PHP_EOL . PHP_EOL;
            $string .= "    Try \x1b[1;33m./phire help\x1b[0m for help" . PHP_EOL . PHP_EOL;
        } else {
            $string = $message . PHP_EOL . PHP_EOL;
            $string .= '    Try \'./phire help\' for help' . PHP_EOL . PHP_EOL;
        }

        echo $string;
        echo PHP_EOL;

        exit(127);
    }

    /**
     * Initialize database service
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return void
     */
    protected function initDb()
    {
        $adapter = DB_ADAPTER;
        $options = [
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'host'     => DB_HOST,
            'type'     => DB_TYPE,
        ];

        $check = Db::check($adapter, $options);

        if (null !== $check) {
            throw new \Pop\Db\Adapter\Exception('Error: ' . $check);
        }

        $this->application->services()->set('database', [
            'call'   => 'Pop\Db\Db::connect',
            'params' => [
                'adapter' => $adapter,
                'options' => $options
            ]
        ]);

        if ($this->application->services()->isAvailable('database')) {
            Record::setDb($this->application->services['database']);
        }
    }

}