<?php

/**
 * Null Object pattern for IEvaluationStrategy.
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class EvaluationNullObject implements IEvaluationStrategy {

    /**
     * @return array|void
     */
    public function getCategories() {
        throw new \Nette\NotImplementedException;
    }

    /**
     * @param ModelCategory $category
     * @return array|void
     */
    public function categoryToStudyYears($category) {
        throw new \Nette\NotImplementedException;
    }

    /**
     * @param \Nette\Database\Row $task
     * @return string|void
     */
    public function getPointsColumn($task) {
        throw new \Nette\NotImplementedException;
    }

    /**
     * @return string|void
     */
    public function getSumColumn() {
        throw new \Nette\NotImplementedException;
    }

    /**
     * @param \Nette\Database\Row $task
     * @param ModelCategory $category
     * @return int|void
     */
    public function getTaskPoints($task, \ModelCategory $category) {
        throw new \Nette\NotImplementedException;
    }

    /**
     * @param ModelCategory $category
     * @return int|void
     */
    public function getTaskPointsColumn(\ModelCategory $category) {
        throw new \Nette\NotImplementedException;
    }

}
