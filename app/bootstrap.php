<?php

use Authorization\ACLExtension;
use FKSDB\Events\EventsExtension;
use FKSDB\Config\Extensions\DBReflectionExtension;
use FKSDB\Config\Extensions\NavigationExtension;
use FKSDB\Config\Extensions\PaymentExtension;
use FKSDB\Config\Extensions\RouterExtension;
use FKSDB\Config\Extensions\StalkingExtension;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Config\Configurator;
use Nette\DI\Compiler;
use Nette\Utils\Finder;

// Load Nette Framework
require LIBS_DIR . '/../vendor/autoload.php';
require LIBS_DIR . '/autoload.php';

define('CONFIG_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config');

// Configure application
$configurator = new Configurator();

// Enable Nette Debugger for error visualisation & logging
$configurator->enableDebugger(dirname(__FILE__) . '/../log');
error_reporting(~E_USER_DEPRECATED & ~E_USER_WARNING & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(dirname(__FILE__) . '/../temp');
$configurator->createRobotLoader()
    ->addDirectory(APP_DIR)
    ->addDirectory(LIBS_DIR)
    ->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(CONFIG_DIR . '/config.neon', Configurator::NONE);
$configurator->addConfig(CONFIG_DIR . '/config.local.neon', Configurator::NONE);

// Load all .neon files in events data directory
foreach (Finder::findFiles('*.neon')->from(dirname(__FILE__) . '/../data/events') as $filename => $file) {
    $configurator->addConfig($filename, Configurator::NONE);
}

$container = $configurator->createContainer();


//
// Register addons
//
Replicator::register();

//
// Configure and run the application!
$container->application->run();
