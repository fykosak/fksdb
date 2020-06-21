<?php

$container = require '../bootstrap.php';

use FKSDB\ORM\Services\ServiceAddress;
use Tester\Assert;
use Tester\TestCase;

class ServiceAddressTest extends TestCase {

    /**
     * @var ServiceAddress
     */
    private $fixture;

    /**
     * ServiceAddressTest constructor.
     * @param ServiceAddress $service
     */
    public function __construct(ServiceAddress $service) {
        $this->fixture = $service;
    }

    /**
     * @dataProvider getPostalCodeData
     */
    public function testStudyYear($postalCode, $region) {
        if ($region === null) {
            Assert::exception(function () use ($postalCode) {
                $this->fixture->inferRegion($postalCode);
            }, 'InvalidPostalCode');
        } else {
            $inferredRegion = $this->fixture->inferRegion($postalCode);
            Assert::equal($region, $inferredRegion);
        }
    }

    public function getPostalCodeData(): array {
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
