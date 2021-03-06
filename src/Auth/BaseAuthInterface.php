<?php

namespace Smitter\Auth;

interface BaseAuthInterface {

    public function login();

    public function logout();

    public function loginAndRemember();

    public function entity();

    public function entityExists();
}