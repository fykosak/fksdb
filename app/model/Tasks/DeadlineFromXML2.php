<?php

namespace Tasks;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServiceTask;
use Nette\DateTime;
use Pipeline\Stage;


/**
 * @note Assumes TasksFromXML has been run previously.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DeadlineFromXML2 extends Stage {

    /**
     * @var SeriesData
     */
    private $data;

    /**
     * @var \FKSDB\ORM\Services\ServiceTask
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
     * @return mixed|SeriesData
     */
    public function getOutput() {
        return $this->data;
    }

    public function process() {
        $xml = $this->data->getData();
        $deadline = (string) $xml->deadline[0];
        if (!$deadline) {
            $this->log(_('Chybí deadline série.'), ILogger::WARNING);
            return;
        }

        $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s', $deadline);

        foreach ($this->data->getTasks() as $task) {
            $task->submit_deadline = $datetime;
            $this->taskService->save($task);
        }
    }

    /**
     * @param mixed $data
     */
    public function setInput($data) {
        $this->data = $data;
    }

}
