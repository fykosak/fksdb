<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\ContestYear;

use FKSDB\Models\Authorization\Roles\Role;
use FKSDB\Models\ORM\Models\ContestYearModel;

interface ContestYearRole extends Role
{
    public function getContestYear(): ContestYearModel;
}
