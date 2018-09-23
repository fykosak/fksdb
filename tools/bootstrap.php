<?php

use Authorization\ACLExtension;
use Events\EventsExtension;
use FKSDB\Config\Extensions\NavigationExtension;
use FKSDB\Config\Extensions\RouterExtension;
use JanTvrdik\Components\DatePicker;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Config\Configurator;
use Nette\Forms\Container;
use Nette\Utils\Finder;


define('LIBS_DIR', __DIR__ . '/../libs');
define('APP_DIR', __DIR__ . '/../app');
define('CONFIG_DIR', APP_DIR . '/config');

// Load Nette Framework
require LIBS_DIR . '/autoload.php';



// Configure application
$configurator = new Configurator();
$configurator->onCompile[] = function ($configurator, $compiler) {
            $compiler->addExtension('fksrouter', new RouterExtension());
            $compiler->addExtension('acl', new ACLExtension());
            $compiler->addExtension('navigation', new NavigationExtension());
            $compiler->addExtension('events', new EventsExtension(CONFIG_DIR . '/events.neon'));
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
$configurator->addConfig(CONFIG_DIR . '/config.neon', Configurator::NONE);
$configurator->addConfig(CONFIG_DIR . '/config.local.neon', Configurator::NONE);

// Load all .neon files in events data directory
foreach (Finder::findFiles('*.neon')->from(dirname(__FILE__) . '/../data/events') as $filename => $file) {
    $configurator->addConfig($filename, Configurator::NONE);
};

$container = $configurator->createContainer();


return $container;
