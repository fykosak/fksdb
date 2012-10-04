<?php


// Load Nette Framework
require LIBS_DIR . '/autoload.php';


// Configure application
$configurator = new NConfigurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode(NConfigurator::AUTO);
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
$container->router[] = new NRoute('index.php', 'Homepage:default', NRoute::ONE_WAY);
$container->router[] = new NRoute('<presenter>/<action>[/<id>]', 'Homepage:default', NRoute::SECURED);


// Configure and run the application!
$container->application->run();
