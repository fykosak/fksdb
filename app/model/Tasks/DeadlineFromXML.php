<?php

namespace FKSDB\Tasks;

use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceTask;
use Nette\Utils\DateTime;
use Pipeline\Stage;


/**
 * @note Assumes TasksFromXML has been run previously.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DeadlineFromXML extends Stage {

    /**
     * @var SeriesData
     */
    private $data;

    /**
     * @var ServiceTask
     */
    private $taskService;

    /**
     * DeadlineFromXML2 constructor.
     * @param ServiceTask $taskService
     */
    function __construct(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    /**
     * @return SeriesData
     */
    public function getOutput() {
        return $this->data;
    }

    public function process() {
        $xml = $this->data->getData();
        $deadline = (string)$xml->deadline[0];
        if (!$deadline) {
            $this->log(new Message(_('Chybí deadline série.'), ILogger::WARNING));
            return;
        }

        $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s', $deadline);
        /**@var ModelTask $task */
        foreach ($this->data->getTasks() as $task) {
            $this->taskService->updateModel2($task, ['submit_deadline' => $datetime]);
        }
    }

    /**
     * @param SeriesData $data
     */
    public function setInput($data) {
        $this->data = $data;
    }

}
