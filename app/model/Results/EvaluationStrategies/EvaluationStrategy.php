<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\Results\ModelCategory;
use Nette\Database\Row;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class EvaluationStrategy {

    /**
     * Should return SQL expression with points for given task.
     * There are avilable tables 'contestant' aliased to 'ct' and
     * 'submit' aliaded to 's'.
     *
     * @param Row $task
     * @return string
     */
    abstract public function getPointsColumn(Row $task);

    /**
     * Should return SQL expression with points for given submit.
     * There are avilable tables 'contestant' aliased to 'ct',
     * 'submit' aliaded to 's' and 'task' to 't'.
     * The returned expression is summed over group by series and contestant.
     *
     * @return string
     */
    abstract public function getSumColumn();

    /**
     * @param ModelCategory $category
     * @return array of int (study years of students with category)
     */
    abstract public function categoryToStudyYears(ModelCategory $category);

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
     * @param Row $task
     * @param ModelCategory $category
     * @return int
     */
    abstract public function getTaskPoints(Row $task, ModelCategory $category);
}


