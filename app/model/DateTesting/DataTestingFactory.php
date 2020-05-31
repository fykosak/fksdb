<?php

namespace FKSDB\DataTesting;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\DataTesting\Tests\Person\PersonTest;
use FKSDB\ORM\Services\ServiceContest;
use Nette\Application\BadRequestException;

/**
 * Class DataTestingFactory
 * *
 */
class DataTestingFactory {
    /**
     * @var PersonTest[][]
     */
    private array $tests = [];

    private ServiceContest $serviceContest;

    private TableReflectionFactory $tableReflectionFactory;

    /**
     * DataTestingFactory constructor.
     * @param ServiceContest $serviceContest
     * @param TableReflectionFactory $tableReflectionFactory
     * @throws BadRequestException
     */
    public function __construct(ServiceContest $serviceContest, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceContest = $serviceContest;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->registersTests();
    }

    /**
     * @throws BadRequestException
     */
    private function registersTests(): void {
        $tests = [
            new Tests\Person\GenderFromBornNumberTest(),
            new Tests\Person\ParticipantsDurationTest(),
            new Tests\Person\EventCoveringTest(),
        ];
        foreach (['phone', 'phone_parent_d', 'phone_parent_m'] as $fieldName) {
            $tests[] = new Tests\Person\PersonInfoFieldTest($this->tableReflectionFactory, $fieldName);
        }
        $this->tests['person'] = $tests;
    }

    /**
     * @param string $section
     * @return PersonTest[]
     */
    public function getTests(string $section): array {
        if (isset($this->tests[$section])) {
            return $this->tests[$section];
        }
        return [];
    }
}
