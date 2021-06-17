<?php

namespace Symfox\Match;

use DI\Container;
use Symfox\Match\RouteRegistry; 
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class MatchFactory implements MatchFactoryInterface {

    private $registry;

    public function __construct(Container $container)
    {
        $this->registry = new RouteRegistry($container); 
    }

    public function getUrlMatcher() 
    {   
        return new UrlMatcher($this->registry->getCollection(), new RequestContext());
    }

}