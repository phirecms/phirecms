<?php

namespace Phire;

use Phire\Table;
use Pop\Archive\Archive;
use Pop\File\Dir;

class Updater extends AbstractUpdater
{

    /**
     * Method to run one-click update
     * @throws \Pop\Archive\Exception
     */
    public function runUpdate()
    {
        file_put_contents(
            __DIR__ . '/../..' . CONTENT_PATH . '/updates/phirecms.zip',
            fopen('http://updates.phirecms.org/releases/phire/phirecms.zip', 'r')
        );

        $basePath = realpath(__DIR__ . '/../..' . CONTENT_PATH . '/updates/');

        $archive  = new Archive($basePath . '/phirecms.zip');
        $archive->extract($basePath);
        unlink(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phirecms.zip');

        $json = json_decode(stream_get_contents(fopen('http://updates.phirecms.org/releases/phire/phire.json', 'r')), true);

        foreach ($json as $file) {
            echo 'Updating: ' . $file . '<br />' . PHP_EOL;
            copy(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phire-cms/' . $file, __DIR__ . '/../' . $file);
        }

        $dir = new Dir(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phire-cms/');
        $dir->emptyDir(true);
    }

}
