<?php

namespace FKSDB\Models\Tasks;

use FKSDB\Models\Logging\Logger;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\ORM\Services\ServiceTask;
use Nette\Utils\DateTime;
use FKSDB\Models\Pipeline\Stage;

/**
 * @note Assumes TasksFromXML has been run previously.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DeadlineFromXML extends Stage {

    private SeriesData $data;
    private ServiceTask $taskService;

    public function __construct(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    public function getOutput(): SeriesData {
        return $this->data;
    }

    public function process(): void {
        $xml = $this->data->getData();
        $deadline = (string)$xml->deadline[0];
        if (!$deadline) {
            $this->log(new Message(_('Missing deadline of the series.'), Logger::WARNING));
            return;
        }

        $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s', $deadline);
        foreach ($this->data->getTasks() as $task) {
            $this->taskService->updateModel2($task, ['submit_deadline' => $datetime]);
        }
    }

    /**
     * @param SeriesData $data
     */
    public function setInput($data): void {
        $this->data = $data;
    }
}
