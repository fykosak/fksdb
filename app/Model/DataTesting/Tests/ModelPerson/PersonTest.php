<?php

namespace FKSDB\Model\DataTesting\Tests\ModelPerson;

use Fykosak\Utils\Logging\ILogger;
use FKSDB\Model\ORM\Models\ModelPerson;

/**
 * Class PersonTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonTest {

    public string $id;

    public string $title;

    public function __construct(string $id, string $title) {
        $this->id = $id;
        $this->title = $title;
    }

    abstract public function run(ILogger $logger, ModelPerson $person): void;
}
