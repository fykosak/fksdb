<?php

namespace Exports\Processings;

use Exports\StoredQueryPostProcessing;
use IEvaluationStrategy;
use ModelCategory;
use ResultsModelFactory;
use ServiceTask;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AESOPContestant extends StoredQueryPostProcessing {

    const END_YEAR = 'end-year';
    const RANK = 'rank';
    const POINTS = 'points';
    const SPAM_DATE = 'spam-date';

    public function getDescription() {
        return 'Profiltruje jenom na kategorii zadanou v parametru \'category\' a spočítá rank v rámci kategorie.';
    }

    public function keepsCount() {
        return false;
    }

    public function processData($data) {
        $filtered = $this->filterCategory($data);
        $ranked = $this->calculateRank($filtered);
        $formated = $this->formatDate($ranked);
        return $formated;
    }

    /**
     * Processing itself is not injectable so we ask the dependency explicitly per method (the task service).
     * 
     * @param ServiceTask $serviceTask
     * @return int|double
     */
    public function getMaxPoints(ServiceTask $serviceTask) {
        $evalutationStrategy = $this->getEvaluationStrategy();
        $category = $this->getCategory();
        if (!$category) {
            return null;
        }
        $tasks = $serviceTask->getTable()
                ->where('contest_id', $this->parameters['contest'])
                ->where('year', $this->parameters['year']);
        $sum = 0;
        foreach ($tasks as $task) {
            $sum += $evalutationStrategy->getTaskPoints($task, $category);
        }
        return $sum;
    }

    private function filterCategory($data) {
        $evaluationStrategy = $this->getEvaluationStrategy();

        $studyYears = array();
        $category = $this->getCategory();
        if ($category) {
            $studyYears = $evaluationStrategy->categoryToStudyYears($category);
            $studyYears = is_array($studyYears) ? $studyYears : array($studyYears);
        }

        $graduationYears = array();
        foreach ($studyYears as $studyYear) {
            $graduationYears[] = $this->studyYearToGraduation($studyYear, $this->parameters['ac_year']);
        }

        $result = array();
        foreach ($data as $row) {
            if (!in_array($row[self::END_YEAR], $graduationYears)) {
                continue;
            }
            $result[] = $row;
        }
        return $result;
    }

    private function calculateRank($data) {
        $points = array();
        foreach ($data as $row) {
            if (!isset($points[$row[self::POINTS]])) {
                $points[$row[self::POINTS]] = 1;
            } else {
                $points[$row[self::POINTS]] += 1;
            }
        }

        krsort($points);
        $ranks = array();
        $cumsum = 0;
        foreach ($points as $pointsValue => $count) {
            $ranks[$pointsValue] = $cumsum + 1;
            $cumsum += $count;
        }

        foreach ($data as $row) {
            $row[self::RANK] = $ranks[$row[self::POINTS]];
        }

        return $data;
    }

    private function formatDate($data) {
        foreach ($data as $row) {
            if ($row[self::SPAM_DATE]) {
                $row[self::SPAM_DATE] = $row[self::SPAM_DATE]->format('Y-m-d');
            }
        }

        return $data;
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

    /**
     * @return IEvaluationStrategy
     */
    private function getEvaluationStrategy() {
        return ResultsModelFactory::findEvaluationStrategy($this->parameters['contest'], $this->parameters['year']);
    }

    /**
     * 
     * @return ModelCategory|null
     */
    private function getCategory() {
        $evaluationStrategy = $this->getEvaluationStrategy();
        foreach ($evaluationStrategy->getCategories() as $category) {
            if ($category->id == $this->parameters['category']) {
                return $category;
            }
        }
        return null;
    }

}
