<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;
use Nette\InvalidArgumentException;

/**
 * Introduced in Výfuk 2011 (1st official year).
 */
class EvaluationVyfuk2011 implements EvaluationStrategy
{

    public function getCategories(): array
    {
        return [
            ModelCategory::tryFrom(ModelCategory::VYFUK_6),
            ModelCategory::tryFrom(ModelCategory::VYFUK_7),
            ModelCategory::tryFrom(ModelCategory::VYFUK_8),
            ModelCategory::tryFrom(ModelCategory::VYFUK_9),
        ];
    }

    /**
     * @return int[]
     */
    public function categoryToStudyYears(ModelCategory $category): array
    {
        switch ($category->value) {
            case ModelCategory::VYFUK_6:
                return [6];
            case ModelCategory::VYFUK_7:
                return [7];
            case ModelCategory::VYFUK_8:
                return [8];
            case ModelCategory::VYFUK_9:
                return [null, 9];
            default:
                throw new InvalidArgumentException('Invalid category ' . $category->value);
        }
    }

    public function getPointsColumn(TaskModel $task): string
    {
        return 's.raw_points';
    }

    public function getSumColumn(): string
    {
        return 's.raw_points';
    }

    public function getTaskPoints(TaskModel $task, ModelCategory $category): int
    {
        return $task->points;
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }
}
