<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\PersonHistory;

/** @var Container $container */
$container = require '../../Bootstrap.php';

use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelSchool;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use FKSDB\Models\ORM\Services\ServiceSchool;
use FKSDB\Models\YearCalculator;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class Extrapolate extends DatabaseTestCase
{
    private ServicePersonHistory $service;
    private ModelPerson $person;
    private ModelSchool $school;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->service = $container->getByType(ServicePersonHistory::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->person = $this->getContainer()->getByType(ServicePerson::class)->createNewModel([
            'family_name' => 'Testerovič',
            'other_name' => 'Tester',
            'gender' => 'M',
        ]);
        $address = $this->getContainer()->getByType(ServiceAddress::class)->createNewModel([
            'first_row' => 'PU',
            'second_row' => 'PU',
            'target' => 'PU',
            'city' => 'PU',
            'postal_code' => '02001',
            'region_id' => '1',
        ]);
        $this->school = $this->getContainer()->getByType(ServiceSchool::class)->createNewModel([
            'name_full' => 'GPU',
            'name' => 'GPU',
            'name_abbrev' => 'GPU',
            'address_id' => $address->address_id,
            'email' => 'mail@example.com',
            'ic' => '0',
            'izo' => '0',
            'active' => 1,
        ]);
    }

    public function testSimple(): void
    {
        $fixture = $this->service->createNewModel([
            'person_id' => $this->person->person_id,
            'ac_year' => YearCalculator::getCurrentAcademicYear(),
            'school_id' => $this->school->school_id,
            'class' => '3.B',
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(YearCalculator::getCurrentAcademicYear() + 1);
        Assert::same(YearCalculator::getCurrentAcademicYear() + 1, $extrapolated->ac_year);
        Assert::same($this->school->school_id, $extrapolated->school_id);
        Assert::same('4.B', $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    public function testNull(): void
    {
        $fixture = $this->service->createNewModel([
            'person_id' => $this->person->person_id,
            'ac_year' => YearCalculator::getCurrentAcademicYear(),
            'school_id' => $this->school->school_id,
            'class' => null,
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(YearCalculator::getCurrentAcademicYear() + 1);
        Assert::same(YearCalculator::getCurrentAcademicYear() + 1, $extrapolated->ac_year);
        Assert::same($this->school->school_id, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    /**
     * @dataProvider getStudyYearData
     */
    public function testStudyYear(int $from, int $step, ?int $to): void
    {
        $fixture = $this->service->createNewModel([
            'person_id' => $this->person->person_id,
            'ac_year' => YearCalculator::getCurrentAcademicYear(),
            'school_id' => $this->school->school_id,
            'class' => null,
            'study_year' => $from,
        ]);

        $extrapolated = $fixture->extrapolate(YearCalculator::getCurrentAcademicYear() + $step);
        Assert::same(YearCalculator::getCurrentAcademicYear() + $step, $extrapolated->ac_year);
        Assert::same($this->school->school_id, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same($to, $extrapolated->study_year);
    }

    public function getStudyYearData(): array
    {
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
