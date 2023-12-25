<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\StudyYear;

class EvaluationFykos2023 extends EvaluationFykos2011
{
    protected function getCategoryMap(): array
    {
        return [
            ContestCategoryModel::FYKOS_1 => [
                StudyYear::Primary5,
                StudyYear::Primary6,
                StudyYear::Primary7,
                StudyYear::Primary8,
                StudyYear::Primary9,
                StudyYear::High1,
            ],
            ContestCategoryModel::FYKOS_2 => [StudyYear::High2],
            ContestCategoryModel::FYKOS_3 => [StudyYear::High3],
            ContestCategoryModel::FYKOS_4 => [StudyYear::High4],
        ];
    }
}
