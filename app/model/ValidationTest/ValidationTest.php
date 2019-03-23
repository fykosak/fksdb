<?php

namespace FKSDB\ValidationTest;


use FKSDB\ORM\Models\ModelPerson;

/**
 * Class ValidationTest
 */
abstract class ValidationTest {
    /**
     * @param ModelPerson $person
     * @return ValidationLog[]
     */
    abstract static function run(ModelPerson $person): array;

    /**
     * @return string
     */
    abstract function getTitle(): string;

    /**
     * @return string
     */
    abstract function getAction(): string;


}
