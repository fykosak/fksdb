<?php


namespace FKSDB\ValidationTest;

/**
 * Class ValidationFactory
 * @package FKSDB\ValidationTest
 */
class ValidationFactory {
    /**
     * @var ValidationTest[]
     */
    private $tests = [];

    public function __construct() {
        $this->registersTests();
    }

    /**
     *
     */
    private function registersTests() {
        $this->tests = [
            new Tests\GenderFromBornNumber(),
            new Tests\ParticipantsDuration(),
            new Tests\PhoneNumber(),
        ];
    }

    /**
     * @return ValidationTest[]
     */
    public function getTests(): array {
        return $this->tests;
    }
}
