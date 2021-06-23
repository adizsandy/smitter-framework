<?php

namespace Smitter\Persistance;

use DI\Container;
use Smitter\Persistance\Persistance;
use Smitter\Persistance\PersistanceFactoryInterface;

class PersistanceFactory implements PersistanceFactoryInterface {

    private $connection;
    private $container;

    public function __construct($connection = 'default', Container $container)
    {
        $this->container = $container;

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
        $this->connection = $this->container->get('config.connection_detail');
    } 
}