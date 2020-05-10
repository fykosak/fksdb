<?php

use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Configurator;
use Nette\Database\Context;
use Nette\Utils\Finder;
use Tracy\Debugger;

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
require LIBS_DIR . '/../vendor/autoload.php';
require LIBS_DIR . '/autoload.php';

define('CONFIG_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'config');


// Configure application
$configurator = new Configurator();

$configurator->setDebugMode(false);
Debugger::$logDirectory = LOG_DIR;
Tester\Environment::setup();

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(TEMP_DIR);
error_reporting(~E_USER_DEPRECATED & ~E_USER_WARNING & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
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
}

// Load .neon files for tests
foreach (Finder::findFiles('*.neon')->from(dirname(__FILE__) . '/neon') as $filename => $file) {
    $configurator->addConfig($filename, Configurator::NONE);
}
$container = $configurator->createContainer();

// Register addons
Replicator::register();

/* Always acquire locks in the order as below! */
define('LOCK_DB', __DIR__ . '/tmp/database.lock');
define('LOCK_UPLOAD', __DIR__ . '/tmp/upload.lock');
return $container;
