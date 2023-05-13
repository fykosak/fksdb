<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Pipeline\Stage;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Utils\DateTime;

/**
 * @note Assumes TasksFromXML has been run previously.
 */
class DeadlineFromXML extends Stage
{
    private TaskService $taskService;

    public function inject(TaskService $taskService): void
    {
        $this->taskService = $taskService;
    }

    /**
     * @param MemoryLogger $logger
     * @param SeriesData $data
     * @return SeriesData
     */
    public function __invoke(MemoryLogger $logger, $data): SeriesData
    {
        $deadline = (string)$data->getData()->deadline[0];
        if (!$deadline) {
            $logger->log(new Message(_('Missing deadline of the series.'), Message::LVL_WARNING));
            return $data;
        }

        $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s', $deadline);
        foreach ($data->getTasks() as $task) {
            $this->taskService->storeModel(['submit_deadline' => $datetime], $task);
        }
        return $data;
    }
}
