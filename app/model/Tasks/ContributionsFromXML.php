<?php

namespace Tasks;

use Pipeline\Stage;
use ServicePerson;
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
     * @var ServicePerson
     */
    private $servicePerson;

    public function __construct($contributionFromXML, ServiceTaskContribution $taskContributionService, ServicePerson $servicePerson) {
        $this->contributionFromXML = $contributionFromXML;
        $this->taskContributionService = $taskContributionService;
        $this->servicePerson = $servicePerson;
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


                $person = $this->servicePerson->findByTeXSignature($signature);
                if (!$person) {
                    $this->log(sprintf(_("Neznámý TeX identifikátor '%s'."), $signature));
                    continue;
                }

                $org = $person->getOrgs($this->data->getContest()->contest_id)->fetch();

                if (!$org) {
                    $this->log(sprintf(_("Osoba '%s' není org."), (string) $person));
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
