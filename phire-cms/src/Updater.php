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
 * Phire Updater class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.1
 */
class Updater extends BaseUpdater
{

    public function update1()
    {
        // Move the phire override asset/config folder up a level
        if (!file_exists(CONTENT_ABS_PATH . '/phire')) {
            mkdir(CONTENT_ABS_PATH . '/phire');
            chmod(CONTENT_ABS_PATH . '/phire', 0777);
            $dir = new \Pop\File\Dir(MODULES_ABS_PATH . '/phire');
            $dir->copyDir(CONTENT_ABS_PATH . '/phire', false);
            $dir->emptyDir(true);
        }
    }

}
