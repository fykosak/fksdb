<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;
use Nette\InvalidArgumentException;

abstract class EvaluationStrategy
{

    /**
     * Should return SQL expression with points for given task.
     * There are available tables 'contestant' aliased to 'ct' and
     * 'submit' aliased to 's'.
     */
    abstract public function getPointsColumn(TaskModel $task): string;

    /**
     * Should return SQL expression with points for given submit.
     * There are available tables 'contestant' aliased to 'ct',
     * 'submit' aliased to 's' and 'task' to 't'.
     * The returned expression is summed over group by series and contestant.
     */
    abstract public function getSumColumn(): string;

    /**
     * @return array of int (study years of students with category)
     */
    final public function categoryToStudyYears(ModelCategory $category): array
    {
        $map = $this->getCategoryMap();
        if (isset($map[$category->value])) {
            return $map[$category->value];
        }
        throw new InvalidArgumentException('Invalid category ' . $category->value);
    }

    final public function studyYearsToCategory(?int $studyYear): ModelCategory
    {
        $map = $this->getCategoryMap();
        foreach ($map as $key => $values) {
            if (in_array($studyYear, $values, true)) {
                return ModelCategory::tryFrom($key);
            }
        }
        throw new InvalidArgumentException('Invalid studyYear ' . $studyYear);
    }

    abstract public function getSubmitPoints(SubmitModel $submit, ModelCategory $category): ?float;

    abstract protected function getCategoryMap(): array;

    /**
     * @return ModelCategory[]
     */
    /**
     * @return ModelCategory[]
     */
    final public function getCategories(): array
    {
        return array_map(fn($value) => ModelCategory::tryFrom($value), array_keys($this->getCategoryMap()));
    }

    /**
     * Should return points for correctly solved task (aka Student Pilný) as part
     * of SQL query.
     * For columns available see getSumColumn.
     */
    abstract public function getTaskPointsColumn(ModelCategory $category): string;

    /**
     * Should return points for correctly solved task (aka Student Pilný).
     */
    abstract public function getTaskPoints(TaskModel $task, ModelCategory $category): ?int;
}
