<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Controller\Config;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Paginator\Form as Paginator;

/**
 * Config controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $config = new Model\Config();
        $config->getAll();

        $this->prepareView('config/index.phtml');
        $this->view->title        = 'Config';
        $this->view->installed_on = $config->installed;
        $this->view->updated_on   = $config->updated;
        $this->view->dbVersion    = $this->services['database']->getVersion();
        $this->view->database     = (strtolower($this->application->config()['database']['adapter']) == 'pdo') ?
            $this->application->config()['database']['type'] . ' (pdo)' :
            $this->view->database = $this->application->config()['database']['adapter'];

        $this->send();
    }

}
