<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Authorization\Resource\PseudoContestYearResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;

/**
 * @phpstan-extends ContestYearWebModel<array{
 *     contestId:int,
 *     year:int,
 * },array<string,mixed>>
 */
class ResultsWebModel extends ContestYearWebModel
{
    /**
     * @throws BadRequestException
     */
    protected function getJsonResponse(): array
    {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->container, $this->getContestYear());
        $tasksData = [];
        /** @var TaskModel $task */
        foreach ($this->getContestYear()->getTasks() as $task) {
            foreach ($evaluationStrategy->getCategories() as $category) {
                $tasksData[$category->label] = $tasksData[$category->label] ?? [];
                $tasksData[$category->label][$task->series] = $tasksData[$category->label][$task->series] ?? [];
                $points = $evaluationStrategy->getTaskPoints($task, $category);
                if (!is_null($points)) {
                    $tasksData[$category->label][$task->series][] = [
                        'taskId' => $task->task_id,
                        'points' => $points,
                        'label' => $task->label,
                    ];
                }
            }
        }
        $results = [];
        /** @var ContestantModel $contestant */
        foreach ($this->getContestYear()->getContestants() as $contestant) {
            $submitsData = [];
            $sum = 0;
            /** @var SubmitModel $submit */
            foreach ($contestant->getSubmits() as $submit) {
                $points = $evaluationStrategy->getSubmitPoints($submit);
                $sum += $points;
                $submitsData[$submit->task_id] = $points;
            }
            if (count($submitsData)) {
                $school = $contestant->getPersonHistory()->school;
                $results[$contestant->contest_category->label] = $results[$contestant->contest_category->label] ?? [];
                $results[$contestant->contest_category->label][] = [
                    'contestant' => [
                        'contestantId' => $contestant->contestant_id,
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

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->contestYearAuthorizator->isAllowed(
            new PseudoContestYearResource(RestApiPresenter::RESOURCE_ID, $this->getContestYear()),
            self::class,
            $this->getContestYear()
        );
    }
}
