<?php

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IEvaluationStrategy {

    /**
     * Should return SQL expression with points for given task.
     * There are avilable tables 'contestant' aliased to 'ct' and
     * 'submit' aliaded to 's'.
     * 
     * @param Nette\Database\Row $task 
     * @return string
     */
    public function getPointsColumn($task);

    /**
     * Should return SQL expression with points for given submit.
     * There are avilable tables 'contestant' aliased to 'ct',
     * 'submit' aliaded to 's' and 'task' to 't'.
     * The returned expression is summed over group by series and contestant.
     * 
     * @return string
     */
    public function getSumColumn();

    /**
     * @param ModelCategory $category
     * @return array of int (study years of students with category)
     */
    public function categoryToStudyYears($category);
    
    /**
     * @return array of ModelCategory
     */
    public function getCategories();
}

?>
