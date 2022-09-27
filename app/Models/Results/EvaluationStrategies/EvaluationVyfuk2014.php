<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
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
            ModelCategory::tryFrom(ModelCategory::VYFUK_6),
            ModelCategory::tryFrom(ModelCategory::VYFUK_7),
            ModelCategory::tryFrom(ModelCategory::VYFUK_8),
            ModelCategory::tryFrom(ModelCategory::VYFUK_9),
        ];
    }

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

    public function getTaskPoints(TaskModel $task, ModelCategory $category): ?int
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
        switch ($category->value) {
            case ModelCategory::VYFUK_6:
            case ModelCategory::VYFUK_7:
                if ($submit->task->label == '1' && $submit->task->series < 7) {
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

    public function studyYearsToCategory(?int $studyYear): ModelCategory
    {
        switch ($studyYear) {
            case null:
            case 9:
                return ModelCategory::tryFrom(ModelCategory::VYFUK_9);
            case 8:
                return ModelCategory::tryFrom(ModelCategory::VYFUK_8);
            case 7:
                return ModelCategory::tryFrom(ModelCategory::VYFUK_7);
            case 6:
                return ModelCategory::tryFrom(ModelCategory::VYFUK_6);
            default:
                throw new InvalidArgumentException('Invalid studyYear ' . $studyYear);
        }
    }
}
