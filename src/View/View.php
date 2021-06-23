<?php

namespace Smitter\View;

use Symfony\Component\HttpFoundation\Session\SessionInterface; 
use Smitter\Request\RequestInterface; 
use Smitter\Response\ResponseInterface;
use Smitter\Auth\BaseAuthInterface as AuthInterface;
use Smitter\View\ViewInterface;

class View {

	private $layout;
	private $template;
	private $data;  
	private $response; 
	private $viewcache;
	private $request;
	public $session; 

	public function __construct(SessionInterface $session, RequestInterface $request,  ViewInterface $viewcache, ResponseInterface $response )
	{ 	
		$this->session = $session; 
		$this->response = $response; 
		$this->viewcache = $viewcache; 
		$this->request = $request; 
	}

	protected function resolve ( $template = null, $options = null, $layout = null ) 
	{
		if ( $this->viewcache->validCacheAvailable() ) {
			$content = $this->viewcache->getCacheContent();
		} else {
			if ( isset($layout) && ! empty($layout) )  { 
				$this->setLayout($layout);
			}
			if ( isset($template) && ! empty($template) ) { 
				$this->setTemplate($template); 
			}
			if ( isset($options) && ! empty($options) ) { 
				$this->setData($options); 
			}
			$content = $this->generateContent();
			if ($this->viewcache->cacheallowed) {
				$this->viewcache->setCacheContent($content);
			} 
		} 
		return $content;
	}

	protected function generateContent() 
	{
		ob_start(); 
		if(!empty($this->getData())) extract($this->getData(), EXTR_SKIP); 
		require $this->getTemplate() . '.php'; 
		$templateContent = ob_get_contents(); 
		ob_end_clean();  

		ob_start(); 
		extract(['content' => $templateContent], EXTR_SKIP); 
		if (! empty($this->getLayout())) require $this->getLayout() . '.php'; 
		$final_content = ob_get_contents(); 
		ob_end_clean(); 

		return $final_content;
	}

	public function setlayout($layout, $module = null) 
	{	
		$this->setModuleDir($module);
		$this->layout = $this->getModuleDir() . '/Design/layouts/' . $layout; 
		return $this;
	}

	protected function getLayout() 
	{
		return $this->layout;
	}

	public function setTemplate($template, $module = null) 
	{	
		$this->setModuleDir($module);
		$this->template = $this->getModuleDir() . '/Design/templates/' . $template;
		return $this;
	}

	protected function getTemplate() 
	{
		return $this->template;
	}

	protected function setModuleDir($module = null) 
	{
		if (empty($module)) { // Current Request Module
			$module = container()->get('collection.route_attributes')[ $this->request->getPathInfo() ]['module'];  
		}  
		$this->module = container()->get('path.module') . implode("/",explode("_", $module));
	}

	protected function getModuleDir() 
	{
		return $this->module;
	}

	public function setData($data) 
	{	
		if ( !empty($data) ) $this->data = $data; 
		return $this;
	}

	protected function getData() 
	{
		return $this->data;
	}

	public function setCache($status = true) 
	{
		$this->viewcache->cacheallowed = $status;
		return $this;
	}

	public function getIncludes($file, $module) 
	{	
		ob_start();
		if(!empty($this->getData())) extract($this->getData(), EXTR_SKIP); 
		$include_file = container()->get('path.module') . implode("/",explode("_", $module)) . '/Design/includes/' . $file . '.php';
		if (file_exists($include_file)) include $include_file;
		$includes = ob_get_contents();
		ob_end_clean(); 
		return $includes;
	}

	public function render ( $template = null, $options = null, $layout = null )
	{	
		$content = $this->resolve($template, $options, $layout);
		return $this->response->output($content);
	}

} 