<?php

namespace FKSDB\DataTesting;

use FKSDB\DBReflection\DBReflectionFactory;
use FKSDB\DataTesting\Tests\Person\PersonTest;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Services\ServiceContest;

/**
 * Class DataTestingFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DataTestingFactory {
    /** @var PersonTest[][] */
    private array $tests = [];

    private ServiceContest $serviceContest;

    private DBReflectionFactory $tableReflectionFactory;

    public function __construct(ServiceContest $serviceContest, DBReflectionFactory $tableReflectionFactory) {
        $this->serviceContest = $serviceContest;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->registersTests();
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    private function registersTests(): void {
        $tests = [
            new Tests\Person\GenderFromBornNumberTest(),
            new Tests\Person\ParticipantsDurationTest(),
            new Tests\Person\EventCoveringTest(),
        ];
        foreach (['person_info.phone', 'person_info.phone_parent_d', 'person_info.phone_parent_m'] as $fieldName) {
            $tests[] = new Tests\Person\PersonInfoFieldTest($this->tableReflectionFactory, $fieldName);
        }
        $this->tests['person'] = $tests;
    }

    /**
     * @param string $section
     * @return PersonTest[]
     */
    public function getTests(string $section): array {
        return $this->tests[$section] ?? [];
    }
}
