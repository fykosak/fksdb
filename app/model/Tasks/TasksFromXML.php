<?php

namespace Tasks;

use Pipeline\Stage;
use ServiceTask;
use SimpleXMLElement;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class TasksFromXML extends Stage {

    /**
     * @var SeriesData
     */
    private $data;

    /**
     * @var array   xml element => task column
     */
    private $xmlToColumnMap;

    /**
     * @var ServiceTask
     */
    private $taskService;

    public function __construct(array $xmlToColumnMap, ServiceTask $taskService) {
        $this->xmlToColumnMap = $xmlToColumnMap;
        $this->taskService = $taskService;
    }

    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        foreach ($this->data->getXML() as $task) {
            $this->processTask($task);
        }
    }

    public function getOutput() {
        return $this->data;
    }

    private function processTask(SimpleXMLElement $XMLTask) {
        $contest = $this->data->getContest();
        $year = $this->data->getYear();
        $series = $this->data->getSeries();
        $tasknr = (int) (string) $XMLTask->number;

        // obtain ModelTask
        $task = $this->taskService->findBySeries($contest, $year, $series, $tasknr);
        if ($task == null) {
            $task = $this->taskService->createNew(array(
                'contest_id' => $contest->contest_id,
                'year' => $year,
                'series' => $series,
                'tasknr' => $tasknr,
            ));
        }

        // update fields
        foreach ($this->xmlToColumnMap as $xmlElement => $column) {
            $task->{$column} = (string) $XMLTask->{$xmlElement};
        }

        // store it
        $this->taskService->save($task);

        // forward it to pipeline
        $this->data->addTask($tasknr, $task);
    }

}
