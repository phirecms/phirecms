<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Controller\Install;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

/**
 * Install Index Controller class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.2
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
        if (($this->services->isAvailable('database')) && count($this->services['database']->getTables()) > 0) {
            $this->redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
        }

        $this->prepareView('phire/install.phtml');
        $this->view->title = 'Installation';

        $this->view->form = new Form\Install($this->application->config()['forms']['Phire\Form\Install']);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $install = new Model\Install();
                $install->installDb($this->view->form->getFields());
                $config  = $install->createConfig($this->view->form->getFields());

                if (is_writable(__DIR__ . '/../../../../config.php')) {
                    file_put_contents(__DIR__ . '/../../../../config.php', $config);
                    $this->sess->app_uri = (!empty($this->view->form->app_uri) && ($this->view->form->app_uri != '/')) ?
                        $this->view->form->app_uri : '';
                    $this->redirect(BASE_PATH . $this->sess->app_uri . '/install/user');
                } else {
                    $this->sess->config  = htmlentities($config, ENT_QUOTES, 'UTF-8');
                    $this->sess->app_uri = (!empty($this->view->form->app_uri) && ($this->view->form->app_uri != '/')) ?
                        $this->view->form->app_uri : '';
                    $this->redirect(BASE_PATH . APP_URI . '/install/config');
                }
            }
        }

        $this->send();
    }

    /**
     * Config action method
     *
     * @return void
     */
    public function config()
    {
        if (!isset($this->sess->config)) {
            $this->sess->kill();
            $this->redirect(BASE_PATH . APP_URI . '/install');
        }

        $this->prepareView('phire/install.phtml');
        $this->view->title = 'Install Configuration File';

        $this->view->form = new Form\InstallConfig(
            $this->sess->config, $this->application->config()['forms']['Phire\Form\InstallConfig']
        );

        if ($this->request->isPost()) {
            if ($this->view->form->isValid()) {
                unset($this->sess->config);
                $this->redirect(BASE_PATH . $this->sess->app_uri . '/install/user');
            }
        }

        $this->send();
    }

    /**
     * User action method
     *
     * @return void
     */
    public function user()
    {
        $this->prepareView('phire/install.phtml');
        $this->view->title = 'Install User';

        $fields = $this->application->config()['forms']['Phire\Form\Register'];
        $fields[1]['email']['required'] = true;
        $fields[2]['role_id']['value']  = 2001;

        unset($fields[1]['first_name']);
        unset($fields[1]['last_name']);
        unset($fields[1]['company']);
        unset($fields[1]['title']);
        unset($fields[1]['phone']);

        $this->view->form = new Form\Register(false, false, $fields);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $fields = $this->view->form->getFields();
                $fields['active']   = 1;
                $fields['verified'] = 1;

                $user = new Model\User();
                $user->save($fields);

                $install = new Model\Install();
                $install->sendConfirmation($user);

                $module = new Model\Module();
                if ($module->detectNew()) {
                    $module->install($this->services);
                }

                $dbType = (DB_INTERFACE == 'pdo') ? DB_TYPE : DB_INTERFACE;
                if (file_exists(__DIR__ . '/../../../data/install.' . strtolower($dbType) . '.sql')) {
                    $install->installProfile(__DIR__ . '/../../../data/install.' . strtolower($dbType) . '.sql');
                }

                unset($this->sess->config);
                unset($this->sess->app_uri);
                $this->sess->setRequestValue('installed', true);
                $this->redirect(BASE_PATH . APP_URI . '/login');
            }
        }

        $this->send();
    }

}