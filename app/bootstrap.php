<?php

use Events\MachineExtension;
use FKS\Config\Extensions\NavigationExtension;
use FKS\Config\Extensions\RouterExtension;
use JanTvrdik\Components\DatePicker;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Config\Configurator;
use Nette\Forms\Container;
use Nette\Utils\Finder;

// Load Nette Framework
require LIBS_DIR . '/autoload.php';


// Configure application
$configurator = new Configurator();
$configurator->onCompile[] = function ($configurator, $compiler) {
            $compiler->addExtension('fksrouter', new RouterExtension());
            $compiler->addExtension('navigation', new NavigationExtension());
            $compiler->addExtension('events', new MachineExtension());
        };

// Enable Nette Debugger for error visualisation & logging
$configurator->enableDebugger(dirname(__FILE__) . '/../log');


// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(dirname(__FILE__) . '/../temp');
$configurator->createRobotLoader()
        ->addDirectory(APP_DIR)
        ->addDirectory(LIBS_DIR)
        ->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(dirname(__FILE__) . '/config/config.neon', Configurator::NONE);
$configurator->addConfig(dirname(__FILE__) . '/config/config.local.neon', Configurator::NONE);

// Load all .neon files in events data directory
foreach (Finder::findFiles('*.neon')->in(dirname(__FILE__) . '/../data/events') as $filename => $file) {
    $configurator->addConfig($filename, Configurator::NONE);
};

$container = $configurator->createContainer();


//
// Register addons
//
Replicator::register();


Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
            return $container[$name] = new DatePicker($label);
        });

//
// Configure and run the application!
$container->application->run();
