<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Mvc\View;

class AbstractController extends \Pop\Mvc\Controller
{

    /**
     * Site live flag
     * @var boolean
     */
    protected $live = null;

    public function isLive()
    {
        return $this->live;
    }

    /**
     * Prepare view method
     *
     * @param  string $template
     * @param  array  $data
     * @return void
     */
    public function prepareView($template = null, array $data = array())
    {
        $site   = \Phire\Table\Sites::getSite();

        if (null !== $template) {
            $template = $this->getCustomView($template, $site);
        }

        $sess   = \Pop\Web\Session::getInstance();
        $config = \Phire\Table\Config::getSystemConfig();
        $i18n   = \Phire\Table\Config::getI18n();
        $this->live = (bool)$config->live;
        $jsVars = null;

        $this->view = View::factory($template, $data);
        $this->view->set('base_path', $site->base_path)
                   ->set('content_path', CONTENT_PATH);

        // Check for an override Phire theme for the header/footer
        if (file_exists($site->document_root . $site->base_path . CONTENT_PATH . '/extensions/themes/phire/header.phtml') &&
            file_exists($site->document_root . $site->base_path . CONTENT_PATH . '/extensions/themes/phire/footer.phtml')) {
            $this->view->set('phireHeader', $site->document_root . $site->base_path . CONTENT_PATH . '/extensions/themes/phire/header.phtml')
                       ->set('phireFooter', $site->document_root . $site->base_path . CONTENT_PATH . '/extensions/themes/phire/footer.phtml');
        // Else, just use the default header/footer
        } else {
            $this->view->set('phireHeader', $site->document_root . $site->base_path . APP_PATH . '/vendor/Phire/view/phire/header.phtml')
                       ->set('phireFooter', $site->document_root . $site->base_path . APP_PATH . '/vendor/Phire/view/phire/footer.phtml');
        }

        if (isset($this->view->assets)) {
            $jsVars = '?lang=' . $config->default_language;
        }

        if (isset($sess->user)) {
            if (isset($this->sess->user->last_action)) {
                $this->sess->user->last_action = date('Y-m-d H:i:s');
            }

            // Set the timeout warning, giving a 30 second buffer to act
            if (isset($this->view->assets)) {
                if (isset($this->view->acl) && ($this->view->acl->getType()->session_expiration > 0) && ($this->view->acl->getType()->timeout_warning)) {
                    $exp = ($this->view->acl->getType()->session_expiration * 60) - 30;
                    $uri = $site->base_path . ((strtolower($this->view->acl->getType()->type) != 'user') ? '/' . strtolower($this->view->acl->getType()->type) : APP_URI);
                    $jsVars .= '&_exp=' . $exp . '&_base=' . urlencode($uri);
                }
            }

            $this->view->set('user', $sess->user)
                       ->set('role', \Phire\Table\UserRoles::getRole($sess->user->role_id))
                       ->set('globalAccess', $sess->user->global_access);

            if (isset($this->view->phireNav) && isset($this->view->acl) && ($this->view->acl->hasRole($this->view->role->getName()))) {
                $this->view->phireNav->setConfig(array(
                    'top' => array(
                        'node'  => 'ul',
                        'id'    => 'phire-nav'
                    ),
                ));
                $this->view->phireNav->setAcl($this->view->acl);
                $this->view->phireNav->setRole($this->view->role);

                $tree = $this->view->phireNav->getTree();

                // If the sub-children haven't been added yet
                if (isset($tree[0])) {
                    // And any user types to the main phire nav
                    $userTypes = \Phire\Table\UserTypes::findAll('id ASC');
                    if (isset($userTypes->rows)) {
                        foreach ($userTypes->rows as $type) {
                            $perm = 'index_' . $type->id;
                            if ($this->view->acl->isAuth('Phire\Controller\Phire\User\IndexController', 'index') &&
                                $this->view->acl->isAuth('Phire\Controller\Phire\User\IndexController', 'index_' . $type->id)) {
                                $perm = 'index';
                            }

                            $this->view->phireNav->addLeaf('Users', array(
                                'name'     => ucwords(str_replace('-', ' ', $type->type)),
                                'href'     => 'index/' . $type->id,
                                'acl' => array(
                                    'resource'   => 'Phire\Controller\Phire\User\IndexController',
                                    'permission' => $perm
                                )
                            ), 1);
                        }
                    }

                    // Set the language
                    $tree = $this->view->phireNav->getTree();
                    foreach ($tree as $key => $value) {
                        if (isset($value['name'])) {
                            $tree[$key]['name'] = $i18n->__($value['name']);
                            if (isset($value['children']) && (count($value['children']) > 0)) {
                                foreach ($value['children'] as $k => $v) {
                                    if (($v['name'] == 'Fields') && isset($tree[$key]['children'][$k]['children'][0]['name'])) {
                                        $tree[$key]['children'][$k]['children'][0]['name'] = $i18n->__($tree[$key]['children'][$k]['children'][0]['name']);
                                    }
                                    $tree[$key]['children'][$k]['name'] = $i18n->__($v['name']);

                                }
                            }
                        }
                    }

                    $this->view->phireNav->setTree($tree);
                }

                $this->view->phireNav->rebuild();
                $this->view->phireNav->nav()->setIndent('    ');
            }
        }

        if (isset($this->view->assets)) {
            $this->view->assets = str_replace('jax.3.2.0.min.js', 'jax.3.2.0.min.js' . $jsVars, $this->view->assets);
        }

        if (isset($sess->errors)) {
            $this->view->set('errors', $sess->errors);
        }

        // Set config object and system/site default data
        $this->view->set('i18n', $i18n)
                   ->set('system_title', $config->system_title)
                   ->set('system_email', $config->system_email)
                   ->set('site_title', $config->site_title)
                   ->set('base_path', $config->base_path)
                   ->set('separator', $config->separator)
                   ->set('default_language', $config->default_language)
                   ->set('datetime_format', $config->datetime_format);
    }

    /**
     * Get custom view
     *
     * @param  string       $view
     * @param  \ArrayObject $site
     * @return string
     */
    public function getCustomView($view, $site)
    {
        $viewTemplate = $this->viewPath . '/' . $view;

        if ($this->hasCustomView($view, $site)) {
            $path = substr($this->viewPath, (strpos($this->viewPath, '/view/phire') + 11));
            $viewTemplate = $site->document_root . $site->base_path . CONTENT_PATH . '/extensions/themes/phire' . $path . '/' . $view;
        }

        return $viewTemplate;
    }

    /**
     * Check if custom view exists
     *
     * @param  string $view
     * @param  \ArrayObject $site
     * @return boolean
     */
    public function hasCustomView($view, $site)
    {
        $result = false;

        if (strpos($this->viewPath, '/view/phire') !== false) {
            $path = substr($this->viewPath, (strpos($this->viewPath, '/view/phire') + 11));
            $result = (file_exists($site->document_root . $site->base_path . CONTENT_PATH . '/extensions/themes/phire' . $path . '/' . $view));
        }

        return $result;
    }

}

