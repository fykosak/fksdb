<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
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
            return 'IF(
            ct.study_year_new IN ("P_5","P_6","P_7","P_8","P_9","H_1","H_2"), 
            2 * s.raw_points, 
            s.raw_points
            )';
        } else {
            return 's.raw_points';
        }
    }

    public function getSumColumn(): string
    {
        return "IF(t.label IN ('1', '2'), 
        IF(ct.study_year_new IN ('P_5','P_6','P_7','P_8','P_9','H_1','H_2'), 2 * s.raw_points, s.raw_points), 
        s.raw_points)";
    }

    public function getSubmitPoints(SubmitModel $submit): ?float
    {
        if (is_null($submit->raw_points)) {
            return null;
        }
        return $this->getMultiplyCoefficient($submit->task, $submit->contestant->contest_category) *
            $submit->raw_points;
    }

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
            ContestCategoryModel::FYKOS_1 => [
                StudyYear::Primary6,
                StudyYear::Primary7,
                StudyYear::Primary8,
                StudyYear::Primary9,
                StudyYear::High1,
            ],
            ContestCategoryModel::FYKOS_2 => [StudyYear::High2],
            ContestCategoryModel::FYKOS_3 => [StudyYear::High3],
            ContestCategoryModel::FYKOS_4 => [StudyYear::None, StudyYear::High4],
        ];
    }
}
