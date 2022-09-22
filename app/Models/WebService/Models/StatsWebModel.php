<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\Stats\TaskStatsModel;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class StatsWebModel extends WebModel
{
    private ContestService $contestService;

    public function inject(ContestService $contestService): void
    {
        $this->contestService = $contestService;
    }

    /**
     * @throws \SoapFault
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
        $contest = $this->contestService->findByPrimary(
            $this->container->getParameters()['inverseContestMapping'][$args->contest]
        );
        if (!isset($args->year)) {
            throw new \SoapFault('Sender', 'Unknown year.');
        }
        if (!isset($args->series)) {
            throw new \SoapFault('Sender', 'Unknown series.');
        }

        $doc = new \DOMDocument();
        $statsNode = $doc->createElement('stats');
        $doc->appendChild($statsNode);
        $model = new TaskStatsModel(
            $contest->getContestYear((int)$args->year),
            $this->contestService->explorer
        );

        if (isset($args->series)) {
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
                    $taskNode->setAttribute('label', (string)$task->label);
                    $taskNode->setAttribute('tasknr', (string)$task->tasknr);

                    $node = $doc->createElement('points', (string)$task->points);
                    $taskNode->appendChild($node);

                    $node = $doc->createElement('solvers', (string)$task->task_count);
                    $taskNode->appendChild($node);

                    $node = $doc->createElement('average', (string)$task->task_avg);
                    $taskNode->appendChild($node);
                }
            }
        }

        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($statsNode), XSD_ANYXML);
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int')->required(),
            'year' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    public function getJsonResponse(array $params): array
    {
        $contest = $this->contestService->findByPrimary($params['contestId']);
        $query = $contest->related(DbNames::TAB_TASK)->where('year', $params['year']);

        $result = [];
        /** @var TaskModel $task */
        foreach ($query as $task) {
            $result[] = array_merge($task->__toArray(), $task->getTaskStats());
        }
        return $result;
    }
}
