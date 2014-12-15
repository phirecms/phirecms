<?php

namespace Phire;

use Pop\Db\Record;
use Pop\File\Dir;
use Pop\Http\Request;
use Pop\Http\Response;

class Application extends \Pop\Application
{

    /**
     * Phire version
     */
    const VERSION = '2.0.0b';

    public function init()
    {
        // Set the database
        if ($this->services->isAvailable('database')) {
            Record::setDb($this->getService('database'));
        }

        // Add route params for the controllers
        if (null !== $this->router) {
            $this->router->addRouteParams(
                '*', [
                    'services' => $this->services,
                    'request'  => new Request(),
                    'response' => new Response()
                ]
            );
        }

        // Session check
        $this->on('app.dispatch.pre', function(Application $application){
            $sess   = $application->getService('session');
            $action = $application->router()->getRouteMatch()->getAction();

            if (isset($sess->user) && (($action == 'login') || ($action == 'register') ||
                    ($action == 'verify') || ($action == 'forgot'))) {
                Response::redirect(BASE_PATH . APP_URI);
                exit();
            } else if (!isset($sess->user) && (($action != 'login') && ($action != 'register') &&
                    ($action != 'unsubscribe') && ($action != 'verify') && ($action != 'forgot') && (null !== $action))) {
                Response::redirect(BASE_PATH . APP_URI . '/login');
                exit();
            }
        });

        $this->loadAssets(__DIR__ . '/../data/assets', 'phire');

        return parent::init();
    }

    public function loadAssets($from, $to)
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets') &&
            is_writable($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets')) {
            $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . $to;
            if (!file_exists($dir)) {
                mkdir($dir);
                chmod($dir, 0777);

                copy($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/index.html', $dir . '/index.html');
                chmod($dir . '/index.html', 0777);
            }

            $assetDirs = array(
                'css', 'css/fonts', 'styles', 'styles/fonts', 'style', 'style/fonts', // CSS folders
                'js', 'scripts', 'script', 'scr',                                     // JS folders
                'image', 'images', 'img', 'imgs'                                      // Image folders
            );

            foreach ($assetDirs as $aDir) {
                if (file_exists($from . '/' . $aDir)) {
                    if (!file_exists($dir . '/' . $aDir)) {
                        mkdir($dir . '/' . $aDir);
                        chmod($dir . '/' . $aDir, 0777);
                        copy($dir . '/index.html', $dir . '/' . $aDir . '/index.html');
                        chmod($dir . '/' . $aDir . '/index.html', 0777);
                    }
                    $d = new Dir($from . '/' . $aDir, false, false, false);
                    foreach ($d->getFiles() as $file) {
                        if (!file_exists($dir . '/' . $aDir . '/' . $file) ||
                            (file_exists($dir . '/' . $aDir . '/' . $file) &&
                                (filemtime($from . '/' . $aDir . '/' . $file) > filemtime($dir . '/' . $aDir . '/' . $file)))) {
                            copy($from . '/' . $aDir . '/' . $file, $dir . '/' . $aDir . '/' . $file);
                            chmod($dir . '/' . $aDir . '/' . $file, 0777);
                        }
                    }
                }
            }
        }
    }

}