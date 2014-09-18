<?php

use Authorization\ACLExtension;
use Events\EventsExtension;
use FKS\Config\Extensions\NavigationExtension;
use FKS\Config\Extensions\RouterExtension;
use JanTvrdik\Components\DatePicker;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Config\Configurator;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Container;
use Nette\Utils\Finder;
use Tester\Environment;

// absolute filesystem path to this web root
define('TESTS_DIR', dirname(__FILE__));

// absolute filesystem path to the application root
define('APP_DIR', TESTS_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', TESTS_DIR . '/../libs');

define('TEMP_DIR', TESTS_DIR . '/../temp/tester');
@mkdir(TEMP_DIR);

define('LOG_DIR', TESTS_DIR . '/../temp/tester/log');
@mkdir(LOG_DIR);

// Load Nette Framework
require LIBS_DIR . '/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tester/Tester/bootstrap.php';

define('CONFIG_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'config');

// Configure application
$configurator = new Configurator();
$configurator->onCompile[] = function ($configurator, $compiler) {
    $compiler->addExtension('fksrouter', new RouterExtension());
    $compiler->addExtension('acl', new ACLExtension());
    $compiler->addExtension('navigation', new NavigationExtension());
    $compiler->addExtension('events', new EventsExtension(CONFIG_DIR . '/events.neon'));
};

$configurator->setDebugMode(false);
Debugger::$logDirectory = LOG_DIR;


// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(dirname(__FILE__) . '/../temp');
$configurator->createRobotLoader()
        ->addDirectory(APP_DIR)
        ->addDirectory(LIBS_DIR)
        ->addDirectory(TESTS_DIR)
        ->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(CONFIG_DIR . '/config.neon', Configurator::NONE);
$configurator->addConfig(CONFIG_DIR . '/config.local.neon', Configurator::NONE);
$configurator->addConfig(CONFIG_DIR . '/config.tester.neon', Configurator::NONE);

// Load all .neon files in events data directory
foreach (Finder::findFiles('*.neon')->from(dirname(__FILE__) . '/../data/events') as $filename => $file) {
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

define('LOCK_DB', __DIR__ . '/tmp/database.lock');
return $container;

