<?php

namespace Smitter\Security;

use DI\Container;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory as ParentHasher;

class PasswordHasherFactory extends ParentHasher implements PasswordHasherFactoryInterface {

    private $hasher;

    public function __construct(Container $container)
    {   
        $definitions = [
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ];
        $parent_hasher = new ParentHasher($definitions); 
        $this->hasher = $parent_hasher->getPasswordHasher($container->get('config.hash_type'));
    }

    public function getHasher() 
    {
        return $this->hasher;
    }

}