<?php

use Nette\Configurator;
use Nette\Utils\Finder;

define('LIBS_DIR', __DIR__ . '/../libs');
define('APP_DIR', __DIR__ . '/../app');
define('CONFIG_DIR', APP_DIR . '/config');

// Load Nette Framework
require LIBS_DIR . '/../vendor/autoload.php';
require LIBS_DIR . '/autoload.php';

// Configure application
$configurator = new \Nette\Configurator();

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

return $configurator->createContainer();
