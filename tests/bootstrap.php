<?php

use Authorization\ACLExtension;
use Events\EventsExtension;
use FKSDB\Config\Extensions\DBReflectionExtension;
use FKSDB\Config\Extensions\NavigationExtension;
use FKSDB\Config\Extensions\PaymentExtension;
use FKSDB\Config\Extensions\RouterExtension;
use FKSDB\Config\Extensions\StalkingExtension;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\Responses\TextResponse;
use Nette\Config\Configurator;
use Nette\Forms\Container;
use Nette\Utils\Finder;
use Tester\Assert;
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
error_reporting(~E_USER_DEPRECATED & ~E_USER_WARNING);

//require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tester/Tester/bootstrap.php';

define('CONFIG_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'config');

// Configure application
$configurator = new Configurator();
$configurator->onCompile[] = function ($configurator, $compiler) {
    $compiler->addExtension('fksrouter', new RouterExtension());
    $compiler->addExtension('acl', new ACLExtension());
    $compiler->addExtension('navigation', new NavigationExtension());
    $compiler->addExtension('stalking', new StalkingExtension());
    $compiler->addExtension('events', new EventsExtension(CONFIG_DIR . '/events.neon'));
    $compiler->addExtension('payment', new PaymentExtension());
    $compiler->addExtension('DBReflection', new DBReflectionExtension());
};

$configurator->setDebugMode(false);
Debugger::$logDirectory = LOG_DIR;


// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(TEMP_DIR);
error_reporting(~E_USER_DEPRECATED & ~E_USER_WARNING);
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


//
// Register addons
//
error_reporting(~E_USER_DEPRECATED & ~E_USER_WARNING);
Replicator::register();

function dumpResponse(TextResponse $response) {
    $source = $response->getSource();
    $html = (string)$source;

    /* Use assert so that expected is dumped as a string to file. */
    Assert::equal('', $html);
}
/*
Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
    return $container[$name] = new DatePicker($label);
});*/

/* Always acquire locks in the order as below! */
define('LOCK_DB', __DIR__ . '/tmp/database.lock');
define('LOCK_UPLOAD', __DIR__ . '/tmp/upload.lock');
return $container;

