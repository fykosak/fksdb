<?php

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\Stats\StatsModelFactory;

class StatsWebModel extends WebModel {

    private ServiceContest $serviceContest;
    private StatsModelFactory $statsModelFactory;

    public function inject(
        ServiceContest $serviceContest,
        StatsModelFactory $statsModelFactory
    ): void {
        $this->serviceContest = $serviceContest;
        $this->statsModelFactory = $statsModelFactory;
    }

    /**
     * @param \stdClass $args
     * @return \SoapVar
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar {
        if (!isset($args->contest) || !isset($this->container->getParameters()['inverseContestMapping'][$args->contest])) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->serviceContest->findByPrimary($this->container->getParameters()['inverseContestMapping'][$args->contest]);
        if (!isset($args->year)) {
            throw new \SoapFault('Sender', 'Unknown year.');
        }
        if (!isset($args->series)) {
            throw new \SoapFault('Sender', 'Unknown series.');
        }

        $doc = new \DOMDocument();
        $statsNode = $doc->createElement('stats');
        $doc->appendChild($statsNode);

        $model = $this->statsModelFactory->createTaskStatsModel($contest->getContestYear((int)$args->year));

        if (isset($args->series)) {
            if (!is_array($args->series)) {
                $args->series = [$args->series];
            }
            foreach ($args->series as $series) {
                $seriesNo = $series->series;
                $model->setSeries($seriesNo);
                $tasks = $series->{'_'};
                foreach ($model->getData(explode(' ', $tasks)) as $task) {
                    $taskNode = $doc->createElement('task');
                    $statsNode->appendChild($taskNode);

                    $taskNode->setAttribute('series', $seriesNo);
                    $taskNode->setAttribute('label', $task['label']);
                    $taskNode->setAttribute('tasknr', $task['tasknr']);

                    $node = $doc->createElement('points', $task['points']);
                    $taskNode->appendChild($node);

                    $node = $doc->createElement('solvers', $task['task_count']);
                    $taskNode->appendChild($node);

                    $node = $doc->createElement('average', $task['task_avg']);
                    $taskNode->appendChild($node);
                }
            }
        }

        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($statsNode), XSD_ANYXML);
    }
}
