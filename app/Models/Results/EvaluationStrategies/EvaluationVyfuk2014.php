<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;

/**
 * Introduced in Výfuk 2014 (4th official year).
 */
class EvaluationVyfuk2014 extends EvaluationStrategy
{
    public function getPointsColumn(TaskModel $task): string
    {
        if ($task->label == '1') {
            return 'IF (
            t.series < 7,
            (IF (ct.study_year_new NOT IN ("P_5","P_6", "P_7"), null, s.raw_points)),
            s.raw_points
            )';
        } else {
            return 's.raw_points';
        }
    }

    public function getSumColumn(): string
    {
        return "IF (t.series < 7,
        IF (t.label IN ('1'),
        IF ( ct.study_year_new NOT IN ('P_5,'P_6', 'P_7'), null, s.raw_points),
        s.raw_points),
        s.raw_points)";
    }

    public function getTaskPoints(TaskModel $task, ContestCategoryModel $category): ?float
    {
        if ($task->label == '1' && $task->series < 7) {
            if (
                in_array($category->label, [
                    ContestCategoryModel::VYFUK_6,
                    ContestCategoryModel::VYFUK_7,
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

    public function getSubmitPoints(SubmitModel $submit): ?float
    {
        if ($submit->task->series > 6) {
            return $submit->raw_points;
        }
        if ($submit->task->label == '1') {
            switch ($submit->contestant->contest_category->label) {
                case ContestCategoryModel::VYFUK_6:
                case ContestCategoryModel::VYFUK_7:
                    return $submit->raw_points;
                default:
                    return null;
            }
        }
        return $submit->raw_points;
    }

    public function getTaskPointsColumn(ContestCategoryModel $category): string
    {
        switch ($category->label) {
            case ContestCategoryModel::VYFUK_6:
            case ContestCategoryModel::VYFUK_7:
                return 'IF (s.raw_points IS NOT NULL, t.points, NULL)';
            default:
                return "IF (s.raw_points IS NOT NULL,
                 IF (t.series < 7, IF (t.label IN ('1'), NULL, t.points), NULL), NULL)";
        }
    }

    protected function getCategoryMap(): array
    {
        return [
            ContestCategoryModel::VYFUK_6 => [StudyYear::Primary6],
            ContestCategoryModel::VYFUK_7 => [StudyYear::Primary7],
            ContestCategoryModel::VYFUK_8 => [StudyYear::Primary8],
            ContestCategoryModel::VYFUK_9 => [StudyYear::None, StudyYear::Primary9],
        ];
    }
}
