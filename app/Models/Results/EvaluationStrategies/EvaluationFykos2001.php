<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;

/**
 * Introduced in FYKOS 1987?? but data are only from 15 th year (2001).
 */
class EvaluationFykos2001 extends EvaluationStrategy
{
    public function getPointsColumn(TaskModel $task): string
    {
        return 's.raw_points';
    }

    public function getSumColumn(): string
    {
        return 's.raw_points';
    }

    public function getTaskPoints(TaskModel $task, ContestCategoryModel $category): float
    {
        return $task->points;
    }

    public function getSubmitPoints(SubmitModel $submit): ?float
    {
        return $submit->raw_points;
    }

    public function getTaskPointsColumn(ContestCategoryModel $category): string
    {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
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
            ContestCategoryModel::FYKOS_4 => [StudyYear::UniversityAll, StudyYear::None, StudyYear::High4],
        ];
    }
}
