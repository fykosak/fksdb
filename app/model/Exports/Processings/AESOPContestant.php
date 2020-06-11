<?php

namespace Exports\Processings;

use Exports\StoredQueryPostProcessing;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\Results\EvaluationStrategies\EvaluationStrategy;
use FKSDB\Results\ModelCategory;
use FKSDB\Results\ResultsModelFactory;
use Nette\Application\BadRequestException;

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

    public function getDescription(): string {
        return 'Profiltruje jenom na kategorii zadanou v parametru "category" a spočítá rank v rámci kategorie.';
    }

    public function keepsCount(): bool {
        return false;
    }

    /**
     * @param $data
     * @return mixed
     * @throws BadRequestException
     */
    public function processData(\PDOStatement $data) {
        $filtered = $this->filterCategory($data);
        //$formated = $this->formatDate($ranked); //implemented in SQL
        return $this->calculateRank($filtered);
    }

    /**
     * Processing itself is not injectable so we ask the dependency explicitly per method (the task service).
     *
     * @param ServiceTask $serviceTask
     * @return int|double
     * @throws BadRequestException
     */
    public function getMaxPoints(ServiceTask $serviceTask) {
        $evalutationStrategy = $this->getEvaluationStrategy();
        $category = $this->getCategory();
        if (!$category) {
            return null;
        }
        $tasks = $serviceTask->getTable()
            ->where('contest_id', $this->parameters['contest'])
            ->where('year', $this->parameters['year'])
            ->where('series BETWEEN 1 AND 6');
        $sum = 0;
        foreach ($tasks as $task) {
            $sum += $evalutationStrategy->getTaskPoints($task, $category);
        }
        return $sum;
    }

    /**
     * @param iterable $data
     * @return array
     * @throws BadRequestException
     */
    private function filterCategory($data): array {
        $evaluationStrategy = $this->getEvaluationStrategy();

        $studyYears = [];
        $category = $this->getCategory();
        if ($category) {
            $studyYears = $evaluationStrategy->categoryToStudyYears($category);
            $studyYears = is_array($studyYears) ? $studyYears : [$studyYears];
        }

        $graduationYears = [];
        foreach ($studyYears as $studyYear) {
            $graduationYears[] = $this->studyYearToGraduation($studyYear, $this->parameters['ac_year']);
        }

        $result = [];
        foreach ($data as $row) {
            if (!in_array($row[self::END_YEAR], $graduationYears)) {
                continue;
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @param iterable $data
     * @return iterable
     */
    private function calculateRank($data) {
        $points = [];
        foreach ($data as $row) {
            if (!isset($points[$row[self::POINTS]])) {
                $points[$row[self::POINTS]] = 1;
            } else {
                $points[$row[self::POINTS]] += 1;
            }
        }

        krsort($points);
        $ranks = [];
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

    /**
     * TODO typesafe
     * @param mixed[]|\DateTimeInterface[][] $data
     * @return mixed
     */
    private function formatDate($data) {
        foreach ($data as $row) {
            if ($row[self::SPAM_DATE]) {
                $row[self::SPAM_DATE] = $row[self::SPAM_DATE]->format('Y-m-d');
            }
        }

        return $data;
    }

    /**
     * @param $studyYear
     * @param $acYear
     * @return int|null
     */
    private function studyYearToGraduation(int $studyYear, int $acYear) {
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        } elseif ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (14 - $studyYear);
        } else {
            return null;
        }
    }

    /**
     * @return EvaluationStrategy
     * @throws BadRequestException
     */
    private function getEvaluationStrategy(): EvaluationStrategy {
        return ResultsModelFactory::findEvaluationStrategy($this->parameters['contest'], $this->parameters['year']);
    }

    /**
     *
     * @return ModelCategory|null
     * @throws BadRequestException
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
