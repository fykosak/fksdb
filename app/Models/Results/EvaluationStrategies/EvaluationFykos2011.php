<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
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
            ModelCategory::tryFrom(ModelCategory::FYKOS_1),
            ModelCategory::tryFrom(ModelCategory::FYKOS_2),
            ModelCategory::tryFrom(ModelCategory::FYKOS_3),
            ModelCategory::tryFrom(ModelCategory::FYKOS_4),
        ];
    }

    public function categoryToStudyYears(ModelCategory $category): array
    {
        switch ($category->value) {
            case ModelCategory::FYKOS_1:
                return [6, 7, 8, 9, 1];
            case ModelCategory::FYKOS_2:
                return [2];
            case ModelCategory::FYKOS_3:
                return [3];
            case ModelCategory::FYKOS_4:
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

    public function getSubmitPoints(SubmitModel $submit, ModelCategory $category): ?float
    {
        if (is_null($submit->raw_points)) {
            return null;
        }
        return $this->getMultiplyCoefficient($submit->task, $category) * $submit->raw_points;
    }

    /**
     * @return float|int
     */
    public function getTaskPoints(TaskModel $task, ModelCategory $category): int
    {
        return $this->getMultiplyCoefficient($task, $category) * $task->points;
    }

    private function getMultiplyCoefficient(TaskModel $task, ModelCategory $category): int
    {
        if (
            in_array($task->label, ['1', '2']) &&
            in_array($category->value, [ModelCategory::FYKOS_1, ModelCategory::FYKOS_2])
        ) {
            return 2;
        }
        return 1;
    }

    public function getTaskPointsColumn(ModelCategory $category): string
    {
        switch ($category->value) {
            case ModelCategory::FYKOS_1:
            case ModelCategory::FYKOS_2:
                return "IF(s.raw_points IS NOT NULL, IF(t.label IN ('1', '2'), 2 * t.points, t.points), NULL)";
            default:
                return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
        }
    }

    public function studyYearsToCategory(?int $studyYear): ModelCategory
    {
        switch ($studyYear) {
            case 9:
            case 8:
            case 7:
            case 6:
            case 1:
                return ModelCategory::tryFrom(ModelCategory::FYKOS_1);
            case 2:
                return ModelCategory::tryFrom(ModelCategory::FYKOS_2);
            case 3:
                return ModelCategory::tryFrom(ModelCategory::FYKOS_3);
            case 4:
            case null:
                return ModelCategory::tryFrom(ModelCategory::FYKOS_4);
            default:
                throw new InvalidArgumentException('Invalid studyYear ' . $studyYear);
        }
    }
}
