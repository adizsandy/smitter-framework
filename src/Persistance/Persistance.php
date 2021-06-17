<?php

namespace Symfox\Persistance;

use Boot\Env\Configurator;
use Illuminate\Database\Capsule\Manager as Capsule; 

class Persistance {

	protected $conn;
	protected $persistance;

	public function __construct(array $connection)
	{ 	
		$this->setConnection($connection);
		$this->setPersistance(); 
	}

	protected function setConnection($connection) 
	{	 
		$this->conn = $connection;
	}

	protected function getConnection() 
	{
		return $this->conn;
	}

	protected function setPersistance()
	{ 	
		$capsule = new Capsule();
		$capsule->addConnection($this->getConnection()); 
		$capsule->bootEloquent();
		$this->persistance = $capsule->getDatabaseManager();
	}

	public function connection($connection_name = 'default') 
	{
		$this->setConnection($connection_name);
		$this->setPersistance();
		return $this;
	}

	public function getPersistance() 
	{
		return $this->persistance;
	}

} 