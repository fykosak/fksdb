<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Models\Exceptions\GoneException;

class SchoolFactory
{
    public function createSchoolSelect(bool $showUnknownSchoolHint = true): SchoolSelectField
    {
        throw new GoneException();
    }
}
