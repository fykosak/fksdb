<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;

class EvaluationVyfuk2023 extends EvaluationVyfuk2014
{
    protected function getCategoryMap(): array
    {
        return [
            ContestCategoryModel::VYFUK_6 => [5, 6],
            ContestCategoryModel::VYFUK_7 => [7],
            ContestCategoryModel::VYFUK_8 => [8],
            ContestCategoryModel::VYFUK_9 => [null, 9],
        ];
    }
}
