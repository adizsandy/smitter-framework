<?php

namespace Symfox\Auth;

class BaseAuth implements BaseAuthInterface {

    protected $model;
    protected $table;
    protected $identifier;
    protected $password;
    protected $retrievable;
    protected $sessionKeyPrefix;
    protected $redirectUrl;

    public function login() 
    {

    }

    public function logout() {}

    public function loginAndRemember() {}

    public function entity() {}

    public function entityExists() {}
}