<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;
use Pop\Web\Server;
use Phire\Model;

class Config extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'config';

    /**
     * @var   string
     */
    protected $primaryId = 'setting';

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Static method to get all configuration values
     *
     * @return \ArrayObject
     */
    public static function getConfig()
    {
        $cfg = static::findById('system_document_root');
        if (($cfg->value == '') || (isset($_SERVER['DOCUMENT_ROOT']) && ($cfg->value != $_SERVER['DOCUMENT_ROOT']))) {
            static::setConfig();
        }

        return static::findAll();
    }

    /**
     * Static method to get base configuration values
     *
     * @return \ArrayObject
     */
    public static function getSystemConfig()
    {
        $settings = array(
            'system_title',
            'system_email',
            'reply_email',
            'site_title',
            'separator',
            'default_language',
            'datetime_format',
            'media_allowed_types',
            'media_max_filesize',
            'media_actions',
            'media_image_adapter',
            'pagination_limit',
            'pagination_range',
            'force_ssl',
            'live'
        );

        $config = array();
        $cfg = static::findAll();

        foreach ($cfg->rows as $c) {
            if (in_array($c->setting, $settings)) {
                $config[$c->setting] = (($c->setting == 'media_allowed_types') || ($c->setting == 'media_actions')) ?
                    unserialize($c->value) : $c->value;
            }
        }

        $allowedTypes = Model\Config::getMediaTypes();
        foreach ($allowedTypes as $key => $value) {
            if (!in_array($key, $config['media_allowed_types'])) {
                unset($allowedTypes[$key]);
            }
        }

        if ($config['media_max_filesize'] > 999999) {
            $maxSize = round($config['media_max_filesize'] / 1000000) . ' MB';
        } else if ($config['media_max_filesize'] > 999) {
            $maxSize = round($config['media_max_filesize'] / 1000) . ' KB';
        } else {
            $maxSize = $config['media_max_filesize'] . ' B';
        }

        $config['media_max_filesize_formatted'] = $maxSize;
        $config['media_allowed_types'] = $allowedTypes;

        $site = Sites::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
        if (isset($site->id)) {
            $config['site_title'] = $site->title;
            $config['base_path']  = $site->base_path;
            $config['force_ssl']  = $site->force_ssl;
            $config['live']       = $site->live;
        } else {
            $config['base_path']  = BASE_PATH;
        }

        return new \ArrayObject($config, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Static method to get all configuration values
     *
     * @return array
     */
    public static function setConfig()
    {
        if (isset($_SERVER) && isset($_SERVER['SERVER_SOFTWARE'])) {
            $server = new Server();

            $cfg = static::findById('system_domain');
            $cfg->value = $_SERVER['HTTP_HOST'];
            $cfg->update();

            $cfg = static::findById('system_document_root');
            $cfg->value = $_SERVER['DOCUMENT_ROOT'];
            $cfg->update();

            $cfg = static::findById('server_operating_system');
            $cfg->value = $server->getOs() . ' (' . $server->getDistro() . ')';
            $cfg->update();

            $cfg = static::findById('server_software');
            $cfg->value = $server->getServer() . ' ' . $server->getServerVersion();
            $cfg->update();

            $cfg = static::findById('database_version');
            $cfg->value = static::getDb()->adapter()->version();
            $cfg->update();

            $cfg = static::findById('php_version');
            $cfg->value = $server->getPhp();
            $cfg->update();

            $cfg = static::findById('reply_email');
            $cfg->value = 'noreply@' . str_replace('www.', '', $_SERVER['HTTP_HOST']);
            $cfg->update();
        }
    }

    /**
     * Static method to get media sizes
     *
     * @return array
     */
    public static function getMediaSizes()
    {
        $mediaActions = unserialize(static::findById('media_actions')->value);
        return array_keys($mediaActions);
    }

    /**
     * Static method to get max file size allowed
     *
     * @param  boolean $string
     * @return string
     */
    public static function getMaxFileSize($string = true)
    {
        $max = null;

        $postMax = strtoupper(ini_get('post_max_size'));
        $fileMax = strtoupper(ini_get('upload_max_filesize'));
        $phireMax = static::findById('media_max_filesize')->value;

        if (strpos($postMax, 'M') !== false) {
            $postMax = trim(str_replace('M', '', $postMax)) . '000000';
        } else if (strpos($postMax, 'K') !== false) {
            $postMax = trim(str_replace('K', '', $postMax)) . '000';
        }

        if (strpos($fileMax, 'M') !== false) {
            $fileMax = trim(str_replace('M', '', $fileMax)) . '000000';
        } else if (strpos($fileMax, 'K') !== false) {
            $fileMax = trim(str_replace('K', '', $fileMax)) . '000';
        }

        $max = min((int)$postMax, (int)$fileMax, (int)$phireMax);

        if ($string) {
            if ($max > 1000000) {
                $max = floor($max / 1000000) . ' MB';
            } else if ($max > 1000) {
                $max = floor($max / 1000) . ' KB';
            } else {
                $max .= ' B';
            }
        }

        return $max;
    }

    /**
     * Static method to get i18n object
     *
     * @return \Pop\I18n\I18n
     */
    public static function getI18n()
    {
        $lang = static::findById('default_language')->value;
        if (!defined('POP_LANG')) {
            define('POP_LANG', $lang);
            $i18n = \Pop\I18n\I18n::factory($lang);
        } else {
            $i18n = \Pop\I18n\I18n::factory(POP_LANG);
        }

        $i18n->loadFile(__DIR__ . '/../../../data/assets/i18n/' . $i18n->getLanguage() . '.xml');

        // Load any module language files
        $modules = Extensions::findAll(null, array('type' => 1));
        foreach ($modules->rows as $module) {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $module->name) &&
                file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $module->name . '/data') &&
                file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $module->name . '/data/assets/i18n') &&
                file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $module->name . '/data/assets/i18n/' . $i18n->getLanguage() . '.xml')) {
                $i18n->loadFile($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $module->name . '/data/assets/i18n/' . $i18n->getLanguage() . '.xml');
            }
        }
        return $i18n;
    }

}

