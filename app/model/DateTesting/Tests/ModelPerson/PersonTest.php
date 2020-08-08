<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class PersonTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonTest {
    /** @var string */
    private $id;
    /** @var string */
    private $title;

    /**
     * PersonTest constructor.
     * @param string $id
     * @param string $title
     */
    public function __construct(string $id, string $title) {
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * @param ILogger $logger
     * @param ModelPerson $person
     * @return void
     */
    abstract public function run(ILogger $logger, ModelPerson $person);

    public function getTitle(): string {
        return $this->title;
    }

    public function getId(): string {
        return $this->id;
    }
}
