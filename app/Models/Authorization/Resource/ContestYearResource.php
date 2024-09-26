<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\Security\Resource;

interface ContestYearResource extends Resource
{
    public function getContestYear(): ContestYearModel;
}
