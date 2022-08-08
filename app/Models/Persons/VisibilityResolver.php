<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\PersonModel;

interface VisibilityResolver
{
    public function isVisible(?PersonModel $person): bool;
}
