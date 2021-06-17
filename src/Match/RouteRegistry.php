<?php

namespace Symfox\Match;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class RouteRegistry {

	private $collection;

	public function __construct()
	{	
		$this->collection = new RouteCollection;  
		$this->registerCustomRoutes();
	}

	public function registerCustomRoutes () 
	{
		$custom_routes = container()->get('collection.route');
		if (! empty($custom_routes) && count($custom_routes) > 0 ) {
			foreach ($custom_routes as $name => $info) {
				$this->collection->add($name, new Route($info[0], array('_controller' => $info[1])));
			}
		}
	}

	public function getCollection () 
	{
		return $this->collection;
	}

} 



