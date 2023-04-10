<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\PersonHistory;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\ORM\Services\CountryService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\DI\Container;
use Tester\Assert;

class Extrapolate extends DatabaseTestCase
{
    private PersonHistoryService $service;
    private PersonModel $person;
    private SchoolModel $school;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->service = $container->getByType(PersonHistoryService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->person = $this->container->getByType(PersonService::class)->storeModel([
            'family_name' => 'Testerovič',
            'other_name' => 'Tester',
            'gender' => 'M',
        ]);
        $address = $this->container->getByType(AddressService::class)->storeModel([
            'first_row' => 'PU',
            'second_row' => 'PU',
            'target' => 'PU',
            'city' => 'PU',
            'postal_code' => '02001',
            'country_id' => CountryService::SLOVAKIA,
        ]);
        $this->school = $this->container->getByType(SchoolService::class)->storeModel([
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
        $fixture = $this->service->storeModel([
            'person_id' => $this->person->person_id,
            'ac_year' => ContestYearService::getCurrentAcademicYear(),
            'school_id' => $this->school->school_id,
            'class' => '3.B',
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(ContestYearService::getCurrentAcademicYear() + 1);
        Assert::same(ContestYearService::getCurrentAcademicYear() + 1, $extrapolated->ac_year);
        Assert::same($this->school->school_id, $extrapolated->school_id);
        Assert::same('4.B', $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    public function testNull(): void
    {
        $fixture = $this->service->storeModel([
            'person_id' => $this->person->person_id,
            'ac_year' => ContestYearService::getCurrentAcademicYear(),
            'school_id' => $this->school->school_id,
            'class' => null,
            'study_year' => 3,
        ]);

        $extrapolated = $fixture->extrapolate(ContestYearService::getCurrentAcademicYear() + 1);
        Assert::same(ContestYearService::getCurrentAcademicYear() + 1, $extrapolated->ac_year);
        Assert::same($this->school->school_id, $extrapolated->school_id);
        Assert::same(null, $extrapolated->class);
        Assert::same(4, $extrapolated->study_year);
    }

    /**
     * @dataProvider getStudyYearData
     */
    public function testStudyYear(int $from, int $step, ?int $to): void
    {
        $fixture = $this->service->storeModel([
            'person_id' => $this->person->person_id,
            'ac_year' => ContestYearService::getCurrentAcademicYear(),
            'school_id' => $this->school->school_id,
            'class' => null,
            'study_year' => $from,
        ]);

        $extrapolated = $fixture->extrapolate(ContestYearService::getCurrentAcademicYear() + $step);
        Assert::same(ContestYearService::getCurrentAcademicYear() + $step, $extrapolated->ac_year);
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

// phpcs:disable
$testCase = new Extrapolate($container);
$testCase->run();
// phpcs:enable
