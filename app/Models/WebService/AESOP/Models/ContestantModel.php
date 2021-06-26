<?php

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\Results\ModelCategory;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Models\WebService\AESOP\AESOPFormat;
use Nette\Application\BadRequestException;
use Nette\Database\ResultSet;
use Nette\DI\Container;

class ContestantModel extends AESOPModel {

    protected ServiceTask $serviceTask;

    private ?string $category;

    public function __construct(Container $container, ModelContestYear $contestYear, ?string $category) {
        parent::__construct($container, $contestYear);
        $this->category = $category;
        $container->callInjects($this);
    }

    public function injectTaskService(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    /**
     * @return AESOPFormat
     * @throws BadRequestException
     */
    protected function createFormat(): AESOPFormat {
        $query = $this->explorer->query("select ac.*, IF(ac.`x-points_ratio` >= 0.5, 'Y', 'N') AS `successful`
         FROM v_aesop_contestant ac
WHERE
	ac.`x-contest_id` = ?
        AND ac.`x-ac_year` = ?
        AND (1=1 or ? = 0) -- hack for parameters
                                               order by surname, name",
            $this->contestYear->contest_id,
            $this->contestYear->ac_year,
            $this->category,
        );
        $data = $this->calculateRank($this->filterCategory($query));

        $params = [
            'version' => 1,
            'event' => $this->getMask(),
            'year' => $this->contestYear->ac_year,
            'date' => date('Y-m-d H:i:s'),
            'errors-to' => 'it@fykos.cz',
            'max-rank' => count($data),
            'max-points' => $this->getMaxPoints(),
            'id-scope' => self::ID_SCOPE,
        ];
        return new AESOPFormat($params, $data, array_keys($query->getColumnTypes()));
    }

    protected function getMask(): string {
        return $this->contestYear->getContest()->getContestSymbol() . '.rocnik.' . $this->category;
    }

    /**
     * Processing itself is not injectable so we ask the dependency explicitly per method (the task service).
     *
     * @return int|double|null
     * @throws BadRequestException
     */
    public function getMaxPoints(): ?int {
        $evalutationStrategy = ResultsModelFactory::findEvaluationStrategyByContestYear($this->contestYear);
        if (!$this->category) {
            return null;
        }
        $tasks = $this->serviceTask->getTable()
            ->where('contest_id', $this->contestYear->contest_id)
            ->where('year', $this->contestYear->year)
            ->where('series BETWEEN 1 AND 6');
        $sum = 0;
        foreach ($tasks as $task) {
            $sum += $evalutationStrategy->getTaskPoints($task, $this->getCategory());
        }
        return $sum;
    }

    /**
     *
     * @return ModelCategory|null
     * @throws BadRequestException
     */
    private function getCategory(): ?ModelCategory {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategyByContestYear($this->contestYear);
        foreach ($evaluationStrategy->getCategories() as $category) {
            if ($category->id == $this->category) {
                return $category;
            }
        }
        return null;
    }

    /**
     * @param ResultSet $data
     * @return array
     * @throws BadRequestException
     */
    private function filterCategory(ResultSet $data): array {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategyByContestYear($this->contestYear);

        $studyYears = [];
        $category = $this->getCategory();
        if ($category) {
            $studyYears = $evaluationStrategy->categoryToStudyYears($category);
            $studyYears = is_array($studyYears) ? $studyYears : [$studyYears];
        }

        $graduationYears = [];
        foreach ($studyYears as $studyYear) {
            $graduationYears[] = $this->studyYearToGraduation($studyYear, $this->contestYear->ac_year);
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

    private function studyYearToGraduation(?int $studyYear, int $acYear): ?int {
        if ($studyYear >= 1 && $studyYear <= 4) {
            return $acYear + (5 - $studyYear);
        } elseif ($studyYear >= 6 && $studyYear <= 9) {
            return $acYear + (14 - $studyYear);
        } else {
            return null;
        }
    }
}
