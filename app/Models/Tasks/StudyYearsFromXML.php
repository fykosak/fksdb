<?php

namespace FKSDB\Models\Tasks;

use FKSDB\Models\Logging\ILogger;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\ORM\Services\ServiceStudyYear;
use FKSDB\Models\ORM\Services\ServiceTaskStudyYear;
use FKSDB\Models\Pipeline\Stage;
use SimpleXMLElement;

/**
 * @note Assumes TasksFromXML has been run previously.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StudyYearsFromXML extends Stage {

    public const XML_ELEMENT_PARENT = 'study-years';

    public const XML_ELEMENT_CHILD = 'study-year';

    private SeriesData $data;
    /** @var array   contribution type => xml element */
    private array $defaultStudyYears;
    private ServiceTaskStudyYear $serviceTaskStudyYear;
    private ServiceStudyYear $serviceStudyYear;

    /**
     * StudyYearsFromXML2 constructor.
     * @param array $defaultStudyYears
     * @param ServiceTaskStudyYear $serviceTaskStudyYear
     * @param ServiceStudyYear $serviceStudyYear
     */
    public function __construct(array $defaultStudyYears, ServiceTaskStudyYear $serviceTaskStudyYear, ServiceStudyYear $serviceStudyYear) {
        $this->defaultStudyYears = $defaultStudyYears;
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
        $this->serviceStudyYear = $serviceStudyYear;
    }

    /**
     * @param SeriesData $data
     */
    public function setInput($data): void {
        $this->data = $data;
    }

    public function process(): void {
        $xml = $this->data->getData();
        foreach ($xml->problems[0]->problem as $task) {
            $this->processTask($task);
        }
    }

    public function getOutput(): SeriesData {
        return $this->data;
    }

    private function processTask(SimpleXMLElement $XMLTask): void {
        $tasks = $this->data->getTasks();
        $tasknr = (int)(string)$XMLTask->number;

        $task = $tasks[$tasknr];
        $this->serviceTaskStudyYear->getConnection()->beginTransaction();

        // parse contributors
        $studyYears = [];
        $hasYears = false;

        $parentEl = $XMLTask->{self::XML_ELEMENT_PARENT};

        if ($parentEl && isset($parentEl->{self::XML_ELEMENT_CHILD})) {
            foreach ($parentEl->{self::XML_ELEMENT_CHILD} as $element) {
                $studyYear = (string)$element;
                $studyYear = trim($studyYear);
                if (!$studyYear) {
                    continue;
                }
                $hasYears = true;

                if (!$this->serviceStudyYear->findByPrimary($studyYear)) {
                    $this->log(new Message(sprintf(_('Unknown year "%s".'), $studyYear), ILogger::INFO));
                    continue;
                }

                $studyYears[] = $studyYear;
            }
        }

        if (!$studyYears) {
            if ($hasYears) {
                $this->log(new Message(_('Doplnění defaultních ročníků i přes nesprávnou specifikaci.'), ILogger::INFO));
            }
            $studyYears = $this->defaultStudyYears[$this->data->getContest()->contest_id];
        }

        // delete old contributions
        foreach ($task->getStudyYears() as $studyYear) {
            $this->serviceTaskStudyYear->dispose($studyYear);
        }

        // store new contributions
        foreach ($studyYears as $studyYear) {
            $this->serviceTaskStudyYear->createNewModel([
                'task_id' => $task->task_id,
                'study_year' => $studyYear,
            ]);
        }
        $this->serviceTaskStudyYear->getConnection()->commit();
    }
}