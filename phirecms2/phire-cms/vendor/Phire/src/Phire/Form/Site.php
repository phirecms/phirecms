<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Table;

class Site extends AbstractForm
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  int    $sid
     * @return self
     */
    public function __construct($action = null, $method = 'post', $sid = 0)
    {
        parent::__construct($action, $method, null, '        ');

        $fieldGroups = array();
        $dynamicFields = false;

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, 0, $sid);
        if ($newFields['dynamic']) {
            $dynamicFields = true;
        }
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
            }
        }

        $fields1 = array(
            'title' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Title'),
                'required'   => true,
                'attributes' => array('size' => 80)
            ),
            'domain' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Domain'),
                'required'   => true,
                'attributes' => array('size' => 80)
            ),
            'document_root' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Document Root'),
                'required'   => true,
                'attributes' => array('size' => 80)
            ),
            'base_path' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Base Path'),
                'attributes' => array('size' => 80)
            )
        );

        if ($sid != 0) {
            $fields1['domain']['attributes']['onkeyup'] = "phire.updateTitle('#site-header-title', this);";
        }

        $fields2 = array(
            'submit' => array(
                'type'  => 'submit',
                'value' => $this->i18n->__('SAVE'),
                'attributes' => array(
                    'class' => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => $this->i18n->__('UPDATE'),
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#site-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class'   => 'update-btn'
                )
            ),
            'force_ssl' => array(
                'type'     => 'radio',
                'label'    => $this->i18n->__('Force SSL'),
                'value' => array(
                    '0' => $this->i18n->__('No'),
                    '1' => $this->i18n->__('Yes')
                ),
                'marked' => '0'
            ),
            'live' => array(
                'type'     => 'radio',
                'label'    => $this->i18n->__('Live'),
                'value'    => array(
                    '0' => $this->i18n->__('No'),
                    '1' => $this->i18n->__('Yes')
                ),
                'marked' => '1'
            ),
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            )
        );

        $allFields = array($fields2);
        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }

        $allFields[] = $fields1;
        $this->initFieldsValues = $allFields;
        $this->setAttributes('id', 'site-form');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $filters
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null)
    {
        parent::setFieldValues($values, $filters);

        // Add validators for checking dupe names and devices
        if (($_POST) && isset($_POST['id'])) {

            $site = Table\Sites::findBy(array('domain' => $this->domain));
            if ((isset($site->id) && ($this->id != $site->id)) || ($this->domain == $_SERVER['HTTP_HOST'])) {
                $this->getElement('domain')
                     ->addValidator(new Validator\NotEqual($this->domain, $this->i18n->__('That site domain already exists.')));
            }

            $site = Table\Sites::findBy(array('document_root' => $this->document_root));
            if ((isset($site->id) && ($this->id != $site->id))) {
                $this->getElement('document_root')
                    ->addValidator(new Validator\NotEqual($this->document_root, $this->i18n->__('That site document root already exists.')));
            }

            $docRoot = ((substr($this->document_root, -1) == '/') || (substr($this->document_root, -1) == "\\")) ?
                substr($this->document_root, 0, -1) : $this->document_root;

            if ($this->base_path != '') {
                $basePath = ((substr($this->base_path, 0, 1) != '/') || (substr($this->base_path, 0, 1) != "\\")) ?
                    '/' . $this->base_path : $this->base_path;

                if ((substr($basePath, -1) == '/') || (substr($basePath, -1) == "\\")) {
                    $basePath = substr($basePath, 0, -1);
                }
            } else {
                $basePath = '';
            }

            if (!file_exists($docRoot)) {
                $this->getElement('document_root')
                     ->addValidator(new Validator\NotEqual($this->document_root, $this->i18n->__('That site document root does not exists.')));
            } else if (!file_exists($docRoot . $basePath)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The base path does not exist under that document root.')));
            } else if (!file_exists($docRoot . $basePath . DIRECTORY_SEPARATOR . 'index.php')) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The index controller does not exist under that document root and base path.')));
            } else if (!file_exists($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The content path does not exist under that document root and base path.')));
            } else if (!is_writable($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The content path is not writable under that document root and base path.')));
            }
        }

        $this->checkFiles();

        return $this;
    }

}

