<?php

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ModelTask;
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
            new ModelCategory(ModelCategory::CAT_ES_6),
            new ModelCategory(ModelCategory::CAT_ES_7),
            new ModelCategory(ModelCategory::CAT_ES_8),
            new ModelCategory(ModelCategory::CAT_ES_9),
        ];
    }

    /**
     * @return int[]
     */
    public function categoryToStudyYears(ModelCategory $category): array
    {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
                return [6];
            case ModelCategory::CAT_ES_7:
                return [7];
            case ModelCategory::CAT_ES_8:
                return [8];
            case ModelCategory::CAT_ES_9:
                return [null, 9];
            default:
                throw new InvalidArgumentException('Invalid category ' . $category->id);
        }
    }

    public function getPointsColumn(ModelTask $task): string
    {
        return 's.raw_points';
    }

    public function getSumColumn(): string
    {
        return 's.raw_points';
    }

    public function getTaskPoints(ModelTask $task, ModelCategory $category): int
    {
        return $task->points;
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }
}
