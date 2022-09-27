<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Models\TaskContributionModel;
use FKSDB\Models\ORM\Models\TaskContributionType;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Services\TaskContributionService;
use FKSDB\Models\Pipeline\Stage;

/**
 * @note Assumes TasksFromXML has been run previously.
 */
class ContributionsFromXML extends Stage
{
    /** @var array   contribution type => xml element */
    private static array $contributionFromXML = [
        'author' => 'authors/author',
        'solution' => 'solution-authors/solution-author',
    ];

    private TaskContributionService $taskContributionService;

    public function __construct(TaskContributionService $taskContributionService)
    {
        $this->taskContributionService = $taskContributionService;
    }

    /**
     * @param SeriesData $data
     */
    public function __invoke(MemoryLogger $logger, $data): SeriesData
    {
        $xml = $data->getData();
        foreach ($xml->problems[0]->problem as $task) {
            $this->processTask($task, $logger, $data);
        }
        return $data;
    }

    private function processTask(\SimpleXMLElement $xMLTask, MemoryLogger $logger, SeriesData $data): void
    {
        $tasks = $data->getTasks();
        $tasknr = (int)(string)$xMLTask->number;

        $task = $tasks[$tasknr];
        $this->taskContributionService->explorer->getConnection()->beginTransaction();

        foreach (self::$contributionFromXML as $type => $xmlElement) {
            [$parent, $child] = explode('/', $xmlElement);
            $parentEl = $xMLTask->{$parent}[0];
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

                $row = $data->getContestYear()->contest
                    ->related(DbNames::TAB_ORG)
                    ->where('tex_signature', $signature)
                    ->fetch();

                if (!$row) {
                    $logger->log(new Message(sprintf(_('Unknown TeX ident \'%s\'.'), $signature), Message::LVL_INFO));
                    continue;
                }
                $contributors[] = $row;
            }

            /** @var TaskContributionModel $contribution */
            foreach ($task->getContributions(TaskContributionType::tryFrom($type)) as $contribution) {
                $this->taskContributionService->disposeModel($contribution);
            }

            /** @var OrgModel $contributor */
            foreach ($contributors as $contributor) {
                $this->taskContributionService->storeModel([
                    'person_id' => $contributor->person_id,
                    'task_id' => $task->task_id,
                    'type' => $type,
                ]);
            }
        }
        $this->taskContributionService->explorer->getConnection()->commit();
    }
}
