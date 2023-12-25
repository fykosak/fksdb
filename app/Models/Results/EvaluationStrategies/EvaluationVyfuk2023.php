<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\StudyYear;

class EvaluationVyfuk2023 extends EvaluationVyfuk2014
{
    protected function getCategoryMap(): array
    {
        return [
            ContestCategoryModel::VYFUK_6 => [StudyYear::Primary5, StudyYear::Primary6],
            ContestCategoryModel::VYFUK_7 => [StudyYear::Primary7],
            ContestCategoryModel::VYFUK_8 => [StudyYear::Primary8],
            ContestCategoryModel::VYFUK_9 => [StudyYear::None, StudyYear::Primary9],
        ];
    }
}
