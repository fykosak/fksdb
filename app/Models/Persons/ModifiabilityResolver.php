<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\PersonModel;

interface ModifiabilityResolver
{

    public function isModifiable(?PersonModel $person): bool;

    public function getResolutionMode(?PersonModel $person): string;
}
