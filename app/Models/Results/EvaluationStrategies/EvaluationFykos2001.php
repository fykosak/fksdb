<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;
use Nette\InvalidArgumentException;

/**
 * Introduced in FYKOS 1987?? but data are only from 15 th year (2001).
 */
class EvaluationFykos2001 implements EvaluationStrategy
{

    /**
     * @return ModelCategory[]
     */
    public function getCategories(): array
    {
        return [
            ModelCategory::tryFrom(ModelCategory::CAT_HS_1),
            ModelCategory::tryFrom(ModelCategory::CAT_HS_2),
            ModelCategory::tryFrom(ModelCategory::CAT_HS_3),
            ModelCategory::tryFrom(ModelCategory::CAT_HS_4),
        ];
    }

    /**
     * @return int[]
     */
    public function categoryToStudyYears(ModelCategory $category): array
    {
        switch ($category->value) {
            case ModelCategory::CAT_HS_1:
                return [6, 7, 8, 9, 1];
            case ModelCategory::CAT_HS_2:
                return [2];
            case ModelCategory::CAT_HS_3:
                return [3];
            case ModelCategory::CAT_HS_4:
                return [null, 4];
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

    /**
     * @param ModelCategory $category
     * @return int|string
     */
    public function getTaskPointsColumn(ModelCategory $category): string
    {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }
}
