<?php

namespace Exports\Processings;

use Exports\StoredQueryPostProcessing;
use ModelContest;
use ResultsModelFactory;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AESOPContestant extends StoredQueryPostProcessing {

    public function getDescription() {
        return 'Profiltruje jenom na kategorii zadanou v parametru \'category\' a spočítá rank v rámci kategorie.';
    }
    
    public function processCount($count) {
        return parent::processCount($count); //TODO
    }

    public function processData($data, $orderColumns, $offset, $limit) {        
        $evalutationStrategy = ResultsModelFactory::findEvaluationStrategy($this->parameters['contest'], $this->parameters['year']);

        $studyYears = array();
        foreach ($evalutationStrategy->getCategories() as $category) {
            if ($category->id == $this->parameters['category']) {
                $studyYears = $evalutationStrategy->categoryToStudyYears($category);
                $studyYears = is_array($studyYears) ? $studyYears : array($studyYears);
            }
        }
        $graduationYears = array();
        foreach ($studyYears as $studyYear) {
            $graduationYears[] = $this->studyYearToGraduation($studyYear, $this->parameters['ac_year']);
        }

        $result = array();
        foreach ($data as $row) {
            if (!in_array($row['end-year'], $graduationYears)) {
                continue;
            }
            $result[] = $row;
        }
        
        return $result;
    }

    private function studyYearToGraduation($studyYear, $acYear) {
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        } else if ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (14 - $studyYear);
        } else {
            return null;
        }
    }

}
