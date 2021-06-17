<?php

namespace Symfox\Session;

//use Symfony\Component\HttpFoundation\Session\Session as Parent_Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Session {

	public function getSession(SessionInterface $session) 
	{
		return $session;
	}

} 