<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles;

use FKSDB\Models\ORM\Models\ContestModel;

interface ContestRole extends Role
{
    public function getContest(): ContestModel;
}
