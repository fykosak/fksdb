<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\Exports\Formats\PlainTextResponse;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\Results\ModelCategory;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Models\YearCalculator;
use Nette\Application\BadRequestException;
use Nette\Database\ResultSet;
use Nette\DI\Container;

class ContestantModel extends AESOPModel {

    protected ServiceTask $serviceTask;

    private ?ModelCategory $category;

    /**
     * ContestantModel constructor.
     * @throws BadRequestException
     */
    public function __construct(Container $container, ModelContestYear $contestYear, ?string $category) {
        parent::__construct($container, $contestYear);
        $this->category = $this->getCategory($category);
        $container->callInjects($this);
    }

    public function injectTaskService(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    /**
     * @throws BadRequestException
     */
    public function createResponse(): PlainTextResponse {
        $query = $this->explorer->query("select ac.*, IF(ac.`x-points_ratio` >= 0.5, 'Y', 'N') AS `successful`
         FROM v_aesop_contestant ac
WHERE
	ac.`x-contest_id` = ?
        AND ac.`x-ac_year` = ?
        AND (1=1 or ? = 0) -- hack for parameters
                                               order by surname, name",
            $this->contestYear->contest_id,
            $this->contestYear->ac_year,
            $this->category->id
        );
        $data = $this->calculateRank($this->filterCategory($query));

        return $this->formatResponse(
            $this->getDefaultParams() + [
                'max-rank' => count($data),
                'max-points' => $this->getMaxPoints(),
            ],
            $data,
            array_keys($query->getColumnTypes())
        );
    }

    protected function getMask(): string {
        return $this->contestYear->getContest()->getContestSymbol() . '.rocnik.' . $this->category->id;
    }

    /**
     * Processing itself is not injectable so we ask the dependency explicitly per method (the task service).
     *
     * @return int|double|null
     * @throws BadRequestException
     */
    public function getMaxPoints(): ?int {
        $evalutationStrategy = ResultsModelFactory::findEvaluationStrategy($this->contestYear);
        if (!$this->category) {
            return null;
        }
        $tasks = $this->serviceTask->getTable()
            ->where('contest_id', $this->contestYear->contest_id)
            ->where('year', $this->contestYear->year)
            ->where('series BETWEEN 1 AND 6');
        $sum = 0;
        foreach ($tasks as $task) {
            $sum += $evalutationStrategy->getTaskPoints($task, $this->category);
        }
        return $sum;
    }

    /**
     * @throws BadRequestException
     */
    private function getCategory(?string $stringCategory): ?ModelCategory {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->contestYear);
        foreach ($evaluationStrategy->getCategories() as $category) {
            if ($category->id == $stringCategory) {
                return $category;
            }
        }
        return null;
    }

    /**
     * @throws BadRequestException
     */
    private function filterCategory(ResultSet $data): array {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->contestYear);

        $studyYears = [];
        if ($this->category) {
            $studyYears = $evaluationStrategy->categoryToStudyYears($this->category);
            $studyYears = is_array($studyYears) ? $studyYears : [$studyYears];
        }

        $graduationYears = [];
        foreach ($studyYears as $studyYear) {
            $graduationYears[] = $this->studyYearToGraduation($studyYear, $this->contestYear);
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

    private function studyYearToGraduation(?int $studyYear, ModelContestYear $contestYear): ?int {
        if (is_null($studyYear)) {
            return null;
        }
        return YearCalculator::getGraduationYear($studyYear, $contestYear);
    }
}
