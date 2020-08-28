<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class PersonTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonTest {

    private string $id;

    private string $title;

    /**
     * PersonTest constructor.
     * @param string $id
     * @param string $title
     */
    public function __construct(string $id, string $title) {
        $this->id = $id;
        $this->title = $title;
    }

    abstract public function run(ILogger $logger, ModelPerson $person): void;

    public function getTitle(): string {
        return $this->title;
    }

    public function getId(): string {
        return $this->id;
    }
}
