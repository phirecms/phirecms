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
namespace Phire\Model;

use Phire\Table;
use Pop\File\Dir;
use Pop\Http\Client\Curl;
use Pop\Web\Server;

/**
 * Config Model class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.1
 */
class Config extends AbstractModel
{

    /**
     * Create a new config model object
     *
     * @param array $data
     * @return Config
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $server = new Server();
        $config = Table\Config::getConfig();
        $distro = $server->getDistro();

        $this->data['overview'] = [
            'version'          => \Phire\Module::VERSION,
            'domain'           => $config['domain'],
            'document_root'    => $config['document_root'],
            'base_path'        => (BASE_PATH == '') ? '&nbsp;' : BASE_PATH,
            'application_path' => (APP_PATH == '') ? '&nbsp;' : APP_PATH,
            'content_path'     => CONTENT_PATH,
            'modules_path'     => MODULES_PATH,
            'operating_system' => $server->getOs() . (!empty($distro) ? ' (' . $distro . ')' : null),
            'software'         => $server->getServer() . ' ' . $server->getServerVersion(),
            'database_version' => Table\Config::db()->version(),
            'php_version'      => $server->getPhp(),
            'installed_on'     => (($config['installed_on'] != '0000-00-00 00:00:00') ?
                date($config['datetime_format'], strtotime($config['installed_on'])) : ''),
            'updated_on'       => (($config['updated_on'] != '0000-00-00 00:00:00') ?
                date($config['datetime_format'], strtotime($config['updated_on'])) : '')
        ];

        $config['datetime_formats'] = [
            'M j Y', 'F d, Y', 'm/d/Y', 'Y/m/d', 'F d, Y g:i A',
            'M j Y g:i A', 'm/d/Y g:i A', 'Y/m/d g:i A'
        ];

        $config['system_themes']        = [];
        $config['custom_system_themes'] = [];

        $dir = new Dir(__DIR__ . '/../../data/themes');
        foreach ($dir->getFiles() as $file) {
            if ($file != 'index.html') {
                $config['system_themes'][$file] = $file;
            }
        }

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules/phire/themes');
            foreach ($dir->getFiles() as $file) {
                if ($file != 'index.html') {
                    $config['custom_system_themes'][$file] = $file;
                }
            }
        }

        sort($config['system_themes']);
        sort($config['custom_system_themes']);

        $this->data['config']  = $config;
        $this->data['modules'] = Table\Modules::findAll(['order' => 'id DESC', 'limit' => 5])->rows();
    }

    /**
     * Get configuration data
     *
     * @return \ArrayObject
     */
    public function getAll()
    {
        return new \ArrayObject(Table\Config::getConfig(), \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get configuration data by id
     *
     * @param  string $id
     * @return mixed
     */
    public function getById($id)
    {
        return Table\Config::findById($id)->value;
    }

    /**
     * Save the config data
     *
     * @param  array $post
     * @return void
     */
    public function save(array $post)
    {
        $config = Table\Config::findById('domain');
        if (isset($_SERVER['HTTP_HOST']) && ($config->value != $_SERVER['HTTP_HOST'])) {
            $config->value = $_SERVER['HTTP_HOST'];
            $config->save();
        }

        $config = Table\Config::findById('document_root');
        if (isset($_SERVER['DOCUMENT_ROOT']) && ($config->value != $_SERVER['DOCUMENT_ROOT'])) {
            $config->value = $_SERVER['DOCUMENT_ROOT'];
            $config->save();
        }

        if (!empty($post['datetime_format_custom']) && ($post['datetime_format'] == 'custom')) {
            $dateFormatValue = str_replace(['"', "'"], ['', ''], strip_tags($post['datetime_format_custom']));
        } else if (!empty($post['datetime_format']) && ($post['datetime_format'] != 'custom')) {
            $dateFormatValue = $post['datetime_format'];
        } else {
            $dateFormatValue = 'M j Y';
        }

        $config = Table\Config::findById('datetime_format');
        $config->value = $dateFormatValue;
        $config->save();

        $config = Table\Config::findById('pagination');
        $config->value = (int)$post['pagination'];
        $config->save();

        $config = Table\Config::findById('system_theme');
        $oldValue = $config->value;
        $config->value = $post['system_theme'];
        $config->save();

        if (isset($_SERVER['DOCUMENT_ROOT']) &&
            file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $oldValue)) {
            $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $oldValue);
            $dir->emptyDir(true);
        }
    }

    /**
     * Get update info
     *
     * @param  boolean $live
     * @return \ArrayObject
     */
    public function getUpdates($live = true)
    {
        $updates = [
            'phirecms' => null,
            'modules'  => []
        ];

        if ($live) {
            $headers = [
                'Authorization: ' . base64_encode('phire-updater-' . time()),
                'User-Agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ?
                    $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0')
            ];

            $curl = new Curl('http://updates.phirecms.org/latest/phirecms', [
                CURLOPT_HTTPHEADER => $headers
            ]);

            $curl->send();

            if ($curl->getCode() == 200) {
                $json = json_decode($curl->getBody(), true);
                $updates['phirecms'] = $json['version'];
            }

            $modules = Table\Modules::findAll();
            if ($modules->hasRows()) {
                foreach ($modules->rows() as $module) {
                    $curl = new Curl('http://updates.phirecms.org/latest/' . $module->name, [
                        CURLOPT_HTTPHEADER => $headers
                    ]);
                    $curl->send();

                    if ($curl->getCode() == 200) {
                        $json = json_decode($curl->getBody(), true);
                        $updates['modules'][$module->name] = $json['version'];
                    }
                }
            }
        }

        return new \ArrayObject($updates, \ArrayObject::ARRAY_AS_PROPS);
    }

}