<?php

namespace Exports\Processings;

use Exports\StoredQueryPostProcessing;
use Nette\Database\Statement;
use ResultsModelFactory;

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

    private function filterCategory($data) {
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

}
