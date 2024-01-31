<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;

/**
 * Introduced in Výfuk 2011 (1st official year).
 */
class EvaluationVyfuk2012 extends EvaluationStrategy
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
            ContestCategoryModel::VYFUK_6 => [StudyYear::Primary6],
            ContestCategoryModel::VYFUK_7 => [StudyYear::Primary7],
            ContestCategoryModel::VYFUK_8 => [StudyYear::Primary8],
            ContestCategoryModel::VYFUK_9 => [StudyYear::Primary9],
            ContestCategoryModel::VYFUK_UNK => [StudyYear::None],
        ];
    }
}
