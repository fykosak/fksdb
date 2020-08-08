<?php

namespace FKSDB\Tests\ModelTests\PersonHistory;
/** @var Container $container */
$container = require '../../bootstrap.php';

use FKSDB\ORM\DbNames;
use FKSDB\ORM\Services\ServicePersonHistory;
use FKSDB\Tests\ModelTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class Extrapolate extends DatabaseTestCase {

    /**
     * @var ServicePersonHistory
     */
    private $service;
    /** @var int */
    private $personId;
    /** @var int */
    private $schoolId;

    /**
     * Extrapolate constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->service = $container->getByType(ServicePersonHistory::class);
    }

    protected function setUp() {
        parent::setUp();
        $this->personId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Testerovič',
            'other_name' => 'Tester',
            'gender' => 'M',
        ]);
        $addressId = $this->insert(DbNames::TAB_ADDRESS, [
            'first_row' => 'PU',
            'second_row' => 'PU',
            'target' => 'PU',
            'city' => 'PU',
            'postal_code' => '02001',
            'region_id' => '1',
        ]);
        $this->schoolId = $this->insert(DbNames::TAB_SCHOOL, [
            'name_full' => 'GPU',
            'name' => 'GPU',
            'name_abbrev' => 'GPU',
            'address_id' => $addressId,
            'email' => 'mail@example.com',
            'ic' => '0',
            'izo' => '0',
            'active' => 1,
        ]);
    }

    public function testSimple() {
        $fixture = $this->service->createNewModel([
            'person_id' => $this->personId,
            'ac_year' => 2000,
            'school_id' => $this->schoolId,
            'class' => '3.B',
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(2001);
        Assert::same(2001, $extrapolated->ac_year);
        Assert::same($this->schoolId, $extrapolated->school_id);
        Assert::same('4.B', $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    public function testNull() {
        $fixture = $this->service->createNewModel([
            'person_id' => $this->personId,
            'ac_year' => 2000,
            'school_id' => $this->schoolId,
            'class' => null,
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(2001);
        Assert::same(2001, $extrapolated->ac_year);
        Assert::same($this->schoolId, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    /**
     * @dataProvider getStudyYearData
     */
    public function testStudyYear(int $from, int $step, $to) {
        $fixture = $this->service->createNewModel([
            'person_id' => $this->personId,
            'ac_year' => 2000,
            'school_id' => $this->schoolId,
            'class' => null,
            'study_year' => $from,
        ]);

        $extrapolated = $fixture->extrapolate(2000 + $step);
        Assert::same(2000 + $step, $extrapolated->ac_year);
        Assert::same($this->schoolId, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same($to, $extrapolated->study_year);
    }

    public function getStudyYearData(): array {
        return [
            [6, 1, 7],
            [9, 1, 1],
            [4, 1, null],
            [9, 5, null],
            [6, 7, 4],
        ];
    }
}

$testCase = new Extrapolate($container);
$testCase->run();
