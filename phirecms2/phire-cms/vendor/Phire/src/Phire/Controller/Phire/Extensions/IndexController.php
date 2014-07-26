<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Extensions;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Controller\AbstractController;
use Phire\Model;

class IndexController extends AbstractController
{

    /**
     * Session object
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Constructor method to instantiate the default controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Project  $project
     * @param  string   $viewPath
     * @return self
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        if (null === $viewPath) {
            $cfg = $project->module('Phire')->asArray();
            $viewPath = __DIR__ . '/../../../../../view/phire/extensions';

            if (isset($cfg['view'])) {
                $class = get_class($this);
                if (is_array($cfg['view']) && isset($cfg['view'][$class])) {
                    $viewPath = $cfg['view'][$class];
                } else if (is_array($cfg['view']) && isset($cfg['view']['*'])) {
                    $viewPath = $cfg['view']['*'] . '/extensions';
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'] . '/extensions';
                }
            }
        }

        parent::__construct($request, $response, $project, $viewPath);
        $this->sess = Session::getInstance();
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('index.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'title'    => 'Extensions'
        ));

        $this->view->set('title', $this->view->i18n->__('Extensions'));

        $this->send();
    }

    /**
     * Modules method
     *
     * @return void
     */
    public function modules()
    {
        $this->prepareView('modules.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $ext = new Model\Extension(array('acl' => $this->project->getService('acl')));
        $ext->getModules($this->project);

        if (null === $this->request->getPath(1)) {
            $this->view->set('title', $this->view->i18n->__('Extensions') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Modules'));
            $this->view->merge($ext->getData());
            $this->send();
        } else if ((null !== $this->request->getPath(1)) && ($this->request->getPath(1) == 'install') && (count($ext->new) > 0)) {
            $ext->installModules();
            if (null !== $ext->error) {
                $this->view->set('title', $this->view->i18n->__('Extensions') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Modules') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Installation Error'));
                $this->view->merge($ext->getData());
                $this->send();
            } else {
                Response::redirect($this->request->getBasePath() . '/modules?saved=' . time());
            }
        } else if (($this->request->isPost()) && (null !== $this->request->getPath(1)) && ($this->request->getPath(1) == 'process')) {
            $ext->processModules($this->request->getPost());
            Response::redirect($this->request->getBasePath() . '/modules?saved=' . time());
        } else {
            Response::redirect($this->request->getBasePath() . '/modules');
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $this->prepareView('error.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', $this->view->i18n->__('404 Error') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Page Not Found'));
        $this->send();
    }

}

