<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\Service;

class ContestYearService extends Service
{
    public function findByContestAndYear(int $contestId, int $year): ?ContestYearModel
    {
        return $this->getTable()->where('contest_id', $contestId)->where('year', $year)->fetch();
    }
}
