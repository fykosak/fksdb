<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\Results\ResultsModelFactory;
use Nette\Application\BadRequestException;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{
 *     contest_id:int,
 *     year:int,
 *     series:int,
 * },array<string,mixed>>
 */
class SeriesResultsWebModel extends WebModel
{
    private ContestYearService $contestYearService;

    public function inject(ContestYearService $contestYearService): void
    {
        $this->contestYearService = $contestYearService;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contest_id' => Expect::scalar()->castTo('int')->required(),
            'year' => Expect::scalar()->castTo('int')->required(),
            'series' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    public function getJsonResponse(array $params): array
    {
        $contestYear = $this->contestYearService->findByContestAndYear($params['contest_id'], $params['year']);
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->container, $contestYear);
        $tasksData = [];
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks($params['series']) as $task) {
            foreach ($evaluationStrategy->getCategories() as $category) {
                $tasksData[$category->label] = $tasksData[$category->label] ?? [];
                $points = $evaluationStrategy->getTaskPoints($task, $category);
                if (!is_null($points)) {
                    $tasksData[$category->label][] = [
                        'taskId' => $task->task_id,
                        'points' => $points,
                        'label' => $task->label,
                    ];
                }
            }
        }
        $results = [];
        /** @var ContestantModel $contestant */
        foreach ($contestYear->getContestants() as $contestant) {
            $submitsData = [];
            $sum = 0;
            /** @var SubmitModel $submit */
            foreach ($contestant->getSubmitsForSeries($params['series']) as $submit) {
                $points = $evaluationStrategy->getSubmitPoints($submit);
                $sum += $points;
                $submitsData[$submit->task_id] = $points;
            }
            if (count($submitsData)) {
                $school = $contestant->getPersonHistory()->school;
                $results[$contestant->contest_category->label] = $results[$contestant->contest_category->label] ?? [];
                $results[$contestant->contest_category->label][] = [
                    'contestant' => [
                        'name' => $contestant->person->getFullName(),
                        'school' => $school->name_abbrev,
                    ],
                    'sum' => $sum,
                    'submits' => $submitsData,
                ];
            }
        }
        foreach ($results as &$values) {
            usort($values, fn(array $a, array $b) => $b['sum'] <=> $a['sum']);
            $fromRank = 1;
            $sameRank = [];
            foreach ($values as $index => &$value) {
                $toRank = $index + 1;
                $sameRank[] = &$value;
                if (!isset($values[$index + 1]) || $value['sum'] !== $values[$index + 1]['sum']) {
                    foreach ($sameRank as &$sameValue) {
                        $sameValue['rank'] = [$fromRank, $toRank];
                    }
                    $sameRank = [];
                    $fromRank = $toRank + 1;
                }
            }
        }

        return [
            'tasks' => $tasksData,
            'submits' => $results,
        ];
    }
}
