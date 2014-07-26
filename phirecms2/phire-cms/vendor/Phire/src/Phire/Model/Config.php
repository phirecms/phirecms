<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\File\Dir;
use Pop\Form\Element;
use Pop\I18n\I18n;
use Phire\Table;

class Config extends AbstractModel
{

    /**
     * Allowed media actions
     *
     * @var   array
     */
    protected static $mediaActions = array(
        'resize'         => 'resize',
        'resizeToWidth'  => 'resizeToWidth',
        'resizeToHeight' => 'resizeToHeight',
        'scale'          => 'scale',
        'crop'           => 'crop',
        'cropThumb'      => 'cropThumb'
    );

    /**
     * Allowed media types
     *
     * @var   array
     */
    protected static $mediaTypes = array(
        'ai'     => 'application/postscript',
        'aif'    => 'audio/x-aiff',
        'aiff'   => 'audio/x-aiff',
        'avi'    => 'video/x-msvideo',
        'bmp'    => 'image/x-ms-bmp',
        'bz2'    => 'application/bzip2',
        'css'    => 'text/css',
        'csv'    => 'text/csv',
        'doc'    => 'application/msword',
        'docx'   => 'application/msword',
        'eps'    => 'application/octet-stream',
        'fla'    => 'application/octet-stream',
        'flv'    => 'application/octet-stream',
        'gif'    => 'image/gif',
        'gz'     => 'application/x-gzip',
        'html'   => 'text/html',
        'htm'    => 'text/html',
        'jpe'    => 'image/jpeg',
        'jpg'    => 'image/jpeg',
        'jpeg'   => 'image/jpeg',
        'js'     => 'text/plain',
        'json'   => 'text/plain',
        'mov'    => 'video/quicktime',
        'mp2'    => 'audio/mpeg',
        'mp3'    => 'audio/mpeg',
        'mp4'    => 'video/mp4',
        'mpg'    => 'video/mpeg',
        'mpeg'   => 'video/mpeg',
        'otf'    => 'application/x-font-otf',
        'pdf'    => 'application/pdf',
        'phar'   => 'application/x-phar',
        'php'    => 'text/plain',
        'php3'   => 'text/plain',
        'phtml'  => 'text/plain',
        'png'    => 'image/png',
        'ppt'    => 'application/msword',
        'pptx'   => 'application/msword',
        'psd'    => 'image/x-photoshop',
        'rar'    => 'application/x-rar-compressed',
        'sql'    => 'text/plain',
        'svg'    => 'image/svg+xml',
        'swf'    => 'application/x-shockwave-flash',
        'tar'    => 'application/x-tar',
        'tbz'    => 'application/bzip2',
        'tbz2'   => 'application/bzip2',
        'tgz'    => 'application/x-gzip',
        'tif'    => 'image/tiff',
        'tiff'   => 'image/tiff',
        'tsv'    => 'text/tsv',
        'ttf'    => 'application/x-font-ttf',
        'txt'    => 'text/plain',
        'wav'    => 'audio/x-wav',
        'wma'    => 'audio/x-ms-wma',
        'wmv'    => 'audio/x-ms-wmv',
        'xls'    => 'application/msword',
        'xlsx'   => 'application/msword',
        'xhtml'  => 'application/xhtml+xml',
        'xml'    => 'application/xml',
        'yml'    => 'text/plain',
        'zip'    => 'application/x-zip'
    );

    /**
     * Get media actions
     *
     * @return array
     */
    public static function getMediaActions()
    {
        return self::$mediaActions;
    }

    /**
     * Get media types
     *
     * @return array
     */
    public static function getMediaTypes()
    {
        return self::$mediaTypes;
    }

    /**
     * Get overview configuration values
     *
     * @return array
     */
    public function getOverview()
    {
        $cfg = Table\Config::getConfig();
        $config = array();
        $overview = array();

        foreach ($cfg->rows as $c) {
            $config[$c->setting] = (($c->setting == 'media_allowed_types') || ($c->setting == 'media_actions')) ?
                unserialize($c->value) : $c->value;
        }

        $sysVersion = $config['system_version'];
        $latest = '';
        $handle = fopen('http://update.phirecms.org/system/version', 'r');
        if ($handle !== false) {
            $latest = trim(stream_get_contents($handle));
            fclose($handle);
        }

        if ((version_compare(\Phire\Project::VERSION, $latest) < 0) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Config\IndexController', 'update'))) {
            $sysVersion .= ' (<a href="' . BASE_PATH . APP_URI . '/config/update">' . $this->i18n->__('Update to') . ' ' . $latest . '</a>?)';
        }

        // Set server config settings
        $overview['system'] = array(
            'system_version'          => $sysVersion,
            'system_domain'           => $config['system_domain'],
            'server_operating_system' => $config['server_operating_system'],
            'server_software'         => $config['server_software'],
            'database_version'        => $config['database_version'],
            'php_version'             => $config['php_version'],
            'installed_on'            => date($this->config->datetime_format, strtotime($config['installed_on'])),
            'updated_on'              => ($config['updated_on'] != '0000-00-00 00:00:00') ?
                date($this->config->datetime_format, strtotime($config['updated_on'])) : '(' . $this->i18n->__('Never') . ')'
        );

        $overview['sites'] = array();
        $overview['sites'][$config['system_domain'] . BASE_PATH] = $config['live'];

        $sites = Table\Sites::findAll('id ASC');
        foreach ($sites->rows as $site) {
            $overview['sites'][$site->domain . $site->base_path] = $site->live;
        }

        return $overview;
    }

    /**
     * Get configuration values
     *
     * @return void
     */
    public function getAll()
    {
        $cfg = Table\Config::getConfig();
        $config = array();
        $formattedConfig = array();

        foreach ($cfg->rows as $c) {
            $config[$c->setting] = (($c->setting == 'media_allowed_types') || ($c->setting == 'media_actions')) ?
                $value = unserialize($c->value) : $c->value;
        }

        $sysVersion = $config['system_version'];
        $latest = '';
        $handle = fopen('http://update.phirecms.org/system/version', 'r');
        if ($handle !== false) {
            $latest = trim(stream_get_contents($handle));
            fclose($handle);
        }

        if ((version_compare(\Phire\Project::VERSION, $latest) < 0) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Config\IndexController', 'update'))) {
            $sysVersion .= ' (<a href="' . BASE_PATH . APP_URI . '/config/update">' . $this->i18n->__('Update to') . ' ' . $latest . '</a>?)';
        }

        // Set server config settings
        $formattedConfig['server'] = array(
            'system_version'          => $sysVersion,
            'system_domain'           => $config['system_domain'],
            'system_document_root'    => $config['system_document_root'],
            'system_base_path'        => BASE_PATH,
            'system_application_path' => APP_PATH,
            'system_content_path'     => CONTENT_PATH,
            'server_operating_system' => $config['server_operating_system'],
            'server_software'         => $config['server_software'],
            'database_version'        => $config['database_version'],
            'php_version'             => $config['php_version'],
            'installed_on'            => date($this->config->datetime_format, strtotime($config['installed_on'])),
            'updated_on'              => ($config['updated_on'] != '0000-00-00 00:00:00') ?
                date($this->config->datetime_format, strtotime($config['updated_on'])) : '(' . $this->i18n->__('Never') . ')'
        );

        // Set site title form element
        $siteTitle = new Element('text', 'site_title', $config['site_title']);
        $siteTitle->setAttributes('size', 85)
                  ->setAttributes('style', 'padding: 5px;');

        // Set system title form element
        $systemTitle = new Element('text', 'system_title', $config['system_title']);
        $systemTitle->setAttributes('size', 85)
                    ->setAttributes('style', 'padding: 5px;');

        // Set system email form element
        $systemEmail = new Element('text', 'system_email', $config['system_email']);
        $systemEmail->setAttributes('size', 85)
                    ->setAttributes('style', 'padding: 5px;');

        // Set system email form element
        $replyEmail = new Element('text', 'reply_email', $config['reply_email']);
        $replyEmail->setAttributes('size', 85)
                   ->setAttributes('style', 'padding: 5px;');

        // Set separator form element
        $separator = new Element('text', 'separator', $config['separator']);
        $separator->setAttributes('size', 3)
                  ->setAttributes('style', 'padding: 5px;');

        // Set default language form element
        $langs = I18n::getLanguages();
        foreach ($langs as $key => $value) {
            $langs[$key] = substr($value, 0, strpos($value, ' ('));
        }

        $lang = new Element\Select('default_language', $langs, $config['default_language'], '                    ');

        // Set date and time format form element
        $datetime = $this->getDateTimeFormat($config['datetime_format']);

        // Set max media size form element
        $maxSize = new Element('text', 'media_max_filesize', $this->getMaxSize($config['media_max_filesize']));
        $maxSize->setAttributes('size', 10)
                ->setAttributes('style', 'padding: 3px;');

        // Set page limit form element
        $pageLimit = new Element('text', 'pagination_limit', $config['pagination_limit']);
        $pageLimit->setAttributes('size', 10)
                  ->setAttributes('style', 'padding: 3px;');

        // Set page range form element
        $pageRange = new Element('text', 'pagination_range', $config['pagination_range']);
        $pageRange->setAttributes('size', 10)
                  ->setAttributes('style', 'padding: 3px;');

        // Set media actions and media types form elements
        $mediaConfig = $this->getMediaConfig($config['media_actions']);
        $mediaTypes = $this->getMediaAllowedTypes($config['media_allowed_types']);

        $imageAdapters = array('Gd' => 'Gd');
        if (\Pop\Image\Imagick::isInstalled()) {
            $imageAdapters['Imagick'] = 'Imagick';
        }

        $phpLimits = array(
            'post_max_size'       => str_replace(array('M', 'K'), array(' MB', ' KB'), strtoupper(ini_get('post_max_size'))),
            'upload_max_filesize' => str_replace(array('M', 'K'), array(' MB', ' KB'), strtoupper(ini_get('upload_max_filesize'))),
            'max_file_uploads'    => str_replace(array('M', 'K'), array(' MB', ' KB'), strtoupper(ini_get('max_file_uploads')))
        );

        $phpLimitsString = '';
        foreach ($phpLimits as $limit => $limitValue) {
            $phpLimitsString .= '<span style="padding: 0 5px 0 5px;">' . $this->i18n->__(ucwords(str_replace('_', ' ', $limit))) . ': ' . '<strong>' . $limitValue . '</strong></span>';
        }

        $formattedConfig['settings'] = array(
            'site_title'          => $siteTitle,
            'system_title'        => $systemTitle,
            'system_email'        => $systemEmail,
            'reply_email'         => $replyEmail,
            'separator'           => $separator,
            'default_language'    => $lang,
            'datetime_format'     => $datetime,
            'media_allowed_types' => $mediaTypes,
            'media_max_filesize'  => '                    ' . $maxSize . ' &nbsp;&nbsp;&nbsp; [<strong style="color: #f00; padding: 0 0 0 5px;">PHP ' . $this->i18n->__('Limits') . ':</strong> ' . $phpLimitsString . ']',
            'media_actions'       => $mediaConfig,
            'media_image_adapter' => new Element\Select('media_image_adapter', $imageAdapters, $config['media_image_adapter'], '                    '),
            'pagination_limit'    => '                    ' . $pageLimit,
            'pagination_range'    => '                    ' . $pageRange,
            'force_ssl'           => new Element\Radio('force_ssl', array('1' => $this->i18n->__('Yes'), '0' => $this->i18n->__('No')), $config['force_ssl'], '                    '),
            'live'                => new Element\Radio('live', array('1' => $this->i18n->__('Yes'), '0' => $this->i18n->__('No')), $config['live'], '                    ')
        );

        $this->data['config'] = new \ArrayObject($formattedConfig, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Update configuration values
     *
     * @param array $post
     * @return void
     */
    public function update($post)
    {
        unset($post['submit']);

        foreach ($post as $key => $value) {
            if ((strpos($key, 'media') === false) && ($key != 'custom_datetime')) {
                $cfg = Table\Config::findById($key);
                if (($key == 'datetime_format') && ($value == 'custom')) {
                    $value = ($post['custom_datetime'] != '') ? $post['custom_datetime'] : 'F d, Y';
                }

                $cfg->value = htmlentities($value, ENT_QUOTES, 'UTF-8');
                $cfg->update();
            }
        }

        $cfg = Table\Config::findById('media_allowed_types');
        $cfg->value = (isset($post['media_allowed_types'])) ? serialize($post['media_allowed_types']) : serialize(array());
        $cfg->update();

        $cfg = Table\Config::findById('media_max_filesize');
        if (stripos($post['media_max_filesize'], 'MB') !== false) {
            $value = trim(str_replace('MB', '', $post['media_max_filesize'])) . '000000';
        } else if (stripos($post['media_max_filesize'], 'KB') !== false) {
            $value = trim(str_replace('KB', '', $post['media_max_filesize'])) . '000';
        } else {
            $value = (int)trim($post['media_max_filesize']);
        }
        $cfg->value = $value;
        $cfg->update();

        $mediaActions = array();

        foreach ($post as $key => $value) {
            $size = '';
            $action = '0';
            $params = '';
            $quality = '';
            if ((strpos($key, 'media_size_') !== false) && (strpos($key, 'new_') === false)) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $size = $post['media_size_' . $id];
                $action = $post['media_action_' . $id];
                $params = $post['media_params_' . $id];
                $quality = $post['media_quality_' . $id];
            } else if ($key == 'media_size_new_1') {
                $size = $post['media_size_new_1'];
                $action = $post['media_action_new_1'];
                $params = $post['media_params_new_1'];
                $quality = $post['media_quality_new_1'];
            }
            if (($size != '') && ($action != '0') && ($params != '') && ($quality != '')) {
                $mediaActions[$size] = array(
                    'action'  => $action,
                    'params'  => $params,
                    'quality' => (int)$quality
                );
            }
        }

        if (isset($post['rm_media'])) {
            foreach ($post['rm_media'] as $rm) {
                if (isset($mediaActions[$rm])) {
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/media/' . $rm)) {
                        $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/media/' . $rm);
                        $dir->emptyDir(null, true);
                    }
                    unset($mediaActions[$rm]);
                }
            }
        }

        $cfg = Table\Config::findById('media_actions');
        $cfg->value = serialize($mediaActions);
        $cfg->update();

        $cfg = Table\Config::findById('media_image_adapter');
        $cfg->value = $post['media_image_adapter'];
        $cfg->update();

        // Reset base config settings
        Table\Config::setConfig();
    }

    /**
     * Perform update
     *
     * @param array   $post
     * @param boolean $cli
     * @return void
     */
    public function getUpdate($post = array(), $cli = false)
    {
        $docRoot = __DIR__ . '/../../../../../..';

        // If system is writable for updates
        if (!isset($post['submit'])) {
            switch ($post['type']) {
                case 'system':
                    $remoteFile = 'http://update.phirecms.org/system/latest.' . $post['format'];
                    break;
                case 'module':
                    $remoteFile = 'http://update.phirecms.org/modules/' . strtolower($post['name']) . '/latest.' . $post['format'];
                    break;
                case 'theme':
                    $remoteFile = 'http://update.phirecms.org/themes/' . strtolower($post['name']) . '/latest.' . $post['format'];
                    break;
            }

            $remoteContents =@ file_get_contents($remoteFile);
            if ($remoteContents !== false) {
                $localFile  = $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'latest.' . $post['format'];
                file_put_contents($localFile, $remoteContents);

                $arc = new \Pop\Archive\Archive($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'latest.' . $post['format']);
                $arc->extract($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update');
                unlink($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'latest.' . $post['format']);
                $msg = null;

                if ($post['type'] == 'module') {
                    if (file_exists($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $post['name'])) {
                        $dir = new Dir($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $post['name']);
                        $dir->emptyDir(null, true);
                        rename(
                            $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update/' . $post['name'],
                            $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $post['name']
                        );
                    }
                    $msg = $this->i18n->__('The %1 module has been updated.', $post['name']);
                } else if ($post['type'] == 'theme') {
                    if (file_exists($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $post['name'])) {
                        $dir = new Dir($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $post['name']);
                        $dir->emptyDir(null, true);
                        rename(
                            $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update/' . $post['name'],
                            $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $post['name']
                        );
                    }
                    $msg = $this->i18n->__('The %1 theme has been updated.', $post['name']);
                } else if ($post['type'] == 'system') {
                    if ($cli) {
                        rename(
                            $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'phire-cms',
                            $docRoot . DIRECTORY_SEPARATOR . 'phire-cms-new'
                        );

                        $config = Table\Config::findById('updated_on');
                        $config->value = date('Y-m-d H:i:s');
                        $config->update();

                        $msg = $this->i18n->__('The system has been updated.');
                    } else {
                        $time = time();
                        mkdir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . $time);

                        // Move old files into archive folder
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'config',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR . 'config'
                        );
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'module',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR . 'module'
                        );
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'script',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR . 'script'
                        );
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'vendor',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR . 'vendor'
                        );

                        // Move new files into main application path
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'phire-cms' . DIRECTORY_SEPARATOR . 'config',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'config'
                        );
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'phire-cms' . DIRECTORY_SEPARATOR . 'module',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'module'
                        );
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'phire-cms' . DIRECTORY_SEPARATOR . 'script',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'script'
                        );
                        rename(
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'phire-cms' . DIRECTORY_SEPARATOR . 'vendor',
                            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . DIRECTORY_SEPARATOR . 'vendor'
                        );

                        $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'phire-cms');
                        $dir->emptyDir(null, true);

                        $config = Table\Config::findById('updated_on');
                        $config->value = date('Y-m-d H:i:s');
                        $config->update();

                        $msg = $this->i18n->__('The system has been updated.');
                    }
                }

                $this->data['msg'] = '<span style="color: #347703">' . $msg . '</span>';
            } else {
                $this->data['error'] = '<span style="color: #a00b0b">' . $this->i18n->__('The update file was not found.') . '</span>';
            }
        // Else use cURL/FTP
        } else {
            unset($post['submit']);
            $curl = new \Pop\Curl\Curl('http://update.phirecms.org/update.php');
            $curl->setPost(true);
            $curl->setFields($post);

            $curl->execute();

            $response = json_decode($curl->getBody());
            unset($curl);

            if ($response->error == 0) {
                $arc = new \Pop\Archive\Archive($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'latest.' . $post['format']);
                $arc->extract($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update');
                unlink($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'latest.' . $post['format']);
                if ($post['type'] == 'system') {
                    chmod($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update/phire-cms', 0777);
                } else if (file_exists($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update/' . $post['name'])) {
                    chmod($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update/' . $post['name'], 0777);
                }

                $post['complete'] = 1;

                $curl = new \Pop\Curl\Curl('http://update.phirecms.org/update.php');
                $curl->setPost(true);
                $curl->setFields($post);

                $curl->execute();

                $complete = json_decode($curl->getBody());

                if ($complete->error == 0) {
                    switch ($complete->type) {
                        case 'system':
                            $config = Table\Config::findById('updated_on');
                            $config->value = date('Y-m-d H:i:s');
                            $config->update();

                            $msg = $this->i18n->__('The system has been updated.');
                            chmod($docRoot . APP_PATH, 0755);
                            break;
                        case 'module':
                            if (file_exists($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $post['name'])) {
                                $dir = new Dir($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $post['name']);
                                $dir->emptyDir(null, true);
                                rename(
                                    $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update/' . $post['name'],
                                    $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $post['name']
                                );
                            }
                            $msg = $this->i18n->__('The %1 module has been updated.', $complete->name);
                            break;
                        case 'theme':
                            if (file_exists($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $post['name'])) {
                                $dir = new Dir($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $post['name']);
                                $dir->emptyDir(null, true);
                                rename(
                                    $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'update/' . $post['name'],
                                    $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $post['name']
                                );
                            }
                            $msg = $this->i18n->__('The %1 theme has been updated.', $complete->name);
                            break;
                    }
                    $this->data['msg'] = '<span style="color: #347703">' . $msg . '</span>';
                } else {
                    $this->data['error'] = '<span style="color: #a00b0b">' . $complete->message . '</span>';
                }
            } else {
                $this->data['error'] = '<span style="color: #a00b0b">' . $response->message . '</span>';
            }
        }

        $this->postUpdate($post);
    }

    /**
     * Perform post update functions
     *
     * @param array $post
     * @return void
     */
    public function postUpdate($post)
    {
        $docRoot = __DIR__ . '/../../../../../../';
        $update  = null;

        switch ($post['type']) {
            case 'system':
                $update = $docRoot . APP_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'Phire' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'update.php';
                break;
            case 'module':
                $update = $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $post['name'] . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'update.php';
                break;
        }

        if ((null !== $update) && file_exists($update)) {
            include_once $update;
        }
    }

    /**
     * Get date-time format radio element
     *
     * @param  string $datetime
     * @return string
     */
    protected function getDateTimeFormat($datetime)
    {
        $dateTimeOptions = array(
            'F d, Y'       => date('F d, Y'),
            'M j Y'        => date('M j Y'),
            'm/d/Y'        => date('m/d/Y'),
            'Y/m/d'        => date('Y/m/d'),
            'F d, Y g:i A' => date('F d, Y g:i A'),
            'M j Y g:i A'  => date('M j Y g:i A'),
            'm/d/Y g:i A'  => date('m/d/Y g:i A'),
            'Y/m/d g:i A'  => date('Y/m/d g:i A'),
        );

        if (array_key_exists($datetime, $dateTimeOptions)) {
            $dateTimeValue = $datetime;
            $dateTimeOptions['custom'] = '<input type="text" style="padding: 2px;" name="custom_datetime" onkeyup="phire.customDatetime(this.value);" size="10" value="" /> <span id="custom-datetime"></span>';
        } else {
            $dateTimeValue = 'custom';
            $dateTimeOptions['custom'] = '<input type="text" style="padding: 2px;" name="custom_datetime" onkeyup="phire.customDatetime(this.value);" size="10" value="' . $datetime . '" /> <span id="custom-datetime">(' . date($datetime) . ')</span>';
        }

        $datetime = new Element\Radio('datetime_format', $dateTimeOptions, $dateTimeValue, '                    ');
        return str_replace('class="radio-', 'class="radio-block-', (string)$datetime);
    }

    /**
     * Get formatted max file size
     *
     * @param  string $maxSize
     * @return string
     */
    protected function getMaxSize($maxSize)
    {
        if ($maxSize > 999999) {
            $size = round($maxSize / 1000000) . ' MB';
        } else if ($maxSize > 999) {
            $size = round($maxSize / 1000) . ' KB';
        } else {
            $size = $maxSize . ' B';
        }

        return $size;
    }

    /**
     * Get media config settings
     *
     * @param  array $actions
     * @return string
     */
    protected function getMediaConfig($actions)
    {
        $mediaSizes  = '                    <div id="media-sizes">' . PHP_EOL . '                        <strong>' . $this->i18n->__('Size') . ':</strong><br />' . PHP_EOL;
        $mediaActions = '                    <div id="media-actions">' . PHP_EOL . '                        <strong>' . $this->i18n->__('Action') . ':</strong><br />' . PHP_EOL;
        $mediaParams  = '                    <div id="media-params">' . PHP_EOL . '                        <strong>' . $this->i18n->__('Parameters') . ':</strong><br />' . PHP_EOL;
        $mediaQuality = '                    <div id="media-quality">' . PHP_EOL . '                        <strong>' . $this->i18n->__('Quality') . ':</strong><br />' . PHP_EOL;
        $mediaRemove = '                    <div id="media-remove">' . PHP_EOL . '                        <strong>' . $this->i18n->__('Remove') . ':</strong><br />' . PHP_EOL;

        $i = 1;
        $actionOptions = array_merge(array('0' => '----'), self::$mediaActions);
        foreach ($actions as $size => $action) {
            $mediaSizes .= '                        <input type="text" name="media_size_' . $i . '" id="media_size_' . $i . '" value="' . $size . '" style="padding: 2px; display: block;" size="10" />' . PHP_EOL;
            $actionSelect = new Element\Select('media_action_' . $i, $actionOptions, $action['action'], '                        ');
            $actionSelect->setAttributes('style', 'display: block; font-size: 1.1em; margin: 0; padding: 3px 0 3px 0;');
            $mediaActions .= '<div style="height: 28px; padding: 0; margin: 0 0 8px 0;">' . $actionSelect . '</div>';
            $mediaParams .= '                        <input type="text" name="media_params_' . $i . '" id="media_params_' . $i . '" value="' . $action['params'] . '" style="padding: 2px; display: block;" size="10" />' . PHP_EOL;
            $mediaQuality .= '                        <input type="text" name="media_quality_' . $i . '" id="media_quality_' . $i . '" value="' . $action['quality'] . '" style="padding: 2px; display: block;" size="10" />' . PHP_EOL;
            $mediaRemove .= '                        <input type="checkbox" class="rm-media" name="rm_media[]" value="' . $size . '" style="display: block;" />' . PHP_EOL;
            $i++;
        }

        $mediaSizes .= '                        <input type="text" name="media_size_new_1" id="media_size_new_1" value="" style="padding: 2px; display: block;" size="10" />' . PHP_EOL;
        $actionSelect = new Element\Select('media_action_new_1', $actionOptions, null, '                        ');
        $actionSelect->setAttributes('style', 'display: block; font-size: 1.1em; margin: 0; padding: 3px 0 3px 0;');
        $mediaActions .= '<div style="height: 28px; padding: 0; margin: 0 0 8px 0;">' . $actionSelect . '</div>';
        $mediaParams .= '                        <input type="text" name="media_params_new_1" id="media_params_new_1" value="" style="padding: 2px; display: block;" size="10" />' . PHP_EOL;
        $mediaQuality .= '                        <input type="text" name="media_quality_new_1" id="media_quality_new_1" value="" style="padding: 2px; display: block;" size="10" />' . PHP_EOL;

        $mediaSizes  .= '                    </div>' . PHP_EOL;
        $mediaActions .= '                    </div>' . PHP_EOL;
        $mediaParams  .= '                    </div>' . PHP_EOL;
        $mediaQuality .= '                    </div>' . PHP_EOL;
        $mediaRemove .= '                    </div>' . PHP_EOL;

        return $mediaSizes . $mediaActions . $mediaParams . $mediaQuality . $mediaRemove;
    }

    /**
     * Get allowed media types
     *
     * @param  array $allowed
     * @return string
     */
    protected function getMediaAllowedTypes($allowed)
    {
        $mediaTypeValues = self::$mediaTypes;
        $mediaTypes = '                    <div class="media-types-div">' . PHP_EOL;

        $i = 0;
        foreach ($mediaTypeValues as $key => $value) {
            if (($i > 0) && ($i % 6) == 0) {
                $mediaTypes .= '                    </div>' . PHP_EOL;
                $mediaTypes .= '                    <div class="media-types-div">' . PHP_EOL;
            }
            $mediaTypes .= '                        <input type="checkbox" class="check-box" name="media_allowed_types[]" value="' . $key . '"' . (in_array($key, $allowed) ? ' checked="checked"' : null) . ' /><span class="check-span">' . $key . '</span>' . PHP_EOL;
            $i++;
        }

        $mediaTypes .= '                    </div>' . PHP_EOL;
        $mediaTypes .= '                    <div style="clear: left;"><a href="#" onclick="$(\'#config-form\').checkAll(\'media_allowed_types\'); return false;">' . $this->i18n->__('Check All') . '</a> | <a href="#" onclick="$(\'#config-form\').uncheckAll(\'media_allowed_types\'); return false;">' . $this->i18n->__('Uncheck All') . '</a> | <a href="#" onclick="$(\'#config-form\').checkInverse(\'media_allowed_types\'); return false;">' . $this->i18n->__('Inverse') . '</a> <em>(' . $this->i18n->__('Uncheck all to allow any file type.') . '</em></div>' . PHP_EOL;

        return $mediaTypes;
    }

}

