<?php

namespace Tasks;

use FKS\Logging\ILogger;
use Nette\DateTime;
use Pipeline\Stage;
use ServiceTask;
use Tasks\SeriesData;

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

    public function setInput($data) {
        $this->data = $data;
    }

}
