<?php

namespace Phire\Acl;

class Acl extends \Pop\Acl\Acl
{

    /**
     * Excluded resources
     * @var array
     */
    protected $excluded = [];

    /**
     * Included resources
     * @var array
     */
    protected $included = [];

    /**
     * Constructor
     *
     * Instantiate the ACL object
     *
     * @param  \Pop\Acl\Role     $role
     * @param  \Pop\Acl\Resource $resource
     * @return Acl
     */
    public function __construct(\Pop\Acl\Role $role = null, \Pop\Acl\Resource $resource = null)
    {
        parent::__construct($role, $resource);

        $this->excluded = [
            APP_URI . '/install[/]',
            APP_URI . '/install/config[/]',
            APP_URI . '/install/user[/]',
            APP_URI . '/verify/:id/:hash',
            APP_URI . '/forgot[/]',
            APP_URI . '/unsubscribe[/]',
            APP_URI . '/logout[/]',
            APP_URI . '/users/roles/json/:id',
            APP_URI . '/config/json/:format'
        ];
    }

    public function getExcluded()
    {
        return $this->excluded;
    }

    public function getIncluded()
    {
        return $this->included;
    }

    public function addExcluded($resource)
    {
        if (!in_array($resource, $this->excluded)) {
            $this->excluded[] = $resource;
        }
        return $this;
    }

    public function addIncluded($resource)
    {
        if (!in_array($resource, $this->included)) {
            $this->included[] = $resource;
        }
        return $this;
    }

}