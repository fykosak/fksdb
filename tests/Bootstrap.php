<?php

declare(strict_types=1);

namespace FKSDB\Tests;

use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Configurator;
use Nette\Utils\Finder;
use Tester\Environment;
use Tracy\Debugger;

// phpcs:disable
// absolute filesystem path to this web root
define('TESTS_DIR', dirname(__FILE__));

define('TEMP_DIR', TESTS_DIR . '/../temp/tester');
@mkdir(TEMP_DIR);

define('LOG_DIR', TESTS_DIR . '/../temp/tester/log');
@mkdir(LOG_DIR);

// Load Nette Framework
require __DIR__ . '/../vendor/autoload.php';

// phpcs:enable
class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();

        // Enable Nette Debugger for error visualisation & logging
        $configurator->setDebugMode(false);
        Debugger::$logDirectory = LOG_DIR;
        Environment::setup();
        error_reporting(/*~E_USER_DEPRECATED &*/
            ~E_USER_WARNING & ~E_USER_NOTICE & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED
        );

// Enable RobotLoader - this will load all classes automatically
        $configurator->setTempDirectory(TEMP_DIR);
        error_reporting(/*~E_USER_DEPRECATED &*/
            ~E_USER_WARNING & ~E_USER_NOTICE & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED
        );
        $configurator->createRobotLoader()
            ->addDirectory(__DIR__ . '/../app/')
            ->addDirectory(__DIR__.'/../libs/')
            ->addDirectory(__DIR__)
            ->register();

// Create Dependency Injection container from config.neon file
        $configurator->addConfig(__DIR__ . '../app/config/config.neon');
        $configurator->addConfig(__DIR__ . '../app/config/config.local.neon');
        $configurator->addConfig(__DIR__ . '../app/config/config.tester.neon');

        // Load all .neon files in events data directory
        foreach (Finder::findFiles('*.neon')->from(dirname(__FILE__) . '/../data/events') as $filename => $file) {
            $configurator->addConfig($filename);
        }
        // Load .neon files for tests
        foreach (Finder::findFiles('*.neon')->from(dirname(__FILE__) . '/neon') as $filename => $file) {
            $configurator->addConfig($filename);
        }
        return $configurator;
    }
}

// phpcs:disable
// Configure application
$configurator = Bootstrap::boot();

$container = $configurator->createContainer();

// Register addons
Replicator::register();

/* Always acquire locks in the order as below! */
define('LOCK_DB', __DIR__ . '/tmp/database.lock');
define('LOCK_UPLOAD', __DIR__ . '/tmp/upload.lock');
return $container;
// phpcs:enable
/* Allow PSR-4 loading in tests
 * "FKSDB\\Tests\\Events\\": "tests/Events/",
 * "FKSDB\\Tests\\MockEnvironment\\": "tests/MockEnvironment/",
 * "FKSDB\\Tests\\PresentersTests\\": "tests/PresentersTests/"
 */
