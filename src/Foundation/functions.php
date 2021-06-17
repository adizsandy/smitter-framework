<?php

/**
 * Collection of Service Factory Functions
 * Public API
 */

function container() 
{
    global $app;
    return $app;
}

function db() 
{   
    return ( container()->get('db') )->getPersistance();
}

function response() 
{
    return container()->get('response');
}

function auth() 
{
    return container()->get('auth');
}

function session() 
{
    return container()->get('session');
}

function filehandler() 
{
    return container()->get('filehandler');
}

function mailer() 
{
    return container()->get('mailer');
}

function cache() 
{
    return container()->get('cache');
}

function view() 
{
    return container()->get('view');
}

function request() 
{
    return container()->get('request');
}

function csrf_token($name = null) 
{   
    $name = empty($name) ? 'symfox' : $name;
    return auth()->csrf()->getToken($name)->__toString();
}
