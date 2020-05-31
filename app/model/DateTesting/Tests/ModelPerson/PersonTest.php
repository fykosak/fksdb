<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\DataTesting\TestsLogger;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class PersonTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonTest {

    abstract public function run(TestsLogger $logger, ModelPerson $person): void;

    abstract public function getTitle(): string;

    abstract public function getAction(): string;
}
