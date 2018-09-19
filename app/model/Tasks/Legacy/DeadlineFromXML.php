<?php

namespace Tasks\Legacy;

use Nette\DateTime;
use Pipeline\PipelineException;
use Pipeline\Stage;
use ServiceTask;
use Tasks\SeriesData;

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
    private static $months = array(
        'ledna' => '1.',
        'února' => '2.',
        'března' => '3.',
        'dubna' => '4.',
        'května' => '5.',
        'června' => '6.',
        'července' => '7.',
        'srpna' => '8.',
        'září' => '9.',
        'října' => '10.',
        'listopadu' => '11.',
        'prosince' => '12.',
    );

    function __construct(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    public function getOutput() {
        return $this->data;
    }

    public function process() {
        $XMLproblems = $this->data->getData();
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
        $compactString = mb_strtolower($compactString);
        $compactString = str_replace(array_keys(self::$months), array_values(self::$months), $compactString);

        if (!($datetime = DateTime::createFromFormat('j.n.YG.i', $compactString))) {
            $datetime = DateTime::createFromFormat('j.n.Y', $compactString . '23.59');
        }

        if (!$datetime) {
            throw new PipelineException("Cannot parse date '$string'.");
        }

        return $datetime;
    }

}
