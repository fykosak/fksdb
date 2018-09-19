<?php

/**
 * Introduced in Výfuk 2011 (1st official year).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EvaluationVyfuk2011 implements IEvaluationStrategy {

    private $categories = null;

    public function getCategories() {
        if ($this->categories == null) {
            $this->categories = array(
                new ModelCategory(ModelCategory::CAT_ES_6),
                new ModelCategory(ModelCategory::CAT_ES_7),
                new ModelCategory(ModelCategory::CAT_ES_8),
                new ModelCategory(ModelCategory::CAT_ES_9),
            );
        }
        return $this->categories;
    }

    public function categoryToStudyYears($category) {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
                return 6;
            case ModelCategory::CAT_ES_7:
                return 7;
            case ModelCategory::CAT_ES_8:
                return 8;
            case ModelCategory::CAT_ES_9:
                return array(null, 9);
            default:
                throw new Nette\InvalidArgumentException('Invalid category ' . $category->id);
                break;
        }
    }

    public function getPointsColumn($task) {
        return "s.raw_points";
    }

    public function getSumColumn() {
        return "s.raw_points";
    }

    public function getTaskPoints($task, \ModelCategory $category) {
        return $task->points;
    }

    public function getTaskPointsColumn(\ModelCategory $category) {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }
}
