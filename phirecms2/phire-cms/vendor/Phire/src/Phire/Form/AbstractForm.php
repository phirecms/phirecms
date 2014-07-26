<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;
use Phire\Table;

abstract class AbstractForm extends Form
{

    /**
     * I18n object
     * @var \Pop\I18n\I18n
     */
    protected $i18n = null;

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return \Phire\Form\AbstractForm
     */
    public function __construct($action = null, $method = 'post', array $fields = null, $indent = null)
    {
        $this->i18n = Table\Config::getI18n();
        parent::__construct($action, $method, $fields, $indent);
    }

    protected function checkFiles()
    {
        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if (($_FILES) && isset($_FILES[$key]) && ($_FILES[$key]['error'] == 1)) {
                    $this->getElement($key)
                         ->addValidator(new Validator\LessThanEqual(-1, $this->i18n->__("The 'upload_max_filesize' setting of %1 exceeded.", ini_get('upload_max_filesize'))));
                } else if ($value['error'] != 4) {
                    if ($value['size'] > $config->media_max_filesize) {
                        $this->getElement($key)
                             ->addValidator(new Validator\LessThanEqual($config->media_max_filesize, $this->i18n->__('The file must be less than %1.', $config->media_max_filesize_formatted)));
                    }
                    if (preg_match($regex, $value['name']) == 0) {
                        $type = strtoupper(substr($value['name'], (strrpos($value['name'], '.') + 1)));
                        $this->getElement($key)
                             ->addValidator(new Validator\NotEqual($value['name'], $this->i18n->__('The %1 file type is not allowed.', $type)));
                    }
                }
            }
        }
    }

}

