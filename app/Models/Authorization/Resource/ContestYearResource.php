<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestYearModel;

interface ContestYearResource extends ContestResource
{
    public function getContestYear(): ContestYearModel;
}
