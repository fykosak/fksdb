<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;

/**
 * First two categories have doubled points for the first two problems.
 * Introduced in FYKOS 2011 (25 th year).
 */
class EvaluationFykos2011 extends EvaluationStrategy
{
    public function getPointsColumn(TaskModel $task): string
    {
        if ($task->label == '1' || $task->label == '2') {
            return 'IF(ct.study_year IN (6,7,8,9,1,2), 2 * s.raw_points, s.raw_points)';
        } else {
            return 's.raw_points';
        }
    }

    public function getSumColumn(): string
    {
        return "IF(t.label IN ('1', '2'), 
        IF(ct.study_year IN (6,7,8,9,1,2), 2 * s.raw_points, s.raw_points), 
        s.raw_points)";
    }

    public function getSubmitPoints(SubmitModel $submit, ContestCategoryModel $category): ?float
    {
        if (is_null($submit->raw_points)) {
            return null;
        }
        return $this->getMultiplyCoefficient($submit->task, $category) * $submit->raw_points;
    }

    /**
     * @return float|int
     */
    public function getTaskPoints(TaskModel $task, ContestCategoryModel $category): float
    {
        return $this->getMultiplyCoefficient($task, $category) * $task->points;
    }

    private function getMultiplyCoefficient(TaskModel $task, ContestCategoryModel $category): int
    {
        if (
            in_array($task->label, ['1', '2']) &&
            in_array($category->label, [ContestCategoryModel::FYKOS_1, ContestCategoryModel::FYKOS_2])
        ) {
            return 2;
        }
        return 1;
    }

    public function getTaskPointsColumn(ContestCategoryModel $category): string
    {
        switch ($category->label) {
            case ContestCategoryModel::FYKOS_1:
            case ContestCategoryModel::FYKOS_2:
                return "IF(s.raw_points IS NOT NULL, IF(t.label IN ('1', '2'), 2 * t.points, t.points), NULL)";
            default:
                return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
        }
    }

    protected function getCategoryMap(): array
    {
        return [
            ContestCategoryModel::FYKOS_1 => [6, 7, 8, 9, 1],
            ContestCategoryModel::FYKOS_2 => [2],
            ContestCategoryModel::FYKOS_3 => [3],
            ContestCategoryModel::FYKOS_4 => [null, 4],
        ];
    }
}
