<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\File\File;
use Phire\Table;

class Site extends \Phire\Model\AbstractModel
{

    /**
     * Get all sites method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $sites = Table\Sites::findAll($order['field'] . ' ' . $order['order']);

        if ($this->data['acl']->isAuth('Phire\Controller\Config\SitesController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_sites[]" id="remove_sites[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_sites" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove')
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove'),
                'style' => 'display: none;'
            );
        }

        $options = array(
            'form' => array(
                'id'      => 'sites-remove-form',
                'action'  => BASE_PATH . APP_URI . '/config/sites/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'            => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=id">#</a>',
                    'edit'          => '<span style="display: block; margin: 0 auto; width: 100%; text-align: center;">' . $this->i18n->__('Edit') . '</span>',
                    'domain'        => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=domain">' . $this->i18n->__('Domain') . '</a>',
                    'document_root' => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=document_root">' . $this->i18n->__('Document Root') . '</a>',
                    'title'         => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=title">' . $this->i18n->__('Title') . '</a>',
                    'live'          => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=live">' . $this->i18n->__('Live') . '</a>',
                    'process'       => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'separator' => '',
            'exclude'   => array(
                'force_ssl'
            ),
            'indent'    => '        '
        );

        $siteAry = array();
        foreach ($sites->rows as $site) {
            if ($this->data['acl']->isAuth('Phire\Controller\Config\SitesController', 'edit')) {
                $edit = '<a class="edit-link" title="' . $this->i18n->__('Edit') . '" href="' . BASE_PATH . APP_URI . '/config/sites/edit/' . $site->id . '">Edit</a>';
            } else {
                $edit = null;
            }

            $sAry = array(
                'id'            => $site->id,
                'title'         => $site->title,
                'domain'        => $site->domain,
                'document_root' => $site->document_root,
                'base_path'     => ($site->base_path == '') ? '&nbsp;' : $site->base_path,
                'live'          => ($site->live == 1) ? $this->i18n->__('Yes') : $this->i18n->__('No')
            );

            if (null !== $edit) {
                $sAry['edit'] = $edit;
            }

            $siteAry[] = $sAry;
        }

        if (isset($siteAry[0])) {
            $this->data['table'] = Html::encode($siteAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get site by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $site = Table\Sites::findById($id);
        if (isset($site->id)) {
            $siteValues = $site->getValues();
            $siteValues = array_merge($siteValues, FieldValue::getAll($id));
            $this->data = array_merge($this->data, $siteValues);
        }
    }

    /**
     * Save site
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $fields = $form->getFields();

        $docRoot = ((substr($fields['document_root'], -1) == '/') && (substr($fields['document_root'], -1) == "\\")) ?
            substr($fields['document_root'], 0, -1) : $fields['document_root'];

        if ($fields['base_path'] != '') {
            $basePath = ((substr($fields['base_path'], 0, 1) != '/') && (substr($fields['base_path'], 0, 1) != "\\")) ?
                '/' . $fields['base_path'] : $fields['base_path'];

            if ((substr($basePath, -1) == '/') && (substr($basePath, -1) == "\\")) {
                $basePath = substr($basePath, 0, -1);
            }
        } else {
            $basePath = '';
        }

        $site = new Table\Sites(array(
            'domain'        => $fields['domain'],
            'document_root' => str_replace('\\', '/', $docRoot),
            'base_path'     => str_replace('\\', '/', $basePath),
            'title'         => $fields['title'],
            'force_ssl'     => (int)$fields['force_ssl'],
            'live'          => (int)$fields['live']
        ));

        $site->save();
        $this->data['id'] = $site->id;

        $user = Table\Users::findById($this->data['user']->id);
        $siteIds = unserialize($user->site_ids);
        $siteIds[] = $site->id;
        $user->site_ids = serialize($siteIds);
        $user->update();

        $sess = \Pop\Web\Session::getInstance();
        $sess->user->site_ids = $siteIds;

        FieldValue::save($fields, $site->id);

        $this->createFolders($docRoot, $basePath);

        // Copy any themes over
        $themes = Table\Extensions::findAll(null, array('type' => 0));
        if (isset($themes->rows[0])) {
            $themePath = $docRoot . $basePath . CONTENT_PATH . '/extensions/themes';
            foreach ($themes->rows as $theme) {
                if (!file_exists($themePath . '/' . $theme->name)) {
                    copy(
                        $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme->file,
                        $themePath . '/' . $theme->file
                    );
                    $archive = new \Pop\Archive\Archive($themePath . '/' . $theme->file);
                    $archive->extract($themePath . '/');
                    if ((stripos($theme->file, 'gz') || stripos($theme->file, 'bz')) && (file_exists($themePath . '/' . $theme->name . '.tar'))) {
                        unlink($themePath . '/' . $theme->name . '.tar');
                    }
                }
            }
        }
    }

    /**
     * Update site
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $fields = $form->getFields();

        $site = Table\Sites::findById($fields['id']);

        $docRoot = ((substr($fields['document_root'], -1) == '/') && (substr($fields['document_root'], -1) == "\\")) ?
            substr($fields['document_root'], 0, -1) : $fields['document_root'];

        $oldDocRoot = $site->document_root;

        $docRoot = str_replace('\\', '/', $docRoot);

        if ($fields['base_path'] != '') {
            $basePath = ((substr($fields['base_path'], 0, 1) != '/') && (substr($fields['base_path'], 0, 1) != "\\")) ?
                '/' . $fields['base_path'] : $fields['base_path'];

            if ((substr($basePath, -1) == '/') && (substr($basePath, -1) == "\\")) {
                $basePath = substr($basePath, 0, -1);
            }
        } else {
            $basePath = '';
        }

        $basePath = str_replace('\\', '/', $basePath);

        $site->domain        = $fields['domain'];
        $site->document_root = $docRoot;
        $site->base_path     = $basePath;
        $site->title         = $fields['title'];
        $site->force_ssl     = (int)$fields['force_ssl'];
        $site->live          = (int)$fields['live'];

        $site->update();
        $this->data['id'] = $site->id;

        FieldValue::update($fields, $site->id);

        if ($oldDocRoot != $docRoot) {
            $this->createFolders($docRoot, $basePath);

            // Copy any themes over
            $themes = Table\Extensions::findAll(null, array('type' => 0));
            if (isset($themes->rows[0])) {
                $themePath = $docRoot . $basePath . CONTENT_PATH . '/extensions/themes';
                foreach ($themes->rows as $theme) {
                    if (!file_exists($themePath . '/' . $theme->name)) {
                        copy(
                            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme->file,
                            $themePath . '/' . $theme->file
                        );
                        $archive = new \Pop\Archive\Archive($themePath . '/' . $theme->file);
                        $archive->extract($themePath . '/');
                        if ((stripos($theme->file, 'gz') || stripos($theme->file, 'bz')) && (file_exists($themePath . '/' . $theme->name . '.tar'))) {
                            unlink($themePath . '/' . $theme->name . '.tar');
                        }
                    }
                }
            }
        }
    }

    /**
     * Remove sites
     *
     * @param array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_sites'])) {
            foreach ($post['remove_sites'] as $id) {
                $site = Table\Sites::findById($id);
                if (isset($site->id)) {
                    $users = Table\Users::findAll();
                    foreach ($users->rows as $user) {
                        $siteIds = unserialize($user->site_ids);
                        if (in_array($site->id, $siteIds)) {
                            $key = array_search($site->id, $siteIds);
                            unset($siteIds[$key]);
                            $u = Table\Users::findById($user->id);
                            if (isset($u->id)) {
                                $u->site_ids = serialize($siteIds);
                                $u->update();
                            }
                        }
                    }

                    $site->delete();
                }
            }
        }
    }

    /**
     * Create site folders
     *
     * @param string $docRoot
     * @param string $basePath
     * @return void
     */
    protected function createFolders($docRoot, $basePath)
    {
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media');

        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html'
        );
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html', 0777);
    }

}

