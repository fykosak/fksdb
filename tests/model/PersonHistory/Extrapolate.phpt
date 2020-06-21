<?php

namespace FKSDB\Tests\PersonHistory;

$container = require '../../bootstrap.php';

use FKSDB\ORM\Services\ServicePersonHistory;
use Tester\Assert;
use Tester\TestCase;

class Extrapolate extends TestCase {

    /**
     * @var ServicePersonHistory
     */
    private $service;

    /**
     * ModelPersonHistoryTest constructor.
     * @param ServicePersonHistory $service
     */
    public function __construct(ServicePersonHistory $service) {
        $this->service = $service;
    }

    public function testSimple() {
        $fixture = $this->service->createNew([
            'person_id' => 1,
            'ac_year' => 2000,
            'school_id' => 123,
            'class' => '3.B',
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(2001);
        Assert::same(2001, $extrapolated->ac_year);
        Assert::same(123, $extrapolated->school_id);
        Assert::same('4.B', $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    public function testNull() {
        $fixture = $this->service->createNew([
            'person_id' => 1,
            'ac_year' => 2000,
            'school_id' => 123,
            'class' => null,
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(2001);
        Assert::same(2001, $extrapolated->ac_year);
        Assert::same(123, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    /**
     * @dataProvider getStudyYearData
     */
    public function testStudyYear(int $from, int $step, $to) {
        $fixture = $this->service->createNew([
            'person_id' => 1,
            'ac_year' => 2000,
            'school_id' => 123,
            'class' => null,
            'study_year' => $from,
        ]);

        $extrapolated = $fixture->extrapolate(2000 + $step);
        Assert::same(2000 + $step, $extrapolated->ac_year);
        Assert::same(123, $extrapolated->school_id);
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

$testCase = new Extrapolate($container->getByType(ServicePersonHistory::class));
$testCase->run();
