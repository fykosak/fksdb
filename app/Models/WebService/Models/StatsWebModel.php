<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\Stats\TaskStatsModel;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{contest_id:int,year:int},(SerializedTaskModel&TaskStatsType)[]>
 * @phpstan-import-type SerializedTaskModel from TaskModel
 * @phpstan-import-type TaskStatsType from TaskModel
 */
class StatsWebModel extends WebModel
{
    private ContestYearService $contestYearService;

    public function inject(ContestYearService $contestYearService): void
    {
        $this->contestYearService = $contestYearService;
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     * @deprecated
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (
            !isset($args->contest)
            || !isset($this->container->getParameters()['inverseContestMapping'][$args->contest])
        ) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        if (!isset($args->year)) {
            throw new \SoapFault('Sender', 'Unknown year.');
        }
        if (!isset($args->series)) {
            throw new \SoapFault('Sender', 'Unknown series.');
        }
        $contestYear = $this->contestYearService->findByContestAndYear(
            $this->container->getParameters()['inverseContestMapping'][$args->contest],
            (int)$args->year
        );

        $doc = new \DOMDocument();
        $statsNode = $doc->createElement('stats');
        $doc->appendChild($statsNode);
        $model = new TaskStatsModel($contestYear, $this->contestYearService->explorer);

        if (!is_array($args->series)) {
            $args->series = [$args->series];
        }
        foreach ($args->series as $series) {
            $seriesNo = $series->series;
            $model->series = $seriesNo;
            $tasks = $series->{'_'};
            /** @var TaskModel $task */
            foreach ($model->getData(explode(' ', $tasks)) as $task) {
                $taskNode = $doc->createElement('task');
                $statsNode->appendChild($taskNode);

                $taskNode->setAttribute('series', (string)$seriesNo);
                $taskNode->setAttribute('label', $task->label);
                $taskNode->setAttribute('tasknr', (string)$task->tasknr);

                $node = $doc->createElement('points', (string)$task->points);
                $taskNode->appendChild($node);

                $node = $doc->createElement('solvers', (string)$task->task_count); // @phpstan-ignore-line
                $taskNode->appendChild($node);

                $node = $doc->createElement('average', (string)$task->task_avg); // @phpstan-ignore-line
                $taskNode->appendChild($node);
            }
        }
        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($statsNode), XSD_ANYXML);
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contest_id' => Expect::scalar()->castTo('int')->required(),
            'year' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    public function getJsonResponse(array $params): array
    {
        $contestYear = $this->contestYearService->findByContestAndYear($params['contest_id'], $params['year']);
        $contestYear->getTasks();
        $result = [];
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks() as $task) {
            $result[] = array_merge($task->__toArray(), $task->getTaskStats());
        }
        return $result;
    }
}
