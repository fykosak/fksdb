<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;

/**
 * Introduced in Výfuk 2014 (4th official year).
 */
class EvaluationVyfuk2014 extends EvaluationStrategy
{
    public function getPointsColumn(TaskModel $task): string
    {
        if ($task->label == '1') {
            return 'IF (t.series < 7, (IF (ct.study_year NOT IN (6, 7), null, s.raw_points)), s.raw_points)';
        } else {
            return 's.raw_points';
        }
    }

    public function getSumColumn(): string
    {
        return "IF (t.series < 7, 
        IF (t.label IN ('1'), 
        IF ( ct.study_year NOT IN (6, 7), null, s.raw_points), 
        s.raw_points), 
        s.raw_points)";
    }

    public function getTaskPoints(TaskModel $task, ModelCategory $category): ?float
    {
        if ($task->label == '1' && $task->series < 7) {
            if (
                in_array($category->value, [
                    ModelCategory::VYFUK_6,
                    ModelCategory::VYFUK_7,
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

    public function getSubmitPoints(SubmitModel $submit, ModelCategory $category): ?float
    {
        if ($submit->task->series > 6) {
            return $submit->raw_points;
        }
        switch ($category->value) {
            case ModelCategory::VYFUK_6:
            case ModelCategory::VYFUK_7:
                if ($submit->task->label == '1') {
                    return $submit->raw_points;
                } else {
                    return null;
                }
        }
        return $submit->raw_points;
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        switch ($category->value) {
            case ModelCategory::VYFUK_6:
            case ModelCategory::VYFUK_7:
                return 'IF (s.raw_points IS NOT NULL, t.points, NULL)';
            default:
                return "IF (s.raw_points IS NOT NULL,
                 IF (t.series < 7, IF (t.label IN ('1'), NULL, t.points), NULL), NULL)";
        }
    }

    protected function getCategoryMap(): array
    {
        return [
            ModelCategory::VYFUK_6 => [6],
            ModelCategory::VYFUK_7 => [7],
            ModelCategory::VYFUK_8 => [8],
            ModelCategory::VYFUK_9 => [null, 9],
        ];
    }
}
