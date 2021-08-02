<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\Logging\Logger;
use FKSDB\Models\ORM\Models\ModelPerson;

abstract class PersonTest
{

    public string $id;

    public string $title;

    public function __construct(string $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    abstract public function run(Logger $logger, ModelPerson $person): void;
}
