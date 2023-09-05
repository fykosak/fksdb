<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\DataTest\Tests\Person\EventCoveringTest;
use FKSDB\Components\DataTest\Tests\Person\GenderFromBornNumberTest;
use FKSDB\Components\DataTest\Tests\Person\ParticipantsDurationTest;
use FKSDB\Components\DataTest\Tests\Person\PersonInfoFieldTest;
use FKSDB\Components\DataTest\Tests\Person\SchoolTest;
use FKSDB\Components\DataTest\Tests\Person\StudyYearTest;
use FKSDB\Components\DataTest\Tests\School\StudyYearFillTest;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\ORMFactory;

/**
 * @phpstan-type TTests array{
 * person:array<string,Test<\FKSDB\Models\ORM\Models\PersonModel>>,
 * school:array<string,Test<\FKSDB\Models\ORM\Models\SchoolModel>>,
 * }
 */
class DataTestFactory
{
    /** @phpstan-var TTests */
    private array $tests;
    private ORMFactory $tableReflectionFactory;

    /**
     * @throws BadTypeException
     */
    public function __construct(ORMFactory $tableReflectionFactory)
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->registersTests();
    }

    /**
     * @throws BadTypeException
     */
    private function registersTests(): void
    {
        $this->tests = [
            'person' => [
                'gender_born_number' => new GenderFromBornNumberTest(),
                'participants_duration' => new ParticipantsDurationTest(),
                'organization_participation' => new EventCoveringTest(),
                'study_year' => new StudyYearTest(),
                'school_study' => new SchoolTest(),
                'phone' => new PersonInfoFieldTest($this->tableReflectionFactory, 'person_info.phone'),
                'phone_parent_d' => new PersonInfoFieldTest(
                    $this->tableReflectionFactory,
                    'person_info.phone_parent_d'
                ),
                'phone_parent_m' => new PersonInfoFieldTest(
                    $this->tableReflectionFactory,
                    'person_info.phone_parent_m'
                ),
            ],
            'school' => [
                'study' => new StudyYearFillTest(),
            ],
        ];
    }

    /**
     * @phpstan-return value-of<TTests>
     */
    public function getTests(string $section): array
    {
        return $this->tests[$section] ?? [];
    }
}
