<?php

namespace Tasks;

use Nette\DateTime;
use Pipeline\Stage;
use RuntimeException;
use ServiceTask;

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

    function __construct(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    public function getOutput() {
        return $this->data;
    }

    public function process() {
        $XMLproblems = $this->data->getXML();
        if (!$XMLproblems['deadline']) {
            return;
        }

        $deadline = $this->datetimeFromString($XMLproblems['deadline']);

        foreach ($this->data->getTasks() as $task) {
            $task->submit_deadline = $deadline;
            $this->taskService->save($task);
        }
    }

    public function setInput($data) {
        $this->data = $data;
    }

    /**
     * @param string $string
     * @return DateTime
     */
    private function datetimeFromString($string) {
        $compactString = strtr($string, '~', ' ');
        $compactString = str_replace(' ', '', $compactString);

        if (!($datetime = DateTime::createFromFormat('j.n.YG.i', $compactString))) {
            $datetime = DateTime::createFromFormat('j.n.Y', $compactString . '23.59');
        }

        if (!$datetime) {
            throw new RuntimeException("Cannot parse date '$compactString'.");
        }

        return $datetime;
    }

}
