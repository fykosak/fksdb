<?php

namespace Tasks\Legacy;

use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\ORM\Services\ServiceTaskContribution;
use Pipeline\Stage;
use SimpleXMLElement;
use Tasks\SeriesData;

/**
 * @note Assumes TasksFromXML has been run previously.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContributionsFromXML extends Stage {

    const DELIMITER = ',';

    /**
     * @var SeriesData
     */
    private $data;

    /**
     * @var array   contribution type => xml element
     */
    private $contributionFromXML;

    /**
     * @var ServiceTaskContribution
     */
    private $taskContributionService;

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

    /**
     * ContributionsFromXML constructor.
     * @param $contributionFromXML
     * @param ServiceTaskContribution $taskContributionService
     * @param ServiceOrg $serviceOrg
     */
    public function __construct($contributionFromXML, ServiceTaskContribution $taskContributionService, ServiceOrg $serviceOrg) {
        $this->contributionFromXML = $contributionFromXML;
        $this->taskContributionService = $taskContributionService;
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @param mixed $data
     */
    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        foreach ($this->data->getData() as $task) {
            $this->processTask($task);
        }
    }

    /**
     * @return mixed|SeriesData
     */
    public function getOutput() {
        return $this->data;
    }

    /**
     * @param SimpleXMLElement $XMLTask
     */
    private function processTask(SimpleXMLElement $XMLTask) {
        $tasks = $this->data->getTasks();
        $tasknr = (int) (string) $XMLTask->number;

        $task = $tasks[$tasknr];
        $this->taskContributionService->getConnection()->beginTransaction();

        foreach ($this->contributionFromXML as $type => $XMLElement) {
            // parse contributors
            $contributors = [];
            foreach (explode(self::DELIMITER, (string) $XMLTask->{$XMLElement}) as $signature) {
                $signature = trim($signature);
                if (!$signature) {
                    continue;
                }


                $org = $this->serviceOrg->findByTeXSignature($signature, $this->data->getContest()->contest_id);

                if (!$org) {
                    $this->log(sprintf(_("Neznámý TeX identifikátor '%s'."), $signature));
                    continue;
                }
                $contributors[] = $org;
            }

            // delete old contributions
            foreach ($task->getContributions($type) as $contribution) {
                $this->taskContributionService->dispose($contribution);
            }

            // store new contributions
            foreach ($contributors as $contributor) {
                $contribution = $this->taskContributionService->createNew([
                    'person_id' => $contributor->person_id,
                    'task_id' => $task->task_id,
                    'type' => $type,
                ]);

                $this->taskContributionService->save($contribution);
            }
        }

        $this->taskContributionService->getConnection()->commit();
    }

}
