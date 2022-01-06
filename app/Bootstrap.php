<?php

declare(strict_types=1);

namespace FKSDB;

use Nette\Configurator;
use Nette\Utils\Finder;

// Load Nette Framework
require LIBS_DIR . '/../vendor/autoload.php';

class Bootstrap {
    public static function boot(): Configurator {
        $configurator = new Configurator();

        // Enable Nette Debugger for error visualisation & logging
        $configurator->enableTracy(__DIR__ . '/../log');
        $configurator->enableDebugger(__DIR__ . '/../log');
        error_reporting(~E_USER_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

        $configurator->setTempDirectory(__DIR__ . '/../temp');
        // Enable RobotLoader - this will load all classes automatically
        $configurator->createRobotLoader()
            ->addDirectory(APP_DIR)
            ->addDirectory(LIBS_DIR)
            ->register();

        // Create Dependency Injection container from config.neon file
        $configurator->addConfig(APP_DIR . '/config/config.neon');
        $configurator->addConfig(APP_DIR . '/config/config.local.neon');

        // Load all .neon files in events data directory
        foreach (Finder::findFiles('*.neon')->from(dirname(__FILE__) . '/../data/events') as $filename => $file) {
            $configurator->addConfig($filename);
        }
        return $configurator;
    }
}
