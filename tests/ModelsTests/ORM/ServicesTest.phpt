<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\ORM;

/** @var Container $container */
$container = require '../../Bootstrap.php';

use Fykosak\NetteORM\AbstractService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class ServicesTest extends DatabaseTestCase
{

    /**
     * @dataProvider getServices
     */
    public function testServices(string $service): void
    {
        Assert::noError(function () use ($service) {
            $this->getContainer()->getService($service);
        });
    }

    public function getServices(): array
    {
        return array_map(function (string $service): array {
            return [$service];
        }, $this->getContainer()->findByType(AbstractService::class));
    }
}

$testCase = new ServicesTest($container);
$testCase->run();
