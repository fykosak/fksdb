<?php


namespace FKSDB\ValidationTest;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Services\ServiceContest;

/**
 * Class ValidationFactory
 * @package FKSDB\ValidationTest
 */
class ValidationFactory {
    /**
     * @var ValidationTest[]
     */
    private $tests = [];
    /**
     * @var ServiceContest
     */
    private $serviceContest;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * ValidationFactory constructor.
     * @param ServiceContest $serviceContest
     * @param TableReflectionFactory $tableReflectionFactory
     * @throws \Nette\Application\BadRequestException
     */
    public function __construct(ServiceContest $serviceContest, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceContest = $serviceContest;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->registersTests();
    }

    /**
     *
     * @throws \Nette\Application\BadRequestException
     */
    private function registersTests() {
        $this->tests = [
            new Tests\GenderFromBornNumber(),
            new Tests\ParticipantDuration\FykosParticipantDuration($this->serviceContest),
            new Tests\ParticipantDuration\VyfukParticipantDuration($this->serviceContest),
            new Tests\Phone\PhoneNumber(),
            new Tests\Phone\PhoneParentDNumber(),
            new Tests\Phone\PhoneParentMNumber(),
            new PersonInfoFieldTest($this->tableReflectionFactory, 'health_insurance'),
        ];
    }

    /**
     * @return ValidationTest[]
     */
    public function getTests(): array {
        return $this->tests;
    }
}
