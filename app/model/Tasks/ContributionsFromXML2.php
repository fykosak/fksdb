<?php

namespace Tasks\Legacy;

use Pipeline\Pipeline;
use Pipeline\Stage;
use ServiceOrg;
use ServiceTaskContribution;
use SimpleXMLElement;
use Tasks\SeriesData;

/**
 * @note Assumes TasksFromXML has been run previously.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContributionsFromXML2 extends Stage {

    /**
     * @var SeriesData
     */
    private $data;

    /**
     * @var array   contribution type => xml element 
     */
    private static $contributionFromXML = [
        'author' => 'authors/author',
        'solution' => 'solution-authors/solution-author',
    ];

    /**
     * @var ServiceTaskContribution
     */
    private $taskContributionService;

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

    public function __construct(ServiceTaskContribution $taskContributionService, ServiceOrg $serviceOrg) {
        $this->taskContributionService = $taskContributionService;
        $this->serviceOrg = $serviceOrg;
    }

    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        $xml = $this->data->getData();
        foreach ($xml->problems[0]->problem as $task) {
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

        foreach (self::$contributionFromXML as $type => $XMLElement) {
            list($parent, $child) = explode('/', $XMLElement);
            $parentEl = $XMLTask->{$parent}[0];
            // parse contributors            
            $contributors = array();
            if (!$parentEl || !isset($parentEl->{$child})) {
                continue;
            }
            foreach ($parentEl->{$child} as $element) {
                $signature = (string) $element;
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
                $contribution = $this->taskContributionService->createNew(array(
                    'person_id' => $contributor->person_id,
                    'task_id' => $task->task_id,
                    'type' => $type,
                ));

                $this->taskContributionService->save($contribution);
            }
        }

        $this->taskContributionService->getConnection()->commit();
    }

}
