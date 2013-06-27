<?php
use Nette\Application\Routers\Route;
use Nette\Config\Configurator;

// Load Nette Framework
require LIBS_DIR . '/autoload.php';


// Configure application
$configurator = new Configurator();

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode(Configurator::AUTO);
$configurator->enableDebugger(dirname(__FILE__) . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(dirname(__FILE__) . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(dirname(__FILE__) . '/config/config.neon');
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Dashboard:default', Route::ONE_WAY);
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Dashboard:default');

// Register addons
\Kdyby\Extension\Forms\Replicator\Replicator::register();

// Configure and run the application!
$container->application->run();
