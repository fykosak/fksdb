<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Services\StudyYearService;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\ORM\Services\TaskContributionService;
use FKSDB\Models\ORM\Services\TaskStudyYearService;
use FKSDB\Models\Pipeline\Pipeline;

/**
 * This is not real factory, it's only used as an internode for defining
 * pipelines inside Neon and inject them into presenters at once.
 */
class PipelineFactory
{
    /**
     * @see StudyYearsFromXML
     * @var array
     */
    private array $defaultStudyYears;

    private TaskService $taskService;
    private TaskContributionService $taskContributionService;
    private TaskStudyYearService $taskStudyYearService;
    private StudyYearService $studyYearService;

    public function __construct(
        array $defaultStudyYears,
        TaskService $taskService,
        TaskContributionService $taskContributionService,
        TaskStudyYearService $taskStudyYearService,
        StudyYearService $studyYearService
    ) {
        $this->defaultStudyYears = $defaultStudyYears;
        $this->taskService = $taskService;
        $this->taskContributionService = $taskContributionService;
        $this->taskStudyYearService = $taskStudyYearService;
        $this->studyYearService = $studyYearService;
    }

    public function create(): Pipeline
    {
        $pipeline = new Pipeline();

        // common stages
        $pipeline->stages[] = new TasksFromXML($this->taskService);
        $pipeline->stages[] = new DeadlineFromXML($this->taskService);
        $pipeline->stages[] = new ContributionsFromXML($this->taskContributionService);
        $pipeline->stages[] =
            new StudyYearsFromXML($this->defaultStudyYears, $this->taskStudyYearService, $this->studyYearService);

        return $pipeline;
    }
}
