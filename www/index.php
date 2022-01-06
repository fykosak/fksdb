<?php

declare(strict_types=1);

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

// absolute filesystem path to this web root
use FKSDB\Bootstrap;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\Application;

define('WWW_DIR', dirname(__FILE__));

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../libs');

// load bootstrap file
require APP_DIR . '/Bootstrap.php';

// inicializace prostředí + získání objektu Nette\Configurator
$configurator = Bootstrap::boot();
// vytvoření DI kontejneru
$container = $configurator->createContainer();
// Register addons
Replicator::register();
// DI kontejner vytvoří objekt Nette\Application\Application
$application = $container->getByType(Application::class);
// spuštění Nette aplikace
$application->run();
