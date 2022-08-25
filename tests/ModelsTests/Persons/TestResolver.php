<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use FKSDB\Models\Persons\Resolvers\Resolver;

class TestResolver implements Resolver
{

    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        return ResolutionMode::tryFrom(ResolutionMode::EXCEPTION);
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return true;
    }

    public function isVisible(?PersonModel $person): bool
    {
        return true;
    }
}
