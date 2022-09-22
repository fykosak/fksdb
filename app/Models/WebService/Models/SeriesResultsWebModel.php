<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\Results\ResultsModelFactory;
use Nette\Application\BadRequestException;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class SeriesResultsWebModel extends WebModel
{
    private ContestService $contestService;

    public function inject(ContestService $contestService): void
    {
        $this->contestService = $contestService;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int')->required(),
            'year' => Expect::scalar()->castTo('int')->required(),
            'series' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    public function getJsonResponse(array $params): array
    {
        $contest = $this->contestService->findByPrimary($params['contestId']);
        $contestYear = $contest->getContestYear($params['year']);
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($contestYear);
        $contestants = $contest->related(DbNames::TAB_CONTESTANT)->where('year', $contestYear->year);
        $tasksData = [];
        /** @var TaskModel $task */
        foreach (
            $contest->related(DbNames::TAB_TASK)
                ->where('year', $params['year'])
                ->where('series', $params['series']) as $task
        ) {
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
        foreach ($contestants as $contestant) {
            $category = $evaluationStrategy->studyYearsToCategory($contestant->getPersonHistory()->study_year);
            $submits = $contestant->related(DbNames::TAB_SUBMIT)->where('task.series', $params['series']);
            $submitsData = [];
            $sum = 0;
            /** @var SubmitModel $submit */
            foreach ($submits as $submit) {
                $points = $evaluationStrategy->getSubmitPoints($submit, $category);
                $sum += $points;
                $submitsData[] = [
                    'taskId' => $submit->task_id,
                    'points' => $points,
                ];
            }
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
        foreach ($results as &$values) {
            usort($values, fn(array $a, array $b) => $b['sum'] <=> $a['sum']);
            $lastSum = null;
            $rank = 1;
            foreach ($values as &$value) {
                $value['rank'] = $rank;
                if ($value['sum'] !== $lastSum) {
                    $rank++;
                    $lastSum = $value['sum'];
                }
            }
        }

        return [
            'tasks' => $tasksData,
            'submits' => $results,
        ];
    }
}
