<?php

/**
 * Null Object pattern for IEvaluationStrategy.
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class EvaluationNullObject implements IEvaluationStrategy {

    public function getCategories() {
        throw new \Nette\NotImplementedException;
    }

    public function categoryToStudyYears($category) {
        throw new \Nette\NotImplementedException;
    }

    public function getPointsColumn($task) {
        throw new \Nette\NotImplementedException;
    }

    public function getSumColumn() {
        throw new \Nette\NotImplementedException;
    }

    public function getTaskPoints($task, \ModelCategory $category) {
        throw new \Nette\NotImplementedException;
    }

    public function getTaskPointsColumn(\ModelCategory $category) {
        throw new \Nette\NotImplementedException;
    }

}
