<?php

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\Results\ModelCategory;
use Nette\InvalidArgumentException;

/**
 * Introduced in Výfuk 2014 (4th official year).
 */
class EvaluationVyfuk2014 implements EvaluationStrategy
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
        if ($task->label == '1') {
            return 'IF (t.series < 7, (IF (ct.study_year NOT IN (6, 7), null, s.raw_points)), s.raw_points)';
        } else {
            return 's.raw_points';
        }
    }

    public function getSumColumn(): string
    {
        return "IF (t.series < 7, IF (t.label IN ('1'), IF ( ct.study_year NOT IN (6, 7), null, s.raw_points), s.raw_points), s.raw_points)";
    }

    public function getTaskPoints(ModelTask $task, ModelCategory $category): ?int
    {
        if ($task->label == '1' && $task->series < 7) {
            if (
                in_array($category->id, [
                    ModelCategory::CAT_ES_6,
                    ModelCategory::CAT_ES_7,
                ])
            ) {
                return $task->points;
            } else {
                return null;
            }
        } else {
            return $task->points;
        }
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
            case ModelCategory::CAT_ES_7:
                return 'IF (s.raw_points IS NOT NULL, t.points, NULL)';
            default:
                return "IF (s.raw_points IS NOT NULL, IF (t.series < 7, IF (t.label IN ('1'), NULL, t.points), NULL), NULL)";
        }
    }
}
