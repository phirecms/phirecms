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
namespace Phire\Controller\Install;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;

/**
 * Install controller class
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
        $install = new Model\Install();
        $fields  = $this->application->config()['forms']['Phire\Form\Install'];

        $fields[0]['db_adapter']['values'] = $install->getDbAdapters();

        $this->prepareView('install/index.phtml');
        $this->view->title = 'Installation';
        $this->view->form  = Form\Install::createFromFieldsetConfig($fields);
        $this->send();
    }

}
