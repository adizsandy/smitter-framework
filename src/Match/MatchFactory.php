<?php

namespace Symfox\Match;

use Symfox\Match\RouteRegistry; 
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class MatchFactory implements MatchFactoryInterface {

    private $registry;

    public function __construct()
    {
        $this->registry = new RouteRegistry(); 
    }

    public function getUrlMatcher() 
    {   
        return new UrlMatcher($this->registry->getCollection(), new RequestContext());
    }

}