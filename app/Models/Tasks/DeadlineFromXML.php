<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Services\TaskService;
use Nette\Utils\DateTime;
use FKSDB\Models\Pipeline\Stage;

/**
 * @note Assumes TasksFromXML has been run previously.
 */
class DeadlineFromXML extends Stage
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
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
            $this->taskService->updateModel($task, ['submit_deadline' => $datetime]);
        }
        return $data;
    }
}
