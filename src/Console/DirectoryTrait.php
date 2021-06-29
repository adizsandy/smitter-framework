<?php

namespace Smitter\Console;

trait DirectoryTrait {

    public function createDirectory($dirname) 
    {
        $filehandler = $this->container->get('filehandler');
        if (!$filehandler->has($dirname)) {
            $filehandler->write($dirname, 1);
            $dirindex = 1;
        } else {
            $dirindex = $filehandler->read($dirname);
        }
        if ($filehandler->has($dirindex)) {
            $filesCount = count($filehandler->listContents($dirindex));
            if ($filesCount > 0) {
                $dirindex++;
                $filehandler->createDir($dirindex);
            }
        } else {
            $filehandler->createDir($dirindex);
        }
    }
}