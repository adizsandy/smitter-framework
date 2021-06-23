<?php

namespace Smitter\Environment;

final class Environment {

    private static function setEnvironment($env) 
    {
        if ($env == 'prod') {
            ini_set('error_reporting', 0);
        } else {
            ini_set('error_reporting', E_ALL);
        }
    }

    private static function setDebug($debug) 
    {
        // In dev...
    }

    public static function set($env = 'local', $debug = true) 
    {
        self::setEnvironment($env);
        self::setDebug($debug);
    }
}