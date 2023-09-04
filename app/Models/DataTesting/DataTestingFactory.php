<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting;

use FKSDB\Models\DataTesting\Tests\Person\SchoolTest;
use FKSDB\Models\DataTesting\Tests\Person\StudyYearTest;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ORMFactory;

/**
 * @phpstan-type TTests array{person:array<string,Test<PersonModel>>}
 */
class DataTestingFactory
{
    /** @phpstan-var array<string,array<string,Test<PersonModel>>> */
    private array $tests = [];
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
        $tests = [
            'gender_born_number' => new Tests\Person\GenderFromBornNumberTest(),
            'participants_duration' => new Tests\Person\ParticipantsDurationTest(),
            'organization_participation' => new Tests\Person\EventCoveringTest(),
            'study_year' => new StudyYearTest(),
            'school_study' => new SchoolTest(),
        ];
        foreach (['phone', 'phone_parent_d', 'phone_parent_m'] as $fieldName) {
            $tests[$fieldName] = new Tests\Person\PersonInfoFieldTest(
                $this->tableReflectionFactory,
                'person_info.' . $fieldName
            );
        }
        $this->tests['person'] = $tests;
    }

    /**
     * @phpstan-return value-of<TTests>
     * @phpstan-param key-of<TTests> $section
     */
    public function getTests(string $section): array
    {
        return $this->tests[$section] ?? [];
    }
}
