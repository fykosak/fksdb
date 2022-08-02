<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;
use Nette\InvalidArgumentException;

/**
 * First two categories have doubled points for the first two problems.
 * Introduced in FYKOS 2011 (25 th year).
 */
class EvaluationFykos2011 implements EvaluationStrategy
{

    public function getCategories(): array
    {
        return [
            new ModelCategory(ModelCategory::CAT_HS_1),
            new ModelCategory(ModelCategory::CAT_HS_2),
            new ModelCategory(ModelCategory::CAT_HS_3),
            new ModelCategory(ModelCategory::CAT_HS_4),
        ];
    }

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

    /**
     * @return float|int
     */
    public function getTaskPoints(TaskModel $task, ModelCategory $category): int
    {
        switch ($category->value) {
            case ModelCategory::CAT_ES_6:
            case ModelCategory::CAT_ES_7:
            case ModelCategory::CAT_ES_8:
            case ModelCategory::CAT_ES_9:
            case ModelCategory::CAT_HS_1:
            case ModelCategory::CAT_HS_2:
                if ($task->label == '1' || $task->label == '2') {
                    return $task->points * 2;
                } else {
                    return $task->points;
                }
            default:
                return $task->points;
        }
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        switch ($category->value) {
            case ModelCategory::CAT_ES_6:
            case ModelCategory::CAT_ES_7:
            case ModelCategory::CAT_ES_8:
            case ModelCategory::CAT_ES_9:
            case ModelCategory::CAT_HS_1:
            case ModelCategory::CAT_HS_2:
                return "IF(s.raw_points IS NOT NULL, IF(t.label IN ('1', '2'), 2 * t.points, t.points), NULL)";
            default:
                return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
        }
    }
}
