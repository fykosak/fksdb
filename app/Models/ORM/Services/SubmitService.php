<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;

/**
 * @phpstan-extends Service<SubmitModel>
 */
final class SubmitService extends Service
{
    /**
     * @phpstan-return TypedSelection<SubmitModel>
     */
    public function getForContestYear(ContestYearModel $contestYear, int $series): TypedSelection
    {
        return $this->getTable()
            ->where([
                'task.contest_id' => $contestYear->contest_id,
                'task.year' => $contestYear->year,
                'task.series' => $series,
            ]);
    }
}
