<?php

declare(strict_types=1);

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

// absolute filesystem path to this web root
use FKSDB\Bootstrap;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\Application;

require __DIR__ . '/../vendor/autoload.php';
// load bootstrap file
require __DIR__ . '/../app/Bootstrap.php';


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
