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
namespace Phire\Form;

use Pop\Http\Client\Curl;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Update Form class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.1.0
 */
class Update extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Update
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'update-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return Update
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->resource)) {
            $curl = new Curl('http://updates.phirecms.org/test');
            $curl->setFields($this->getFields());
            $curl->setPost(true);

            $curl->send();

            if ($curl->getCode() == 401) {
                $json = json_decode($curl->getBody(), true);
                $this->getElement('ftp_address')
                     ->addValidator(new Validator\NotEqual($this->ftp_address, $json['error']));
            }
        }

        return $this;
    }
}