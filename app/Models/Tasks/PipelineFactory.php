<?php

namespace FKSDB\Models\Tasks;

use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Models\ORM\Services\ServiceStudyYear;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\ORM\Services\ServiceTaskContribution;
use FKSDB\Models\ORM\Services\ServiceTaskStudyYear;
use FKSDB\Models\Pipeline\Pipeline;

/**
 * This is not real factory, it's only used as an internode for defining
 * pipelines inside Neon and inject them into presenters at once.
 */
class PipelineFactory {

    /**
     * @see TasksFromXML
     * @var array
     */
    private array $columnMappings;

    /**
     * @see ContributionsFromXML
     * @var array
     */
    private array $contributionMappings;

    /**
     * @see StudyYearsFromXML
     * @var array
     */
    private array $defaultStudyYears;

    private ServiceTask $serviceTask;
    private ServiceTaskContribution $serviceTaskContribution;
    private ServiceTaskStudyYear $serviceTaskStudyYear;
    private ServiceStudyYear $serviceStudyYear;
    private ServiceOrg $serviceOrg;

    public function __construct(array $columnMappings, array $contributionMappings, array $defaultStudyYears, ServiceTask $serviceTask, ServiceTaskContribution $serviceTaskContribution, ServiceTaskStudyYear $serviceTaskStudyYear, ServiceStudyYear $serviceStudyYear, ServiceOrg $serviceOrg) {
        $this->columnMappings = $columnMappings;
        $this->contributionMappings = $contributionMappings;
        $this->defaultStudyYears = $defaultStudyYears;
        $this->serviceTask = $serviceTask;
        $this->serviceTaskContribution = $serviceTaskContribution;
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
        $this->serviceStudyYear = $serviceStudyYear;
        $this->serviceOrg = $serviceOrg;
    }

    public function create(): Pipeline {
        $pipeline = new Pipeline();
        $pipeline->setLogger(new MemoryLogger());

        // common stages
        $pipeline->addStage(new TasksFromXML($this->serviceTask));
        $pipeline->addStage(new DeadlineFromXML($this->serviceTask));
        $pipeline->addStage(new ContributionsFromXML($this->serviceTaskContribution));
        $pipeline->addStage(new StudyYearsFromXML($this->defaultStudyYears, $this->serviceTaskStudyYear, $this->serviceStudyYear));

        return $pipeline;
    }
}
