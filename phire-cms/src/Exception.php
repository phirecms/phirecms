<?php

namespace Phire;

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