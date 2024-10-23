<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\TaskContributionModel;
use FKSDB\Models\ORM\Models\TaskContributionType;
use FKSDB\Models\ORM\Services\TaskContributionService;
use FKSDB\Models\Pipeline\Stage;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;

/**
 * @note Assumes TasksFromXML has been run previously.
 * @phpstan-extends Stage<SeriesData>
 */
class ContributionsFromXML extends Stage
{
    /** @phpstan-var array{author:string,solution:string}  contribution type => xml element */
    private static array $contributionFromXML = [
        'author' => 'authors/author',
        'solution' => 'solution-authors/solution-author',
    ];

    private TaskContributionService $taskContributionService;

    public function inject(TaskContributionService $taskContributionService): void
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
            /** @phpstan-ignore-next-line */
            $parentEl = $xMLTask->{$parent}[0];
            // parse contributors
            $contributors = [];
            /** @phpstan-ignore-next-line */
            if (!$parentEl || !isset($parentEl->{$child})) {
                continue;
            }
            /** @phpstan-ignore-next-line */
            foreach ($parentEl->{$child} as $element) {
                $signature = (string)$element;
                $signature = trim($signature);
                if (!$signature) {
                    continue;
                }

                $row = $data->getContestYear()->contest
                    ->getOrganizers()
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

            /** @var OrganizerModel $contributor */
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
