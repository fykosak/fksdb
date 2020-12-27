<?php

namespace FKSDB\Models\StoredQuery\Processing;

use FKSDB\Models\StoredQuery\StoredQueryPostProcessing;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationStrategy;
use FKSDB\Models\Results\ModelCategory;
use FKSDB\Models\Results\ResultsModelFactory;
use Nette\Application\BadRequestException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AESOPContestant extends StoredQueryPostProcessing {

    public const END_YEAR = 'end-year';
    public const RANK = 'rank';
    public const POINTS = 'points';
    public const SPAM_DATE = 'spam-date';

    public function getDescription(): string {
        return _('Profiltruje jenom na kategorii zadanou v parametru "category" a spočítá rank v rámci kategorie.');
    }

    public function keepsCount(): bool {
        return false;
    }

    /**
     * @param \PDOStatement $data
     * @return array
     * @throws BadRequestException
     */
    public function processData(\PDOStatement $data): array {
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
     * @param \PDOStatement $data
     * @return array
     * @throws BadRequestException
     */
    private function filterCategory(\PDOStatement $data): array {
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

    private function calculateRank(array $data): array {
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
    private function formatDate(iterable $data): iterable {
        foreach ($data as $row) {
            if ($row[self::SPAM_DATE]) {
                $row[self::SPAM_DATE] = $row[self::SPAM_DATE]->format('Y-m-d');
            }
        }

        return $data;
    }

    private function studyYearToGraduation(int $studyYear, int $acYear): ?int {
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
    private function getCategory(): ?ModelCategory {
        $evaluationStrategy = $this->getEvaluationStrategy();
        foreach ($evaluationStrategy->getCategories() as $category) {
            if ($category->id == $this->parameters['category']) {
                return $category;
            }
        }
        return null;
    }
}