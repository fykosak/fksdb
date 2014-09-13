<?php

$container = require 'bootstrap.php';

use Nette\Database\Connection;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

class ModelPersonHistoryTest extends TestCase {

    /**
     * @var ServicePersonHistory
     */
    private $service;

    function __construct(ServicePersonHistory $service) {
        $this->service = $service;
    }

    public function testSimple() {
        $fixture = $this->service->createNew(array(
            'person_id' => 1,
            'ac_year' => 2000,
            'school_id' => 123,
            'class' => '3.B',
            'study_year' => 3,
        ));

        $extrapolated = $fixture->extrapolate(2001);
        Assert::same(2001, $extrapolated->ac_year);
        Assert::same(123, $extrapolated->school_id);
        Assert::same('4.B', $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    public function testNull() {
        $fixture = $this->service->createNew(array(
            'person_id' => 1,
            'ac_year' => 2000,
            'school_id' => 123,
            'class' => null,
            'study_year' => 3,
        ));

        $extrapolated = $fixture->extrapolate(2001);
        Assert::same(2001, $extrapolated->ac_year);
        Assert::same(123, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

}

$testCase = new ModelPersonHistoryTest($container->getService('ServicePersonHistory'));
$testCase->run();
