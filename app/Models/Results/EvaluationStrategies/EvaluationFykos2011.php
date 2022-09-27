<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;

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

    public function getSubmitPoints(SubmitModel $submit, ModelCategory $category): ?float
    {
        if (is_null($submit->raw_points)) {
            return null;
        }
        return $this->getMultiplyCoefficient($submit->task, $category) * $submit->raw_points;
    }

    /**
     * @return float|int
     */
    public function getTaskPoints(TaskModel $task, ModelCategory $category): float
    {
        return $this->getMultiplyCoefficient($task, $category) * $task->points;
    }

    private function getMultiplyCoefficient(TaskModel $task, ModelCategory $category): int
    {
        if (
            in_array($task->label, ['1', '2']) &&
            in_array($category->value, [ModelCategory::FYKOS_1, ModelCategory::FYKOS_2])
        ) {
            return 2;
        }
        return 1;
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        switch ($category->value) {
            case ModelCategory::FYKOS_1:
            case ModelCategory::FYKOS_2:
                return "IF(s.raw_points IS NOT NULL, IF(t.label IN ('1', '2'), 2 * t.points, t.points), NULL)";
            default:
                return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
        }
    }

    protected function getCategoryMap(): array
    {
        return [
            ModelCategory::FYKOS_1 => [6, 7, 8, 9, 1],
            ModelCategory::FYKOS_2 => [2],
            ModelCategory::FYKOS_3 => [3],
            ModelCategory::FYKOS_4 => [null, 4],
        ];
    }
}
