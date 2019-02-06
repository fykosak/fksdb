<?php

/**
 * Introduced in FYKOS 1987?? but data are only from 15 th year (2001).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EvaluationFykos2001 implements IEvaluationStrategy {

    private $categories = null;

    /**
     * @return array|null
     */
    public function getCategories() {
        if ($this->categories == null) {
            $this->categories = array(
                new ModelCategory(ModelCategory::CAT_HS_1),
                new ModelCategory(ModelCategory::CAT_HS_2),
                new ModelCategory(ModelCategory::CAT_HS_3),
                new ModelCategory(ModelCategory::CAT_HS_4),
            );
        }
        return $this->categories;
    }

    /**
     * @param ModelCategory $category
     * @return array|int
     */
    public function categoryToStudyYears($category) {
        switch ($category->id) {
            case ModelCategory::CAT_HS_1:
                return array(6, 7, 8, 9, 1);
            case ModelCategory::CAT_HS_2:
                return 2;
            case ModelCategory::CAT_HS_3:
                return 3;
            case ModelCategory::CAT_HS_4:
                return array(null, 4);
            default:
                throw new Nette\InvalidArgumentException('Invalid category ' . $category->id);
                break;
        }
    }

    /**
     * @param \Nette\Database\Row $task
     * @return string
     */
    public function getPointsColumn($task) {
        return "s.raw_points";
    }

    /**
     * @return string
     */
    public function getSumColumn() {
        return "s.raw_points";
    }

    /**
     * @param \Nette\Database\Row $task
     * @param ModelCategory $category
     * @return int
     */
    public function getTaskPoints($task, \ModelCategory $category) {
        return $task->points;
    }

    /**
     * @param ModelCategory $category
     * @return int|string
     */
    public function getTaskPointsColumn(\ModelCategory $category) {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }

}
