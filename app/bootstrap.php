<?php

use JanTvrdik\Components\DatePicker;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\Routers\Route;
use Nette\Config\Configurator;
use Nette\Forms\Container;

// Load Nette Framework
require LIBS_DIR . '/autoload.php';


// Configure application
$configurator = new Configurator();

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
$container = $configurator->createContainer();

//
// Setup router
//

$container->router[] = new Route('index.php', 'Authentication:login', Route::ONE_WAY);
// Compatibility route
$container->router[] = new Route('web-service/<action>', array(
    'module' => 'Org',
    'presenter' => 'WebService',
    'action' => 'default',
        ), Route::ONE_WAY);
// TODO refactor
$container->router[] = new Route('fksapp/<presenter>/<action>[/<id>]', array
    (
    'presenter' => 'Homepage',
    'action' => 'default',
    'module' => 'Fksapp'
        ));
// General route
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Authentication:login');

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
