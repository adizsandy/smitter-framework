<?php

namespace Symfox\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestAction extends Request implements RequestInterface {

    public function __construct()
    {
        parent::__construct($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
    }

    public function allPost() 
    {
        return $this->request->all();
    }

    public function allQuery() 
    {
        return $this->query->all();
    }

    public function allCookie() 
    {
        return $this->cookies->all();
    }

    public function allFiles() 
    {
        return $this->files;
    }

    public function isPost() 
    {
        return $this->getMethod() == 'POST';
    }

    public function isGet() 
    {
        return $this->getMethod() == 'GET';
    }

}