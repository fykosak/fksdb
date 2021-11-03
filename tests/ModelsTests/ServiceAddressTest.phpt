<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests;

/** @var Container $container */
$container = require '../Bootstrap.php';

use FKSDB\Models\ORM\Services\Exceptions\InvalidPostalCode;
use FKSDB\Models\ORM\Services\ServiceAddress;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

class ServiceAddressTest extends TestCase
{

    private ServiceAddress $fixture;

    /**
     * ServiceAddressTest constructor.
     * @param ServiceAddress $service
     */
    public function __construct(ServiceAddress $service)
    {
        $this->fixture = $service;
    }

    /**
     * @dataProvider getPostalCodeData
     */
    public function testStudyYear(string $postalCode, ?int $region): void
    {
        if ($region === null) {
            Assert::exception(function () use ($postalCode) {
                $this->fixture->inferRegion($postalCode);
            }, InvalidPostalCode::class);
        } else {
            $inferredRegion = $this->fixture->inferRegion($postalCode);
            Assert::equal($region, $inferredRegion);
        }
    }

    public function getPostalCodeData(): array
    {
        return [
            ['01233', 2],
            ['67401', 3],
            ['654a5', null],
            ['354 0', null],
        ];
    }
}

$testCase = new ServiceAddressTest($container->getByType(ServiceAddress::class));
$testCase->run();
