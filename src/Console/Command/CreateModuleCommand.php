<?php

namespace Smitter\Console\Command;

use DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModuleCommand extends Command
{
    // the name of the command (the part after "php sunshine")
    protected static $defaultName = 'create:module';

    protected $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure(): void
    {
        $this->setDescription('Creates a new module');
        $this->addArgument('moduleName');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ... put here the code to create the user

        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable
        $output->writeln([
            '==================',
            '==================',
            '==== Smile :) ====', 
            '==================',
            '==================',
            'Preparing module for you',
            '',
        ]);

        $moduleName = $input->getArgument('moduleName');

        // Create module directory
        $this->createModuleDirectory($moduleName);

        // Create basic structure
        $this->createBasicStructure($moduleName);

        // retrieve the argument value using getArgument()
        $output->writeln('Module: ' . $moduleName . ' created successfully' );

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }

    private function createModuleDirectory($moduleName) 
    {
        $expModuleArr = explode(".", $moduleName);

        $wrapper = 'app/'.ucfirst($expModuleArr[0]) . '/';
        mkdir($wrapper);

        $module = 'app/'.ucfirst($expModuleArr[0]) . '/' . ucfirst($expModuleArr[1]) . '/';
        mkdir($module);
    }

    private function createBasicStructure($moduleName) 
    {   
        $expModuleArr = explode(".", $moduleName);
        $module = 'app/'.ucfirst($expModuleArr[0]) . '/' . ucfirst($expModuleArr[1]) . '/';
        $name = implode("_", $expModuleArr);

        // Register module name
        $registry_data = file_get_contents('app/register.php');
        if (empty($registry_data)) $registry_data = [];
        $registry_data[ $name ] = [ 'active' => true, 'parent' => false ]; 
        file_put_contents('app/register.php', $registry_data);

        // Create Controller dir
        mkdir($module . 'Controller');

        // Create Model dir
        mkdir($module . 'Model');

        // Create Design dir
        mkdir($module . 'Design');
        mkdir($module . 'Design' . '/' . 'layouts' );
        mkdir($module . 'Design' . '/' . 'templates' );

        // Create basic config files
        fopen($module . 'module.php', 'w');
        fopen($module . 'routes.php', 'w');
    }
}