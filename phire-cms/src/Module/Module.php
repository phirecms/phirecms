<?php

namespace Phire\Module;

class Module extends \Pop\Module\Module
{

    /**
     * Register module
     *
     * @param  \Pop\Application $application
     * @return Module
     */
    public function register(\Pop\Application $application)
    {
        parent::register($application);

        if (null !== $this->config) {
            // If the module has navigation
            $params = $this->application->services()->getParams('nav.phire');

            // If the module has module-level navigation
            if (isset($this->config['nav.module'])) {
                if (!isset($params['tree']['modules']['children'])) {
                    $params['tree']['modules']['children'] = [];
                }
                $params['tree']['modules']['children'] = array_merge([$this->config['nav.module']], $params['tree']['modules']['children']);
            }

            // If the module has system-level navigation
            if (isset($this->config['nav.phire'])) {
                $newNav = [];
                foreach ($this->config['nav.phire'] as $key => $value) {
                    if (($key !== 'modules') && ($key !== 'users') && ($key !== 'config')) {
                        $newNav[$key] = $value;
                    } else {
                        $params['tree'][$key] = array_merge_recursive($params['tree'][$key], $value);
                    }
                }
                if (count($newNav) > 0) {
                    $params['tree'] = array_merge($newNav, $params['tree'], $this->config['nav.phire']);
                }
            }

            // If the module has ACL resources
            if (isset($this->config['resources'])) {
                $this->application->mergeConfig(['resources' => $this->config['resources']]);
            }

            // If the module has form configs
            if (isset($this->config['forms'])) {
                $this->application->mergeConfig(['forms' => $this->config['forms']]);
            }

            // Add the nav params back to the service
            $this->application->services()->setParams('nav.phire', $params);
        }

        return $this;
    }

}