<?php

namespace FKSDB\ValidationTest;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class ValidationTest
 */
abstract class ValidationTest {
    /**
     * @param ModelPerson $person
     * @return ValidationLog
     */
    abstract public function run(ModelPerson $person): ValidationLog;

    /**
     * @return string
     */
    abstract public function getTitle(): string;

    /**
     * @return string
     */
    abstract public function getAction(): string;
}
