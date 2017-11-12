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
class StudyYearsFromXML2 extends Stage {

    const XML_ELEMENT_PARENT = 'study-years';

    const XML_ELEMENT_CHILD = 'study-year';

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

    function __construct($defaultStudyYears, ServiceTaskStudyYear $serviceTaskStudyYear, ServiceStudyYear $serviceStudyYear) {
        $this->defaultStudyYears = $defaultStudyYears;
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
        $this->serviceStudyYear = $serviceStudyYear;
    }

    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        $xml = $this->data->getData();
        foreach ($xml->problems[0]->problem as $task) {
            $this->processTask($task);
        }
    }

    public function getOutput() {
        return $this->data;
    }

    private function processTask(SimpleXMLElement $XMLTask) {
        $tasks = $this->data->getTasks();
        $tasknr = (int) (string) $XMLTask->number;

        $task = $tasks[$tasknr];
        $this->serviceTaskStudyYear->getConnection()->beginTransaction();

        // parse contributors            
        $studyYears = array();
        $hasYears = false;

        $parentEl = $XMLTask->{self::XML_ELEMENT_PARENT};
        // parse contributors            
        $contributors = array();
        if ($parentEl && isset($parentEl->{self::XML_ELEMENT_CHILD})) {
            foreach ($parentEl->{self::XML_ELEMENT_CHILD} as $element) {
                $studyYear = (string) $element;
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
