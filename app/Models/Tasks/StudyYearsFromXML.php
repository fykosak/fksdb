<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Services\StudyYearService;
use FKSDB\Models\ORM\Services\TaskStudyYearService;
use FKSDB\Models\Pipeline\Stage;

/**
 * @note Assumes TasksFromXML has been run previously.
 */
class StudyYearsFromXML extends Stage
{

    public const XML_ELEMENT_PARENT = 'study-years';
    public const XML_ELEMENT_CHILD = 'study-year';

    /** @var array   contribution type => xml element */
    private array $defaultStudyYears;
    private TaskStudyYearService $taskStudyYearService;
    private StudyYearService $studyYearService;

    public function __construct(
        array $defaultStudyYears,
        TaskStudyYearService $taskStudyYearService,
        StudyYearService $studyYearService
    ) {
        $this->defaultStudyYears = $defaultStudyYears;
        $this->taskStudyYearService = $taskStudyYearService;
        $this->studyYearService = $studyYearService;
    }

    /**
     * @param SeriesData $data
     */
    public function __invoke(MemoryLogger $logger, $data): SeriesData
    {
        $xml = $data->getData();
        foreach ($xml->problems[0]->problem as $task) {
            $this->processTask($task, $logger, $data);
        }
        return $data;
    }

    private function processTask(\SimpleXMLElement $xMLTask, MemoryLogger $logger, SeriesData $data): void
    {
        $tasks = $data->getTasks();
        $tasknr = (int)(string)$xMLTask->number;

        $task = $tasks[$tasknr];
        $this->taskStudyYearService->explorer->getConnection()->beginTransaction();

        // parse contributors
        $studyYears = [];
        $hasYears = false;

        $parentEl = $xMLTask->{self::XML_ELEMENT_PARENT};

        if ($parentEl && isset($parentEl->{self::XML_ELEMENT_CHILD})) {
            foreach ($parentEl->{self::XML_ELEMENT_CHILD} as $element) {
                $studyYear = (string)$element;
                $studyYear = trim($studyYear);
                if (!$studyYear) {
                    continue;
                }
                $hasYears = true;

                if (!$this->studyYearService->findByPrimary($studyYear)) {
                    $logger->log(new Message(sprintf(_('Unknown year "%s".'), $studyYear), Message::LVL_INFO));
                    continue;
                }

                $studyYears[] = $studyYear;
            }
        }

        if (!$studyYears) {
            if ($hasYears) {
                $logger->log(
                    new Message(_('Filling in default study years despite incorrect specification.'), Message::LVL_INFO)
                );
            }
            $studyYears = $this->defaultStudyYears[$data->getContestYear()->contest_id];
        }

        // delete old contributions
        foreach ($task->getStudyYears() as $studyYear) {
            $this->taskStudyYearService->disposeModel($studyYear);
        }

        // store new contributions
        foreach ($studyYears as $studyYear) {
            $this->taskStudyYearService->storeModel([
                'task_id' => $task->task_id,
                'study_year' => $studyYear,
            ]);
        }
        $this->taskStudyYearService->explorer->getConnection()->commit();
    }
}
