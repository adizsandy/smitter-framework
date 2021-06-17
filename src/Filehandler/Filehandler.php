<?php

namespace Symfox\Filehandler;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local; 

class Filehandler extends Filesystem implements FilehandlerInterface {

    public function __construct()
    {   
        parent::__construct(new Local(container()->get('path.base')));
    } 
}