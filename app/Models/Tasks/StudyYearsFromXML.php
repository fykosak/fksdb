<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use FKSDB\Models\ORM\Models\TaskCategoryModel;
use FKSDB\Models\ORM\Services\StudyYearService;
use FKSDB\Models\ORM\Services\TaskCategoryService;
use FKSDB\Models\Pipeline\Stage;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

/**
 * @note Assumes TasksFromXML has been run previously.
 */
class StudyYearsFromXML extends Stage
{

    public const XML_ELEMENT_PARENT = 'study-years';
    public const XML_ELEMENT_CHILD = 'study-year';

    private array $defaultCategories;
    private TaskCategoryService $taskCategoryService;
    private StudyYearService $studyYearService;

    public function __construct(
        array $defaultCategories,
        Container $container
    ) {
        parent::__construct($container);
        $this->defaultCategories = $defaultCategories;
    }

    public function inject(
        TaskCategoryService $taskCategoryService,
        StudyYearService $studyYearService
    ): void {
        $this->taskCategoryService = $taskCategoryService;
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
        $this->taskCategoryService->explorer->getConnection()->beginTransaction();

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
            $categories = $this->defaultCategories[$data->getContestYear()->contest_id];
        } else {
            $categories = $studyYears;
        }
        /** @var TaskCategoryModel $category */
        foreach ($task->getCategories() as $category) {
            $this->taskCategoryService->disposeModel($category);
        }

        foreach ($categories as $category) {
            $this->taskCategoryService->storeModel([
                'task_id' => $task->task_id,
                'contest_category_id' => $category,
            ]);
        }
        $this->taskCategoryService->explorer->getConnection()->commit();
    }
}
