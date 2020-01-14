<?php

namespace Tasks;

use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\ORM\Services\ServiceTaskContribution;
use Pipeline\Stage;
use SimpleXMLElement;


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
     * @var \FKSDB\ORM\Services\ServiceTaskContribution
     */
    private $taskContributionService;

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

    /**
     * ContributionsFromXML2 constructor.
     * @param ServiceTaskContribution $taskContributionService
     * @param ServiceOrg $serviceOrg
     */
    public function __construct(ServiceTaskContribution $taskContributionService, ServiceOrg $serviceOrg) {
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
        $xml = $this->data->getData();
        foreach ($xml->problems[0]->problem as $task) {
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

        foreach (self::$contributionFromXML as $type => $XMLElement) {
            list($parent, $child) = explode('/', $XMLElement);
            $parentEl = $XMLTask->{$parent}[0];
            // parse contributors
            $contributors = [];
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
                $contribution = $this->taskContributionService->createNewModel([
                    'person_id' => $contributor->person_id,
                    'task_id' => $task->task_id,
                    'type' => $type,
                ]);
            }
        }

        $this->taskContributionService->getConnection()->commit();
    }

}
