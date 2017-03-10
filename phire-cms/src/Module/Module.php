<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Module;

use Pop\Application;

/**
 * Main module class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Module extends \Pop\Module\Module
{

    /**
     * Register module
     *
     * @param  Application $application
     * @return Module
     */
    public function register(Application $application)
    {
        parent::register($application);

        if (null !== $this->config) {
            // If the module has navigation
            $topNavParams  = $this->application->services()->getParams('nav.top');
            $sideNavParams = $this->application->services()->getParams('nav.side');

            // If the module has module-level navigation
            if (isset($this->config['nav.module'])) {
                if (!isset($topNavParams['tree']['modules']['children'])) {
                    $topNavParams['tree']['modules']['children'] = [];
                }
                $topNavParams['tree']['modules']['children'] = array_merge([$this->config['nav.module']], $topNavParams['tree']['modules']['children']);
            }

            // If the module has top-level navigation
            if (isset($this->config['nav.top'])) {
                $newNav = [];
                foreach ($this->config['nav.top'] as $key => $value) {
                    if (($key !== 'modules') && ($key !== 'users') && ($key !== 'config')) {
                        $newNav[$key] = $value;
                    } else {
                        $topNavParams['tree'][$key] = array_merge_recursive($topNavParams['tree'][$key], $value);
                    }
                }
                if (count($newNav) > 0) {
                    $topNavParams['tree'] = array_merge($newNav, $topNavParams['tree'], $this->config['nav.top']);
                }
            }

            // If the module has system-level navigation
            if (isset($this->config['nav.side'])) {
                $newNav = [];
                foreach ($this->config['nav.side'] as $key => $value) {
                    if (!isset($sideNavParams['tree'][$key])) {
                        $sideNavParams['tree'][$key] = [];
                    }
                    $sideNavParams['tree'][$key] = array_merge_recursive($sideNavParams['tree'][$key], $value);
                }
                if (count($newNav) > 0) {
                    $sideNavParams['tree'] = array_merge($newNav, $sideNavParams['tree'], $this->config['nav.side']);
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

            // If the module has a dashboard include
            if (isset($this->config['dashboard'])) {
                $dashboard = array_merge([$this->config['dashboard']], $this->application->config()['dashboard']);
                $this->application->mergeConfig(['dashboard' => $dashboard]);
            }

            // If the module has a dashboard side include
            if (isset($this->config['dashboard_side'])) {
                $dashboard_side = array_merge([$this->config['dashboard_side']], $this->application->config()['dashboard_side']);
                $this->application->mergeConfig(['dashboard_side' => $dashboard_side]);
            }

            // Add the nav params back to the service
            $this->application->services()->setParams('nav.top', $topNavParams);
            $this->application->services()->setParams('nav.side', $sideNavParams);
        }

        return $this;
    }

}