<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Table;

class Update extends AbstractForm
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  string $type
     * @param  string $name
     * @param  string $format
     * @param  string $version
     * @return self
     */
    public function __construct($action = null, $method = 'post', $type = null, $name = null, $format = null, $version = null)
    {
        parent::__construct($action, $method, null, '        ');

        $site = Table\Sites::getSite();
        if (strpos($site->domain, 'www.') !== false) {
            $domain = 'ftp.' . str_replace('www.', '', $site->domain);
        } else {
            $domain = $site->domain;
        }

        $rootValue = (($_POST) && isset($_POST['change_ftp_root'])) ? $_POST['change_ftp_root'] : null;

        $fields1 = array(
            'ftp_address' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('FTP Address'),
                'required'   => true,
                'attributes' => array('size' => 40),
                'value'      => $domain
            ),
            'username' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Username'),
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'password' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Password'),
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'ftp_root' => array(
                'type'       => 'radio',
                'label'      => $this->i18n->__('FTP Root'),
                'value' => array(
                    '0' => $this->i18n->__('Log directly into the document root.') . '<br /><br />',
                    '1' => $this->i18n->__('No, change the directory to') . ' <input style="margin-left: 5px; width: 150px; height: 15px; font-size: 0.9em;" type="text" size="18" name="change_ftp_root" value="' . $rootValue . '" />'
                ),
                'marked' => '0'
            )
        );

        $fields2 = array(
            'submit' => array(
                'type'  => 'submit',
                'value' => $this->i18n->__('UPDATE'),
                'attributes' => array(
                    'class' => 'save-btn'
                )
            ),
            'use_pasv' => array(
                'type'     => 'radio',
                'label'    => $this->i18n->__('Use PASV'),
                'value' => array(
                    '1' => $this->i18n->__('Yes'),
                    '0' => $this->i18n->__('No')
                ),
                'marked' => '1'
            ),
            'protocol' => array(
                'type'     => 'radio',
                'label'    => $this->i18n->__('Protocol'),
                'value'    => array(
                    '0' => $this->i18n->__('FTP'),
                    '1' => $this->i18n->__('FTPS')
                ),
                'marked' => '0'
            ),
            'type' => array(
                'type'  => 'hidden',
                'value' => $type
            ),
            'name' => array(
                'type'  => 'hidden',
                'value' => $name
            ),
            'version' => array(
                'type'  => 'hidden',
                'value' => $version
            ),
            'format' => array(
                'type'      => 'hidden',
                'value'     => $format
            ),
            'base_path' => array(
                'type'  => 'hidden',
                'value' => BASE_PATH
            ),
            'content_path' => array(
                'type'  => 'hidden',
                'value' => CONTENT_PATH
            ),
            'app_path' => array(
                'type'  => 'hidden',
                'value' => APP_PATH
            )
        );

        $this->initFieldsValues = array($fields2, $fields1);
        $this->setAttributes('id', 'update-form');
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

        // Add validator for checking ftp connection
        if ($_POST) {
            try {
                $ftp = @new \Pop\Ftp\Ftp($this->ftp_address, $this->username, $this->password, (bool)$this->protocol);
            } catch (\Exception $e) {
                $this->getElement('ftp_address')
                     ->addValidator(new Validator\NotEqual($this->ftp_address, $e->getMessage()));
            }
        }

        return $this;
    }

}

