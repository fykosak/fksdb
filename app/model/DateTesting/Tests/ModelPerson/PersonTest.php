<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class PersonTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonTest {
    /**
     * @param ILogger $logger
     * @param ModelPerson $person
     * @return void
     */
    abstract public function run(ILogger $logger, ModelPerson $person);

    abstract public function getTitle(): string;

    abstract public function getAction(): string;
}
