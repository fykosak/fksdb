<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting;

use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\DataTesting\Tests\ModelPerson\PersonTest;
use FKSDB\Models\Exceptions\BadTypeException;

class DataTestingFactory {
    /** @var PersonTest[][] */
    private array $tests = [];
    private ORMFactory $tableReflectionFactory;

    /**
     * @throws BadTypeException
     */
    public function __construct( ORMFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->registersTests();
    }

    /**
     * @throws BadTypeException
     */
    private function registersTests(): void {
        $tests = [
            new Tests\ModelPerson\GenderFromBornNumberTest(),
            new Tests\ModelPerson\ParticipantsDurationTest(),
            new Tests\ModelPerson\EventCoveringTest(),
        ];
        foreach (['person_info.phone', 'person_info.phone_parent_d', 'person_info.phone_parent_m'] as $fieldName) {
            $tests[] = new Tests\ModelPerson\PersonInfoFieldTest($this->tableReflectionFactory, $fieldName);
        }
        $this->tests['person'] = $tests;
    }

    /**
     * @return PersonTest[]
     */
    public function getTests(string $section): array {
        return $this->tests[$section] ?? [];
    }
}
