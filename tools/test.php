<?php

declare(strict_types=1);

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Tests\Person\EmptyPerson;
use Nette\DI\Container;

ini_set('memory_limit', '4096M');
try {
    /** @var Container $container */
    $container = require __DIR__ . '/bootstrap.php';
    set_time_limit(-1);

    $service = $container->getByType(PersonService::class);
    $test = new EmptyPerson($container);
    $logger = new TestLogger();
    foreach ($service->getTable() as $person) {
        $test->run($logger, $person, '');
    }
} catch (\Throwable $exception) {
    echo get_class($exception) . "\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString() . "\n";
}
