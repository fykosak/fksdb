<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests;

// phpcs:disable
$container = require '../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Models\ORM\Services\CountryService;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

class ServiceAddressTest extends TestCase
{

    private AddressHandler $fixture;

    public function __construct(Container $container)
    {
        $this->fixture = new AddressHandler($container);
    }

    /**
     * @dataProvider getPostalCodeData
     */
    public function testStudyYear(string $postalCode, ?int $countryId): void
    {
        $countryData = $this->fixture->inferCountry($postalCode);
        Assert::equal($countryId, $countryData ? $countryData['country_id'] : $countryData);
    }

    public function getPostalCodeData(): array
    {
        return [
            ['01233', CountryService::SLOVAKIA],
            ['67401', CountryService::CZECH_REPUBLIC],
            ['654a5', null],
            ['354 0', null],
        ];
    }
}

// phpcs:disable
$testCase = new ServiceAddressTest($container);
$testCase->run();
// phpcs:enable
