<?php

namespace Symfox\Persistance;

use Symfox\Persistance\Persistance;
use Symfox\Persistance\PersistanceFactoryInterface;

class PersistanceFactory implements PersistanceFactoryInterface {

    private $connection;

    public function __construct(string $connection = 'default')
    {
        // Set Connection
        $this->setConnection($connection);
    }

    public function getPersistance() 
    {   
        return ( new Persistance($this->connection) )->getPersistance();
    }

    public function connection($connection_name = 'default') 
	{
		$this->setConnection($connection_name); 
		return $this;
	}

    protected function setConnection($connection = 'default') 
    {
        $this->connection = container()->get('config.connection_detail');
    } 
}