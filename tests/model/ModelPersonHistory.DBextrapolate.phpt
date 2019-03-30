<?php

$container = require '../bootstrap.php';

use FKSDB\ORM\Services\ServicePerson;
use Nette\DI\Container;
use Tester\Assert;

class ModelPersonHistoryTest extends DatabaseTestCase {

    /**
     * @var ServicePerson
     */
    private $service;

    function __construct(ServicePerson $service, Container $container) {
        parent::__construct($container);
        $this->service = $service;
    }

    public function testNull() {
        $personId = $this->createPerson('Student', 'Pilný');
        $this->createPersonHistory($personId, 2000, 1, 1);

        $person = $this->service->findByPrimary($personId);
        $extrapolated = $person->getHistory(2001, true);

        Assert::same(2001, $extrapolated->ac_year);
        Assert::same(1, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(2, $extrapolated->study_year);
    }

}

$testCase = new ModelPersonHistoryTest($container->getService('ServicePerson'), $container);
$testCase->run();
