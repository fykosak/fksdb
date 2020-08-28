<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\Results\ModelCategory;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class EvaluationStrategy {

    /**
     * Should return SQL expression with points for given task.
     * There are available tables 'contestant' aliased to 'ct' and
     * 'submit' aliased to 's'.
     *
     * @param ActiveRow $task
     * @return string
     */
    abstract public function getPointsColumn(ActiveRow $task): string;

    /**
     * Should return SQL expression with points for given submit.
     * There are available tables 'contestant' aliased to 'ct',
     * 'submit' aliased to 's' and 'task' to 't'.
     * The returned expression is summed over group by series and contestant.
     *
     * @return string|null
     */
    abstract public function getSumColumn(): string;

    /**
     * @param ModelCategory $category
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
     *
     * @param ModelCategory $category
     * @return string
     */
    abstract public function getTaskPointsColumn(ModelCategory $category): string;

    /**
     * Should return points for correctly solved task (aka Student Pilný).
     *
     * @param ActiveRow $task
     * @param ModelCategory $category
     * @return int
     */
    abstract public function getTaskPoints(ActiveRow $task, ModelCategory $category): ?int;
}
