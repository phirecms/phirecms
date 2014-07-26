<?php
/**
 * Phire CMS 2.0 Override Module Config File
 */

$phCfg = include __DIR__ . '/../../../..' . APP_PATH . '/vendor/Phire/config/module.php';

/**
 * Option 1:
 * Merge the existing Phire module config with any new custom values.
 */
/*
return array(
    'Phire' => $phCfg['Phire']->merge(
        new \Pop\Config(array(
            'user_view' => array(
                2001 => array(
                    'name', 'company', 'username', 'email'
                )
            )
        ))
    )
);
*/

/**
 * Option 2:
 * Completely replace the entire Phire module config.
 *
 * IMPORTANT! If you do this, you must copy the original config and build upon that.
 * Failure to do so could cause the system to break or act irregular.
 * It is better to use the merge method above in option 1.
 */
/*
return array(
    'Phire' => new \Pop\Config(array(
        // Add your custom Phire config values
    ))
);
*/

// Comment this line out if you un-comment and use either of the blocks of code above
return $phCfg;
