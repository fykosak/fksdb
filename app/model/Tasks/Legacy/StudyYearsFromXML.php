<?php

namespace Tasks\Legacy;

use Pipeline\Stage;
use ServiceStudyYear;
use ServiceTaskStudyYear;
use SimpleXMLElement;
use Tasks\SeriesData;

/**
 * @note Assumes TasksFromXML has been run previously.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StudyYearsFromXML extends Stage {

    const DELIMITER = ',';
    const XML_ELEMENT = 'study-years';

    /**
     * @var SeriesData
     */
    private $data;

    /**
     * @var array   contribution type => xml element
     */
    private $defaultStudyYears;

    /**
     * @var ServiceTaskStudyYear
     */
    private $serviceTaskStudyYear;

    /**
     * @var ServiceStudyYear
     */
    private $serviceStudyYear;

    /**
     * StudyYearsFromXML constructor.
     * @param $defaultStudyYears
     * @param ServiceTaskStudyYear $serviceTaskStudyYear
     * @param ServiceStudyYear $serviceStudyYear
     */
    function __construct($defaultStudyYears, ServiceTaskStudyYear $serviceTaskStudyYear, ServiceStudyYear $serviceStudyYear) {
        $this->defaultStudyYears = $defaultStudyYears;
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
        $this->serviceStudyYear = $serviceStudyYear;
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
        $tasks = $this->data->getTasks();
        $tasknr = (int) (string) $XMLTask->number;

        $task = $tasks[$tasknr];
        $this->serviceTaskStudyYear->getConnection()->beginTransaction();

        // parse contributors
        $studyYears = [];
        $hasYears = false;
        foreach (explode(self::DELIMITER, (string) $XMLTask->{self::XML_ELEMENT}) as $studyYear) {
            $studyYear = trim($studyYear);
            if (!$studyYear) {
                continue;
            }
            $hasYears = true;

            if (!$this->serviceStudyYear->findByPrimary($studyYear)) {
                $this->log(sprintf(_("Neznámý ročník '%s'."), $studyYear));
                continue;
            }

            $studyYears[] = $studyYear;
        }

        if (!$studyYears) {
            if ($hasYears) {
                $this->log(_('Doplnění defaultních ročníků i přes nesprávnou specifikaci.'));
            }
            $studyYears = $this->defaultStudyYears[$this->data->getContest()->contest_id];
        }

        // delete old contributions
        foreach ($task->getStudyYears() as $studyYear) {
            $this->serviceTaskStudyYear->dispose($studyYear);
        }


        // store new contributions
        foreach ($studyYears as $studyYear) {
            $studyYearModel = $this->serviceTaskStudyYear->createNew(array(
                'task_id' => $task->task_id,
                'study_year' => $studyYear,
            ));

            $this->serviceTaskStudyYear->save($studyYearModel);
        }


        $this->serviceTaskStudyYear->getConnection()->commit();
    }

}

