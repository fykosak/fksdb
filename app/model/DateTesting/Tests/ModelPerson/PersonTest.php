<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\DataTesting\TestsLogger;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class PersonTest
 * *
 */
abstract class PersonTest {
    /**
     * @param TestsLogger $logger
     * @param ModelPerson $person
     * @return void
     */
    abstract public function run(TestsLogger $logger, ModelPerson $person);

    /**
     * @return string
     */
    abstract public function getTitle(): string;

    /**
     * @return string
     */
    abstract public function getAction(): string;
}
