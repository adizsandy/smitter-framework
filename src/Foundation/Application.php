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

    private $abstract_bindings = [];

    private $key_bindings = [];

    public function __construct($basepath = null)
    {   
        // Set root path of project
        $this->setBasePath($basepath); 

        // Register core service bindings
        // Abstract and Key Bindings
        $this->registerCoreServiceBindings();

        // Setup container
        $this->setupContainer();

        // Set key bindings with container
        $this->setKeyBindings();

        // Set path bindings with container
        $this->setPathBindings();

        // Set config bindings with container
        $this->setConfigBindings();

        // Set collection bindings with container
        $this->setCollectionBindings();
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
        $containerBuilder->addDefinitions($this->abstract_bindings);
        $this->container = $containerBuilder->build();
    }

    protected function setKeyBindings() 
    {
        if ( count($this->key_bindings) > 0 ) {
            foreach ( $this->key_bindings as $key => $instance ) {
                $this->container->set($key, $instance);
            }
        } 
    }

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
        
        if ( empty($services) || count($services) == 0 ) { 
            $services = [
                // Default services 
                'request' => [ 
                    'abstract' => Symfox\Request\RequestInterface::class,
                    'concrete' => \Symfox\Request\RequestAction::class, 
                ],  
                'response' => [ 
                    'abstract' => \Symfox\Response\ResponseInterface::class,
                    'concrete' => \Symfox\Response\ResponseAction::class, 
                ],
                'filehandler' => [ 
                    'abstract' => \Symfox\Filehandler\FilehandlerInterface::class,
                    'concrete' => \Symfox\Filehandler\Filehandler::class 
                ], 
                'mailer' => [ 
                    'abstract' => \Symfox\Mail\MailerInterface::class,
                    'concrete' => \Symfox\Mail\Mailer::class 
                ], 
                'db' => [ 
                    'abstract' => \Symfox\Persistance\PersistanceFactoryInterface::class,
                    'concrete' => \Symfox\Persistance\PersistanceFactory::class
                ], 
                'session' => [ 
                    'abstract' => Symfony\Component\HttpFoundation\Session\SessionInterface::class,
                    'concrete' => Symfony\Component\HttpFoundation\Session\Session::class 
                ], 
                'hasher' => [ 
                    'abstract' => Symfox\Security\PasswordHasherFactoryInterface::class,
                    'concrete' => Symfox\Security\PasswordHasherFactory::class
                ],
                'csrf' => [ 
                    'abstract' => Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class,
                    'concrete' => Symfony\Component\Security\Csrf\CsrfTokenManager::class
                ],
                'auth' => [ 
                    'abstract' => \Symfox\Security\AuthInterface::class,
                    'concrete' => \Symfox\Security\Auth::class 
                ],  
                'viewcache' => [ 
                    'abstract' => \Symfox\View\ViewInterface::class,
                    'concrete' => \Symfox\View\ViewCache::class 
                ], 
                'view' => [ 
                    'concrete' => \Symfox\View\View::class 
                ],    
                'matcher' => [ 
                    'abstract' => \Symfox\Match\MatchFactoryInterface::class,
                    'concrete' => \Symfox\Match\MatchFactory::class 
                ],   
                'dispatcher' => [ 
                    'abstract' => Symfony\Component\EventDispatcher\EventDispatcherInterface::class,
                    'concrete' => \Symfox\Dispatch\Dispatch::class 
                ],
                'control' => [ 
                    'abstract' => Symfony\Component\HttpKernel\Controller\ControllerResolverInterface::class,
                    'concrete' => Symfony\Component\HttpKernel\Controller\ControllerResolver::class 
                ],
                'kernel' => [  
                    'concrete' => Boot\Kernel::class 
                ]
            ];
        }

        foreach ( $services as $key => $definition ) {
            
            // Get Resolved Instance 
            $instance = $this->resolveArgumentsInjection($definition['concrete']);

            // Abstract Assignment to Implementation if applicable
            if (isset($definition['abstract']) && !empty($definition['abstract'])) { 
                $this->abstract_bindings[$definition['abstract']] = $instance; 
            } 

            // Set Key Bindings for Services
            $this->key_bindings[$key] = $instance;   
        }
         
    }

    protected function resolveArgumentsInjection($class) 
    {
        $ref = new ReflectionClass($class);
        $constructor = $ref->getConstructor();
        $args = $constructor->getParameters();
        $mod_args = [];
        if (count($args) > 0) {
            foreach ($args as $a) {
                if (!empty($a->getType())) {
                    $name = $a->getType()->getName();
                    if (array_key_exists($name, $this->abstract_bindings)){
                        $mod_args[] = $this->abstract_bindings[$name];
                    }
                } else {
                    $mod_args[] = $a->getDefaultValue();
                }
            }
        }
        return $ref->newInstanceArgs($mod_args);
    }

    public function make() 
    { 
        return $this->container;
    }
}
