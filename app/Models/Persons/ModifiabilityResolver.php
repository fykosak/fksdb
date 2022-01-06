<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\ORM\Models\ModelPerson;

interface ModifiabilityResolver {

    public function isModifiable(?ModelPerson $person): bool;

    public function getResolutionMode(?ModelPerson $person): string;
}
