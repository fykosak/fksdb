<?php

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\Results\ModelCategory;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;

/**
 * Introduced in FYKOS 1987?? but data are only from 15 th year (2001).
 */
class EvaluationFykos2001 extends EvaluationStrategy
{

    /**
     * @return ModelCategory[]
     */
    public function getCategories(): array
    {
        return [
            new ModelCategory(ModelCategory::CAT_HS_1),
            new ModelCategory(ModelCategory::CAT_HS_2),
            new ModelCategory(ModelCategory::CAT_HS_3),
            new ModelCategory(ModelCategory::CAT_HS_4),
        ];
    }

    /**
     * @param ModelCategory $category
     * @return int[]
     */
    public function categoryToStudyYears(ModelCategory $category): array
    {
        switch ($category->id) {
            case ModelCategory::CAT_HS_1:
                return [6, 7, 8, 9, 1];
            case ModelCategory::CAT_HS_2:
                return [2];
            case ModelCategory::CAT_HS_3:
                return [3];
            case ModelCategory::CAT_HS_4:
                return [null, 4];
            default:
                throw new InvalidArgumentException('Invalid category ' . $category->id);
        }
    }

    public function getPointsColumn(ActiveRow $task): string
    {
        return 's.raw_points';
    }

    public function getSumColumn(): string
    {
        return 's.raw_points';
    }

    /**
     * @param ActiveRow|ModelTask $task
     * @param ModelCategory $category
     * @return int
     */
    public function getTaskPoints(ActiveRow $task, ModelCategory $category): int
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
