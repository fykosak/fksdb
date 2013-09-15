<?php

namespace Tasks;

use Nette\InvalidArgumentException;
use Pipeline\Stage;
use ServiceOrg;
use ServiceTaskContribution;
use SimpleXMLElement;

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
    private $orgService;

    public function __construct($contributionFromXML, ServiceTaskContribution $taskContributionService, ServiceOrg $orgService) {
        $this->contributionFromXML = $contributionFromXML;
        $this->taskContributionService = $taskContributionService;
        $this->orgService = $orgService;
    }

    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        foreach ($this->data->getXML() as $task) {
            $this->processTask($task);
        }
    }

    public function getOutput() {
        return $this->data;
    }

    private function processTask(SimpleXMLElement $XMLTask) {
        $tasks = $this->data->getTasks();
        $tasknr = (int) (string) $XMLTask->number;

        $task = $tasks[$tasknr];
        $this->taskContributionService->getConnection()->beginTransaction();

        foreach ($this->contributionFromXML as $type => $XMLElement) {
            // parse contributors            
            $contributors = array();
            foreach (explode(self::DELIMITER, (string) $XMLTask->{$XMLElement}) as $signature) {
                $signature = trim($signature);
                if (!$signature) {
                    continue;
                }
                $org = $this->orgService->findByTeXSignature($this->data->getContest(), $signature);

                if (!$org) {
                    $this->log(sprintf("Neznámý TeX identifikátor '%s'.", $signature));
                } else {
                    $contributors[] = $org;
                }
            }

            // delete old contributions
            foreach ($task->getContributions($type) as $contribution) {
                $this->taskContributionService->dispose($contribution);
            }

            // store new contributions
            foreach ($contributors as $contributor) {
                $contribution = $this->taskContributionService->createNew(array(
                    'org_id' => $contributor->org_id,
                    'task_id' => $task->task_id,
                    'type' => $type,
                ));

                $this->taskContributionService->save($contribution);
            }
        }

        $this->taskContributionService->getConnection()->commit();
    }

}
