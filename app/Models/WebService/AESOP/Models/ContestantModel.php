<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\Exports\Formats\PlainTextResponse;
use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Results\ResultsModelFactory;
use Nette\Application\BadRequestException;
use Nette\Database\ResultSet;
use Nette\DI\Container;

class ContestantModel extends AESOPModel
{

    protected TaskService $taskService;
    private ?ContestCategoryModel $category;

    /**
     * @throws BadRequestException
     */
    public function __construct(Container $container, ContestYearModel $contestYear, ?string $category)
    {
        parent::__construct($container, $contestYear);
        $this->category = $this->getCategory($category);
        $container->callInjects($this);
    }

    public function injectTaskService(TaskService $taskService): void
    {
        $this->taskService = $taskService;
    }

    /**
     * @throws BadRequestException
     */
    public function createResponse(): PlainTextResponse
    {
        $query = $this->explorer->query(
            "select ac.*, IF(ac.`x-points_ratio` >= 0.5, 'Y', 'N') AS `successful`
         FROM v_aesop_contestant ac
WHERE
	ac.`x-contest_id` = ?
        AND ac.`x-ac_year` = ?
                                               order by surname, name",
            $this->contestYear->contest_id,
            $this->contestYear->ac_year
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

    protected function getMask(): string
    {
        return $this->contestYear->contest->getContestSymbol() . '.rocnik.' . $this->category->label;
    }

    /**
     * Processing itself is not injectable so we ask the dependency explicitly per method (the task service).
     * @throws BadRequestException
     */
    public function getMaxPoints(): ?int
    {
        $evalutationStrategy = ResultsModelFactory::findEvaluationStrategy($this->container, $this->contestYear);
        if (!$this->category) {
            return null;
        }
        $tasks = $this->contestYear->getTasks()->where('series BETWEEN 1 AND 6');
        $sum = 0;
        /** @var TaskModel $task */
        foreach ($tasks as $task) {
            $sum += $evalutationStrategy->getTaskPoints($task, $this->category);
        }
        return (int)$sum;
    }

    /**
     * @throws BadRequestException
     */
    private function getCategory(?string $stringCategory): ?ContestCategoryModel
    {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->container, $this->contestYear);
        foreach ($evaluationStrategy->getCategories() as $category) {
            if ($category->contest_category_id === +$stringCategory) {
                return $category;
            }
        }
        return null;
    }

    /**
     * @throws BadRequestException
     */
    private function filterCategory(ResultSet $data): array
    {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->container, $this->contestYear);

        $studyYears = [];
        if ($this->category) {
            $studyYears = $evaluationStrategy->categoryToStudyYears($this->category);
        }

        $graduationYears = [];
        foreach ($studyYears as $studyYear) {
            $graduationYears[] = $this->studyYearToGraduation(StudyYear::tryFromLegacy($studyYear), $this->contestYear);
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

    private function calculateRank(array $data): array
    {
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

    private function studyYearToGraduation(?StudyYear $studyYear, ContestYearModel $contestYear): ?int
    {
        if (is_null($studyYear)) {
            return null;
        }
        return $studyYear->getGraduationYear($contestYear->ac_year);
    }
}
