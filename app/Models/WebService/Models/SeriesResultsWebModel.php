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
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contestYear);
        $tasksData = [];
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks($params['series']) as $task) {
            foreach ($evaluationStrategy->getCategories() as $category) {
                $tasksData[$category->value] = $tasksData[$category->value] ?? [];
                $points = $evaluationStrategy->getTaskPoints($task, $category);
                if (!is_null($points)) {
                    $tasksData[$category->value][] = [
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
            $category = $evaluationStrategy->studyYearsToCategory($contestant->getPersonHistory()->study_year);
            $submits = $contestant->getSubmitsForSeries($params['series']);
            $submitsData = [];
            $sum = 0;
            /** @var SubmitModel $submit */
            foreach ($submits as $submit) {
                $points = $evaluationStrategy->getSubmitPoints($submit, $category);
                $sum += $points;
                $submitsData[$submit->task_id] = $points;
            }
            if (count($submitsData)) {
                $school = $contestant->getPersonHistory()->school;
                $results[$category->value] = $results[$category->value] ?? [];
                $results[$category->value][] = [
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
