<?php

namespace Tasks\Legacy;

use FKSDB\ORM\Services\ServiceTask;
use Pipeline\Stage;
use SimpleXMLElement;
use Tasks\SeriesData;

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

    /**
     * TasksFromXML constructor.
     * @param array $xmlToColumnMap
     * @param ServiceTask $taskService
     */
    public function __construct(array $xmlToColumnMap, ServiceTask $taskService) {
        $this->xmlToColumnMap = $xmlToColumnMap;
        $this->taskService = $taskService;
    }

    /**
     * @param mixed $data
     */
    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        foreach ($this->data->getData() as $task) {
            $this->processTask($task);
        }
    }

    /**
     * @return mixed|SeriesData
     */
    public function getOutput() {
        return $this->data;
    }

    /**
     * @param SimpleXMLElement $XMLTask
     */
    private function processTask(SimpleXMLElement $XMLTask) {
        $contest = $this->data->getContest();
        $year = $this->data->getYear();
        $series = $this->data->getSeries();
        $tasknr = (int)(string)$XMLTask->number;

        // obtain FKSDB\ORM\Models\ModelTask
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
        $data = [];
        foreach ($this->xmlToColumnMap as $xmlElement => $column) {
            $data[$column] = (string)$XMLTask->{$xmlElement};
        }
        $this->taskService->updateModel2($task, $data);

        // forward it to pipeline
        $this->data->addTask($tasknr, $task);
    }

}
