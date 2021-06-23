<?php

namespace Smitter\View;

use Smitter\Cache\Cache;
use Smitter\Request\RequestInterface;

class ViewCache implements ViewInterface {
 
    private $request;
    public $cacheallowed = false;

    public function __construct(RequestInterface $request)
    {   
        if ($_SERVER['APP_ENV'] == 'local') $this->cacheallowed = false; 
        $this->request = $request;
    }

    public function validCacheAvailable() 
	{	
		if ($this->cacheallowed) {
			$file = 'view/' . $this->getCacheKey() . '.php';
			if ( Cache::has($file) ) {
				return true;
			}
		} 
		return false;  
	} 

	protected function getCacheContent() 
	{	
		$file = 'view/' . $this->getCacheKey() . '.php';
		return Cache::get($file);
	}

	protected function setCacheContent($content) 
	{
		$file = 'view/' . $this->getCacheKey() . '.php';
		Cache::put($file, $content);
		return;
	}

	protected function getCacheKey() 
	{
		return md5($this->request->getPathInfo() . ':' . container()->get('config.app_key'));
	}

}