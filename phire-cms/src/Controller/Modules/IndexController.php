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
namespace Phire\Controller\Modules;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Paginator\Form as Paginator;

/**
 * Modules controller class
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
        $module = new Model\Module();

        if ($module->hasPages($this->application->config()['pagination'])) {
            $limit = $this->application->config()['pagination'];
            $pages = new Paginator($module->getCount(), $limit);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('modules/index.phtml');
        $this->view->title       = 'Modules';
        $this->view->pages       = $pages;
        $this->view->queryString = $this->getQueryString('sort');
        $this->view->modules     = $module->getAll($limit, $this->request->getQuery('page'), $this->request->getQuery('sort'));
        $this->send();
    }

}
