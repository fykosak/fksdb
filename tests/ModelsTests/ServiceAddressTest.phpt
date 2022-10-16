<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests;

// phpcs:disable
$container = require '../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\ORM\Services\Exceptions\InvalidPostalCode;
use FKSDB\Models\ORM\Services\AddressService;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

class ServiceAddressTest extends TestCase
{
    private AddressService $fixture;

    public function __construct(AddressService $service)
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

// phpcs:disable
$testCase = new ServiceAddressTest($container->getByType(AddressService::class));
$testCase->run();
// phpcs:enable
