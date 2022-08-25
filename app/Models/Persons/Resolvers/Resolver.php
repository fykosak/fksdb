<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;

interface Resolver
{
    public function isVisible(?PersonModel $person): bool;

    public function isModifiable(?PersonModel $person): bool;

    public function getResolutionMode(?PersonModel $person): ResolutionMode;
}
