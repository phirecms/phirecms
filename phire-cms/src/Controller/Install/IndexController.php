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

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                    ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                    ->filterValues();

                $install->installDb($this->view->form);

                $config = $install->createConfig($this->view->form);

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

        $this->prepareView('install/config.phtml');
        $this->view->title = 'Install Configuration File';

        $fields = $this->application->config()['forms']['Phire\Form\InstallConfig'];
        $fields[0]['config']['value'] = $this->sess->config;
        $this->view->form = Form\InstallConfig::createFromFieldsetConfig($fields);

        if ($this->request->isPost()) {
            $this->view->form->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                unset($this->sess->config);
                $this->redirect(BASE_PATH . $this->sess->app_uri . '/install/user');
            }
        }

        $this->send();
    }

    /**
     * Config action method
     *
     * @return void
     */
    public function user()
    {
        $this->prepareView('install/user.phtml');
        $this->view->title = 'Install Initial User';
        $this->view->form = Form\InstallUser::createFromFieldsetConfig($this->application->config()['forms']['Phire\Form\InstallUser']);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $user = new Model\User();
                $user->save(
                    $this->view->form,
                    $this->application->config()['application_title'],
                    $this->application->services()->get('mailer')
                );

                $notify = new Model\Notification();
                $notify->sendConfirmation(
                    $user,
                    $this->application->config()['application_title'],
                    $this->application->services()->get('mailer')
                );

                $install = new Model\Install();
                $install->sendStats();

                $this->sess->setRequestValue('installed', true);
                $this->redirect(BASE_PATH . APP_URI . '/login');
            }
        }

        $this->send();
    }

}
