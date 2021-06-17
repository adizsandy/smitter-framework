<?php

namespace Symfox\Foundation;

use DI\ContainerBuilder;
use ReflectionClass;

/**
 * Application/Service Container
 */
class Application {

    private $basepath;

    private $route_attributes;

    private $container; 

    //private $abstract_bindings = [];

    //private $key_bindings = [];

    public function __construct($basepath = null)
    {   
        // Set root path of project
        $this->setBasePath($basepath); 

        // Setup container
        $this->setupContainer();

        // Set path bindings with container
        $this->setPathBindings();

        // Set config bindings with container
        $this->setConfigBindings();

        // Set collection bindings with container
        $this->setCollectionBindings();

        // Register core service bindings
        // Abstract and Key Bindings
        $this->registerCoreServiceBindings();

        // Set key bindings with container
        //$this->setKeyBindings();
    }

    private function setBasePath($basepath) 
    {
        $this->basepath = dirname( $basepath . '/../');
    }

    public function modulePath() 
    {
        return $this->basepath . DIRECTORY_SEPARATOR . 'app/modules/';
    }

    public function cachePath() 
    {
        return $this->basepath . DIRECTORY_SEPARATOR . 'storage/cache/';
    }

    public function viewCacheTime() 
    {
        $cachetime = require $this->basepath . DIRECTORY_SEPARATOR . 'config/cache.php';
        return $cachetime['cache_time']; 
    }

    public function hashType() 
    {
        $hash = require $this->basepath . DIRECTORY_SEPARATOR . 'config/hash.php';
        return $hash['hash_type'];
    }

    public function connectionDetails ($connection = 'default') 
    {
        return ( require $this->basepath . DIRECTORY_SEPARATOR . 'config/database.php' ) [ $connection ]; 
    }

    public function mailTransportCollection() 
    {
        return require $this->basepath . DIRECTORY_SEPARATOR . 'config/mail.php'; 
    }

    public function moduleCollection() 
    {
        return require $this->basepath . DIRECTORY_SEPARATOR . 'app/modules/register.php';
    }

    public function eventCollection() 
    {
        return []; // In dev
    }

    public function listenerCollection() 
    {
        return []; // In dev
    }

    public function routeCollection() 
    {   
        $route_collection = [];
        $collection = $this->moduleCollection();
        if ( !empty($collection) && count($collection) > 0 ) {
            foreach ( $collection as $name => $module ) {
                if ($module['active']) { // If a module is active, then only it can be registered
                    
                    // Get basic info about a module  
                    $module_prefix = strtolower($name); 
                    $module_dir = implode('/', explode("_", $name ) );
                    $module_path = $this->modulePath() . $module_dir;
                    // $all_info = require $module_path . '/module.php';   
                    
                    // Get module declarations
                    $declarations = include $module_path . '/module.php'; 

                    // Get registred routes
                    $routes = include $module_path . '/routes.php'; 
                    
                    // Set route mappings
                    if ( ! empty($routes) && count($routes) > 0 ) {
                        foreach ( $routes as $route_name => $detail ) { 
                             
                            // Prepare prefixed url path
                            $final_url_path = '/'.rtrim(ltrim($declarations['url_prefix'].ltrim($detail[0], '/'), '/'), '/');
                            
                            // Prepare prefixed controller
                            $final_controller = str_replace("/", "\\", "App/Module/". $module_dir . '/Controller/');
                            
                            // add to collection
                            $route_collection[ $module_prefix . '_' . $route_name ] = [ $final_url_path, $final_controller . $detail[1] ];

                            // Pushing collections
                            $this->route_attributes [ $final_url_path ] = [
                                'route_name' => $route_name,
                                'module' => $name,
                                'controller' => $detail[1],
                                'controller_path' => $final_controller
                            ];
                        }
                    }   
                } 
            }
        }
        return $route_collection; 
    }

    private function setupContainer() 
    {
        $containerBuilder = new ContainerBuilder; 
        //$containerBuilder->addDefinitions($this->abstract_bindings);
        $this->container = $containerBuilder->build();
    }

    // protected function setKeyBindings() 
    // {
    //     if ( count($this->key_bindings) > 0 ) {
    //         foreach ( $this->key_bindings as $key => $instance ) {
    //             $this->container->set($key, $instance);
    //         }
    //     } 
    // }

    protected function setPathBindings() 
    {
        $this->container->set('path.module', $this->modulePath());
        $this->container->set('path.cache', $this->cachePath());
        $this->container->set('path.base', $this->basepath); 
        $this->container->set('path.cache_dir', 'storage/cache/'); 
    }

    protected function setConfigBindings() 
    {   
        $this->container->set('config.app_key', $_SERVER['APP_KEY']);
        $this->container->set('config.view_cache_time', $this->viewCacheTime());
        $this->container->set('config.connection_detail', $this->connectionDetails());
        $this->container->set('config.hash_type', $this->hashType()); 
    }

    protected function setCollectionBindings() 
    {
        $this->container->set('collection.mail', $this->mailTransportCollection());
        $this->container->set('collection.module', $this->moduleCollection());
        $this->container->set('collection.event', $this->eventCollection());
        $this->container->set('collection.listener', $this->listenerCollection());
        $this->container->set('collection.route', $this->routeCollection());
        $this->container->set('collection.route_attributes', $this->route_attributes);
    }

    protected function registerCoreServiceBindings() 
    {   
        $services = ( require $this->basepath . DIRECTORY_SEPARATOR . 'config/app.php' ) ['services'];

        foreach ( $services as $key => $definition ) {

            // Refreh container instance over container
            $this->container->set('DI\Container', $this->container);
        
            $ref = new ReflectionClass($definition['concrete']);
            $constructor = $ref->getConstructor();
            $args = $constructor->getParameters();

            // List filtered arguments from constructor
            $filtered_args = []; 
            if (count($args) > 0) { 
                foreach ($args as $a) {
                    if (empty($a->getType())) {
                        $filtered_args[] = $a->getDefaultValue();
                    } else {
                        $name = $a->getType()->getName(); 
                        if ( $this->container->has($name) ) { 
                            $filtered_args[] = $this->container->get($name);
                        } else {
                            $filtered_args[] = $a->getDefaultValue();
                        }
                    } 
                }
            }
            
            // Create instance from given arguments
            $instance = $ref->newInstanceArgs($filtered_args);

            // Set Namespace Bindings for Services
            // OPTIONAL: If abstract definitions are provided
            if (isset($definition['abstract']) && !empty($definition['abstract'])) {  
                $this->container->set($definition['abstract'], $instance);
            } 

            // Set Key Bindings for Services
            $this->container->set($key, $instance);   
        }
    }

    public function make() 
    { 
        return $this->container;
    }
}
