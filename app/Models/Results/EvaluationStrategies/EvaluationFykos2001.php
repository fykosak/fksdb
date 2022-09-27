<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;

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

    public function getTaskPoints(TaskModel $task, ModelCategory $category): float
    {
        return $task->points;
    }

    public function getSubmitPoints(SubmitModel $submit, ModelCategory $category): ?float
    {
        return $submit->raw_points;
    }

    /**
     * @param ModelCategory $category
     * @return int|string
     */
    public function getTaskPointsColumn(ModelCategory $category): string
    {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
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
