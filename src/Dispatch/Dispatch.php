<?php

namespace Symfox\Dispatch;

use DI\Container;
use Symfony\Component\EventDispatcher\EventDispatcher; 

class Dispatch extends EventDispatcher {

	private $events = [];
	private $listeners = [];
	private $container;

	public function __construct(Container $container)
	{	
		$this->container = $container;
		$this->setEvents();
		$this->setListeners(); 
	}

	public function getEvents() 
	{
		if (empty($this->events)) $this->setEvents();
		return $this->events;
	}

	protected function setEvents() 
	{
		$this->events = $this->container->get('collection.event'); 
	}

	public function getListenerList($event_key) 
	{	
		if (empty($this->listeners)) $this->setListeners();
		return $this->listeners;
	}

	protected function setListeners() 
	{
		$this->listeners = $this->container->get('collection.listener'); ;
	}

	public function resolve($request, $response)
	{
		if ( !empty($this->getEvents()) && count($this->getEvents()) > 0 ) {
            foreach( $this->getEvents() as $event_key => $event ) {
				$listeners = $this->getListenerList($event_key);
                if ( count($listeners) > 0 ){
                    foreach ( $listeners as $listener ) {
                        $this->addSubscriber(new $listener());
                    }
                    $this->dispatch($event_key, new $event($response, $request));
                } 
            }
        }
		return;
	}
} 