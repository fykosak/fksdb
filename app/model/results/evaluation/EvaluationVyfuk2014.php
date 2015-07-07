<?php

use Nette\InvalidArgumentException;

/**
 * Introduced in Výfuk 2014 (4th official year).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EvaluationVyfuk2014 implements IEvaluationStrategy {

    private $categories = null;

    public function getCategories() {
        if($this->categories == null){
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
                return array(null,9);
            default:
                throw new InvalidArgumentException('Invalid category '.$category->id);
                break;
        }
    }

    public function getPointsColumn($task) {
        if($task->label == '1'){
            return "IF(t.series<7,(IF(ct.study_year NOT IN (6,7), null, s.raw_points)),s.raw_points)";
            //return "IF(ct.study_year NOT IN (6,7), null, s.raw_points)";
        }else{
            return "s.raw_points";
        }
    }

    public function getSumColumn() {

        //return "IF(t.label IN ('1'), IF(ct.study_year NOT IN (6,7), null, s.raw_points), s.raw_points)";

        return "IF(t.series<7,IF(t.label IN ('1'),IF( ct.study_year NOT IN (6,7),null, s.raw_points ) , s.raw_points),s.raw_points)";
    }

    public function getTaskPoints($task,\ModelCategory $category) {
        
        if($task->label == '1' && $task->series<7){
            if(in_array($category->id,array(
                        ModelCategory::CAT_ES_6,
                        ModelCategory::CAT_ES_7,
                    ))){
                return $task->points;
            }else{
                return null;
            }
        }else{
            return $task->points;
        }
    }

    public function getTaskPointsColumn(\ModelCategory $category) {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
            case ModelCategory::CAT_ES_7:
                return "IF(s.raw_points IS NOT NULL, t.points, NULL)";
                break;
            default:
                //return "IF(s.raw_points IS NOT NULL,               IF(t.label IN ('1'), NULL, t.points),       NULL)";
                return "IF(s.raw_points IS NOT NULL, IF(t.series<7,IF(t.label IN ('1'), NULL, t.points),NULL), NULL)";
        }
    }

}
