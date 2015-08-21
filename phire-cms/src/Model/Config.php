<?php

namespace Phire\Model;

use Phire\Table;
use Pop\Web\Server;

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

        $this->data['overview'] = [
            'version'          => \Phire\Module::VERSION,
            'domain'           => $_SERVER['HTTP_HOST'],
            'document_root'    => $_SERVER['DOCUMENT_ROOT'],
            'base_path'        => (BASE_PATH == '') ? '&nbsp;' : BASE_PATH,
            'application_path' => (APP_PATH == '') ? '&nbsp;' : APP_PATH,
            'content_path'     => CONTENT_PATH,
            'module_path'      => MODULE_PATH,
            'operating_system' => $server->getOs() . ' (' . $server->getDistro() . ')',
            'software'         => $server->getServer() . ' ' . $server->getServerVersion(),
            'database_version' => Table\Config::getDb()->version(),
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

        $this->data['config']  = $config;
        $this->data['modules'] = Table\Modules::findAll(null, ['order' => 'order, id ASC'])->rows();
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
        $config = Table\Config::findById('datetime_format');
        $config->value = (!empty($post['datetime_format'])) ? $post['datetime_format'] : $post['datetime_format_custom'];
        $config->save();

        $config = Table\Config::findById('pagination');
        $config->value = (int)$post['pagination'];
        $config->save();
    }

}