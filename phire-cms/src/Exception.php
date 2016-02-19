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
namespace Phire;

/**
 * Phire Exception class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.0
 */
class Exception extends \Exception {


    /**
     * Install error flag
     * @var boolean
     */
    protected $installError = false;

    /**
     * Set the install error flag
     *
     * @param  boolean $flag
     * @return void
     */
    public function setInstallErrorFlag($flag)
    {
        $this->installError = (bool)$flag;
    }

    /**
     * Get the install error flag
     *
     * @return boolean
     */
    public function isInstallError()
    {
        return $this->installError;
    }

}