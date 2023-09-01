<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting;

use FKSDB\Models\DataTesting\Tests\ModelPerson\PersonTest;
use FKSDB\Models\DataTesting\Tests\ModelPerson\StudyYearTest;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\ORMFactory;

class DataTestingFactory
{
    /** @phpstan-var PersonTest[][] */
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
            new Tests\ModelPerson\GenderFromBornNumberTest(),
            new Tests\ModelPerson\ParticipantsDurationTest(),
            new Tests\ModelPerson\EventCoveringTest(),
            new StudyYearTest(),
        ];
        foreach (['person_info.phone', 'person_info.phone_parent_d', 'person_info.phone_parent_m'] as $fieldName) {
            $tests[] = new Tests\ModelPerson\PersonInfoFieldTest($this->tableReflectionFactory, $fieldName);
        }
        $this->tests['person'] = $tests;
    }

    /**
     * @phpstan-return PersonTest[]
     */
    public function getTests(string $section): array
    {
        return $this->tests[$section] ?? [];
    }
}
