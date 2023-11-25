<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\ORM;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Fykosak\NetteORM\Service\Service;
use Tester\Assert;

class ServicesTest extends DatabaseTestCase
{
    /**
     * @dataProvider getServices
     */
    public function testServices(string $service): void
    {
        Assert::noError(function () use ($service) {
            $this->container->getService($service);
        });
    }

    public function getServices(): array
    {
        return array_map(function (string $service): array {
            return [$service];
        }, $this->container->findByType(Service::class));
    }
}

// phpcs:disable
$testCase = new ServicesTest($container);
$testCase->run();
// phpcs:enable
