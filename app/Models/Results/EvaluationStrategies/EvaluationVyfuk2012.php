<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;
use Nette\InvalidArgumentException;

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

    public function getTaskPoints(TaskModel $task, ModelCategory $category): float
    {
        return $task->points;
    }

    public function getSubmitPoints(SubmitModel $submit, ModelCategory $category): ?float
    {
        return $submit->raw_points;
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }

    protected function getCategoryMap(): array
    {
        return [
            ModelCategory::VYFUK_6 => [6],
            ModelCategory::VYFUK_7 => [7],
            ModelCategory::VYFUK_8 => [8],
            ModelCategory::VYFUK_9 => [9],
            ModelCategory::VYFUK_UNK => [null],
        ];
    }
}
