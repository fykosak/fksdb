<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;

class EvaluationFykos2023 extends EvaluationFykos2011
{
    protected function getCategoryMap(): array
    {
        return [
            ContestCategoryModel::FYKOS_1 => [5, 6, 7, 8, 9, 1],
            ContestCategoryModel::FYKOS_2 => [2],
            ContestCategoryModel::FYKOS_3 => [3],
            ContestCategoryModel::FYKOS_4 => [4],
        ];
    }
}
