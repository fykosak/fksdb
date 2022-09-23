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
            /** @var SubmitModel $submit */
            foreach ($submits as $submit) {
                $submitsData[] = [
                    'taskId' => $submit->task_id,
                    'points' => $evaluationStrategy->getSubmitPoints($submit, $category),
                ];
            }
            $school = $contestant->getPersonHistory()->school;
            $results[$category->value] = $results[$category->value] ?? [];
            $results[$category->value][] = [
                'contestant' => [
                    'name' => $contestant->person->getFullName(),
                    'school' => $school->name_abbrev,
                ],
                'submits' => $submitsData,
            ];
        }
        return ['tasks' => $tasksData, 'submits' => $results];
    }
}
