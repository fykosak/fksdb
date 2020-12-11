<?php

namespace FKSDB\Model\Tasks;

use Fykosak\Utils\Logging\Message;
use FKSDB\Model\ORM\Services\ServiceOrg;
use FKSDB\Model\ORM\Services\ServiceTaskContribution;
use FKSDB\Model\Pipeline\Stage;
use SimpleXMLElement;

/**
 * @note Assumes TasksFromXML has been run previously.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContributionsFromXML extends Stage {

    private SeriesData $data;

    /** @var array   contribution type => xml element */
    private static array $contributionFromXML = [
        'author' => 'authors/author',
        'solution' => 'solution-authors/solution-author',
    ];

    private ServiceTaskContribution $taskContributionService;

    private ServiceOrg $serviceOrg;

    public function __construct(ServiceTaskContribution $taskContributionService, ServiceOrg $serviceOrg) {
        $this->taskContributionService = $taskContributionService;
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @param SeriesData $data
     */
    public function setInput($data): void {
        $this->data = $data;
    }

    public function process(): void {
        $xml = $this->data->getData();
        foreach ($xml->problems[0]->problem as $task) {
            $this->processTask($task);
        }
    }

    public function getOutput(): SeriesData {
        return $this->data;
    }

    private function processTask(SimpleXMLElement $XMLTask): void {
        $tasks = $this->data->getTasks();
        $tasknr = (int)(string)$XMLTask->number;

        $task = $tasks[$tasknr];
        $this->taskContributionService->getConnection()->beginTransaction();

        foreach (self::$contributionFromXML as $type => $xmlElement) {
            [$parent, $child] = explode('/', $xmlElement);
            $parentEl = $XMLTask->{$parent}[0];
            // parse contributors
            $contributors = [];
            if (!$parentEl || !isset($parentEl->{$child})) {
                continue;
            }
            foreach ($parentEl->{$child} as $element) {
                $signature = (string)$element;
                $signature = trim($signature);
                if (!$signature) {
                    continue;
                }

                $org = $this->serviceOrg->findByTeXSignature($signature, $this->data->getContest()->contest_id);

                if (!$org) {
                    $this->log(new Message(sprintf(_('Unknown TeX ident \'%s\'.'), $signature), Message::LVL_INFO));
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
                $this->taskContributionService->createNewModel([
                    'person_id' => $contributor->person_id,
                    'task_id' => $task->task_id,
                    'type' => $type,
                ]);
            }
        }

        $this->taskContributionService->getConnection()->commit();
    }

}
