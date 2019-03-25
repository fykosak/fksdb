<?php


namespace FKSDB\ValidationTest;

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
     * ValidationFactory constructor.
     * @param ServiceContest $serviceContest
     */
    public function __construct(ServiceContest $serviceContest) {
        $this->serviceContest = $serviceContest;
        $this->registersTests();
    }

    /**
     *
     */
    private function registersTests() {
        $this->tests = [
            new Tests\GenderFromBornNumber(),
            new Tests\ParticipantDuration\FykosParticipantDuration($this->serviceContest),
            new Tests\ParticipantDuration\VyfukParticipantDuration($this->serviceContest),
            new Tests\Phone\PhoneNumber(),
            new Tests\Phone\PhoneParentDNumber(),
            new Tests\Phone\PhoneParentMNumber(),
        ];
    }

    /**
     * @return ValidationTest[]
     */
    public function getTests(): array {
        return $this->tests;
    }
}
