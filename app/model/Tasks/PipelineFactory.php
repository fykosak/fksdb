<?php

namespace FKSDB\Tasks;

use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\ORM\Services\ServiceStudyYear;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\ORM\Services\ServiceTaskStudyYear;
use FKSDB\Pipeline\Pipeline;

/**
 * This is not real factory, it's only used as an internode for defining
 * pipelines inside Neon and inject them into presenters at once.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PipelineFactory {

    /**
     * @see TasksFromXML
     * @var array
     */
    private $columnMappings;

    /**
     * @see ContributionsFromXML
     * @var array
     */
    private $contributionMappings;

    /**
     * @see StudyYearsFromXML
     * @var array
     */
    private $defaultStudyYears;

    private ServiceTask $serviceTask;

    private ServiceTaskContribution $serviceTaskContribution;

    private ServiceTaskStudyYear $serviceTaskStudyYear;

    private ServiceStudyYear $serviceStudyYear;

    private ServiceOrg $serviceOrg;

    /**
     * PipelineFactory constructor.
     * @param array $columnMappings
     * @param array $contributionMappings
     * @param array $defaultStudyYears
     * @param ServiceTask $serviceTask
     * @param ServiceTaskContribution $serviceTaskContribution
     * @param ServiceTaskStudyYear $serviceTaskStudyYear
     * @param ServiceStudyYear $serviceStudyYear
     * @param ServiceOrg $serviceOrg
     */
    public function __construct($columnMappings, $contributionMappings, $defaultStudyYears, ServiceTask $serviceTask, ServiceTaskContribution $serviceTaskContribution, ServiceTaskStudyYear $serviceTaskStudyYear, ServiceStudyYear $serviceStudyYear, ServiceOrg $serviceOrg) {
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
        $pipeline->addStage(new ContributionsFromXML($this->serviceTaskContribution, $this->serviceOrg));
        $pipeline->addStage(new StudyYearsFromXML($this->defaultStudyYears, $this->serviceTaskStudyYear, $this->serviceStudyYear));

        return $pipeline;
    }
}
