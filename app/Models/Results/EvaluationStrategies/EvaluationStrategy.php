<?php

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\Results\ModelCategory;
use Nette\Database\Table\ActiveRow;

abstract class EvaluationStrategy {

    /**
     * Should return SQL expression with points for given task.
     * There are available tables 'contestant' aliased to 'ct' and
     * 'submit' aliased to 's'.
     */
    abstract public function getPointsColumn(ActiveRow $task): string;

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
    abstract public function categoryToStudyYears(ModelCategory $category): array;

    /**
     * @return ModelCategory[]
     */
    abstract public function getCategories(): array;

    /**
     * Should return points for correctly solved task (aka Student Pilný) as part
     * of SQL query.
     * For columns available see getSumColumn.
     */
    abstract public function getTaskPointsColumn(ModelCategory $category): string;

    /**
     * Should return points for correctly solved task (aka Student Pilný).
     *
     * @param ActiveRow|ModelTask $task
     */
    abstract public function getTaskPoints(ActiveRow $task, ModelCategory $category): ?int;
}
