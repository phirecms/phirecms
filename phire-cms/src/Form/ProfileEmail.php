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

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

/**
 * ProfileEmail Form class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.1
 */
class ProfileEmail extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return ProfileEmail
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'profile-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return ProfileEmail
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->email)) {
            // Check for dupe username
            $user = null;
            if (null !== $this->email) {
                $user = Table\Users::findBy(['username' => $this->email]);
                if (isset($user->id) && ($this->id != $user->id)) {
                    $this->getElement('email')
                         ->addValidator(new Validator\NotEqual($this->email, 'That username is not allowed.'));
                } else {
                    $email = Table\Users::findBy(['email' => $this->email]);
                    if (isset($email->id) && ($this->id != $email->id)) {
                        $this->getElement('email')
                             ->addValidator(new Validator\NotEqual($this->email, 'That email is not allowed.'));
                    }
                }
            }

            // Check password matches
            if (!empty($this->password1)) {
                $this->getElement('password2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
            }
        }

        return $this;
    }

}