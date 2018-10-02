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
namespace Phire\Http\Api\Controller;

use Phire\Module;

/**
 * HTTP index controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
class IndexController extends AbstractController
{

    /**
     * Version action method
     *
     * @return void
     */
    public function version()
    {
        $this->send(200, ['version' => Module::VERSION]);
    }

    /**
     * Authenticate action method
     *
     * @return void
     */
    public function authenticate()
    {
        $this->send(200, ['location' => 'API Authenticate']);
    }

}