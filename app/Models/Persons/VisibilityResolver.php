<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\ModelPerson;

interface VisibilityResolver {

    public function isVisible(?ModelPerson $person): bool;
}
